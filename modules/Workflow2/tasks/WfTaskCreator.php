<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

use \Workflow\VTEntity;
use \Workflow\ExpressionParser;
/* vt6 ready */

class WfTaskCreator extends \Workflow\Task
{
    protected $_envSettings = array("new_record_id", 'was_created_new');
    private $_disableModules = array("Events", "Calendar", "Workflow2", "Colorizer", "Integration", "Webmail", "Kommentare", "SMS");

    protected $_javascriptFile = "WfTaskSetter.js";

    /**
     * @var VTEntity
     */
    protected $_newObj = null;

    /**
     * @var \Workflow\Preset\FieldSetter
     */
    protected $fieldSetter = false;

    public function init() {
        if(-1 != $this->get("new_module") || !empty($_POST["task"]["new_module_setter"])) {
            $newModule = !empty($_POST["task"]["new_module_setter"])?$_POST["task"]["new_module_setter"]:$this->get("new_module");

            $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
                'fromModule' => $this->getModuleName(),
                'toModule' => $newModule,
                'refFields' => false
            ));
        }
    }

    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        $setterMap = $this->get("setter");
        $newModule = $this->get("new_module");

        if(empty($newModule)) {
            $this->addStat("NO Configuration set");
            return "yes";
        }

        $uniqueCheck = $this->get('uniquecheck');

        $createNew = true;

        if($uniqueCheck !== -1 && is_array($uniqueCheck) && !empty($uniqueCheck)) {
            $setterMap = $this->get('setter');
            $fieldValue = $this->fieldSetter->getFieldValueArray($context, $setterMap);
            $condition = array();
            foreach($uniqueCheck as $checkField) {
                $condition[$checkField] = $fieldValue[$checkField];
            }

            $this->addStat($condition);
            $records = \Workflow\VtUtils::findRecordIDs($newModule,$condition);

            if(count($records) > 0) {
                // duplicate records found
                $this->addStat('duplicate Record found ['.implode(',',$records).'] -> do not create new');

                $updateexisting = $this->get('updateexisting');
                if($updateexisting !== -1 && is_array($updateexisting) && !empty($updateexisting)) {
                    foreach($records as $crmid) {
                        $entity = \Workflow\VTEntity::getForId($crmid);

                        foreach($updateexisting as $field) {
                            $entity->set($field, $fieldValue[$field]);
                        }

                        $entity->save();
                        break;
                    }
                }

                $context->setEnvironment("new_record_id", $records[0], $this);
                $context->setEnvironment("was_created_new", 'false', $this);

                $createNew = false;
                $newObj = \Workflow\VTEntity::getForId($records[0]);
            }
        }

        if($createNew === true) {
            $newObj = VTEntity::create($newModule);

            $this->fieldSetter->apply($newObj, $setterMap, $context, $this);

            try {
                $newObj->save();
            } catch(WebServiceException $exp) {
                // Somethink is wrong with the values. missing mandatory fields?
            }


            $context->setEnvironment("new_record_id", $newObj->getId(), $this);
            $context->setEnvironment("was_created_new", 'true', $this);
        }

        if($context->getModuleName() == "Assets" && $newModule == "HelpDesk") {
            global $adb;
            $sql = "INSERT INTO vtiger_crmentityrel SET crmid = ?, module = ?, relcrmid = ?, relmodule = ?";
            \Workflow\VtUtils::query($sql, array($context->getId(), $context->getModuleName(), $newObj->getId(), $newObj->getModuleName()));
        }

        if($this->get("redirectAfter") == "1" || $this->get("redirectAfter") == "2") {
            if($newModule == 'Events') {
                $newModule = 'Calendar';
            }
            $this->getWorkflow()->setSuccessRedirection("index.php?module=".$newModule."&view=Detail&record=".$newObj->getId(), $this->get("redirectAfter") == "1"?'same':'new');
        }

        if($this->get("exec_workflow") !== "" && $this->get("exec_workflow") != -1) {
            $newContext = VTEntity::getForId($newObj->getId(), $newObj->getModuleName());
            $newContext->loadEnvironment($context->getEnvironment());

            $objWorkflow = new \Workflow\Main($this->get("exec_workflow"), false, $context->getUser());
            $objWorkflow->isSubWorkflow(true);
            $objWorkflow->setContext($newContext);

            $objWorkflow->start();
        }

        if(!empty($newObj)) {
            $this->_newObj = $newObj;
        }

        Workflow2::$enableError = true;

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb, $vtiger_current_version;

        $new_module = $this->get("new_module");

        if(!empty($_POST["task"]["new_module_setter"])) {
            $new_module= $_POST["task"]["new_module_setter"];

            $mandatoryFields = VtUtils::getMandatoryFields(getTabId($_POST["task"]["new_module_setter"]));
            $startFields = array();
            $counter = 1;
            foreach($mandatoryFields as $field) {
                $startFields["".$counter] = array("field" => $field["fieldname"], "mode" => "value", "value" => "", "fixed" => true);
                $counter++;
            }

            if($_POST["task"]["new_module_setter"] == "Calendar") {
                $startFields["".$counter] = array("field" => "time_start", "mode" => "value", "value" => "", "fixed" => true);
            }

            $this->set("setter", $startFields);
            $this->set("new_module", $new_module);

        }

        $workflows = Workflow2::getWorkflowsForModule($new_module, 1);
        $viewer->assign("extern_workflows", $workflows);

        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
        $result = \Workflow\VtUtils::query($sql);
        $wsTabId = $adb->query_result($result, 0, "id");

        if(!empty($new_module) && $new_module != -1) {
            $viewer->assign("new_module", $new_module);
        }

        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = \Workflow\VtUtils::query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            if($row["name"] == "Calendar")
                continue;

            $module[$row["name"]] = getTranslatedString($row["tablabel"],$row["name"]);
        }
        #$module["Events"] =  getTranslatedString($row["tablabel"],"Events");
        asort($module);

        $viewer->assign("avail_module", $module);

    }

}
