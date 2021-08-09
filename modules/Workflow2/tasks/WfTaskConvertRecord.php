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

class WfTaskConvertRecord extends \Workflow\Task
{
    protected $_envSettings = array("new_record_id");
    private $_disableModules = array("Events", "Calendar", "Workflow2", "Colorizer", "Integration", "Webmail", "Kommentare", "SMS");

    protected $_javascriptFile = "WfTaskSetter.js";

    /**
     * @var \Workflow\Preset\ProductChooser
     */
    private $_productchooser = null;

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

            $recordModel = \Vtiger_Module_Model::getInstance($newModule);

            if($recordModel instanceof \Inventory_Module_Model) {
                $this->_productchooser = $this->addPreset("ProductChooser", "product", array(
                    'module' => $this->getModuleName()
                ));
            }

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

        $newObj = VTEntity::create($newModule);
        $this->fieldSetter->apply($newObj, $setterMap, $context, $this);

        if($newObj->isInventory() && $context->isInventory()) {
            $newObj->importInventory($context->exportInventory());
            $newObj->set('hdnTaxType', $context->get('hdnTaxType'));
        }

        //$newObj->redirectToCreationForm();

        $newObj->save();

        if($this->_productchooser !== null && $this->get('product') != -1) {
            $newObj = $this->_productchooser->addProducts2Entity($this->get('product'), $context, $newObj);

            $newObj->save();
        }

        if($context->getModuleName() == "Assets" && $newModule == "HelpDesk") {
            global $adb;
            $sql = "INSERT INTO vtiger_crmentityrel SET crmid = ?, module = ?, relcrmid = ?, relmodule = ?";
            $adb->pquery($sql, array($context->getId(), $context->getModuleName(), $newObj->getId(), $newObj->getModuleName()));
        }
        $context->setEnvironment("new_record_id", $newObj->getId(), $this);

        if($this->get("redirectAfter") == "1") {
            $this->getWorkflow()->setSuccessRedirection("index.php?module=".$newModule."&view=Detail&record=".$newObj->getId(), 'same');
        }

        if($this->get("exec_workflow") !== "" && $this->get("exec_workflow") != -1) {
            $newContext = VTEntity::getForId($newObj->getId(), $newObj->getModuleName());
            $newContext->loadEnvironment($context->getEnvironment());

            $objWorkflow = new \Workflow\Main($this->get("exec_workflow"), false, $context->getUser());

            $objWorkflow->setContext($newContext);

            $objWorkflow->start();
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb, $vtiger_current_version;

        $new_module = $this->get("new_module");

        if(!empty($_POST["task"]["new_module_setter"])) {
            $new_module= $_POST["task"]["new_module_setter"];

            $allFields = VtUtils::getFieldsForModule($_POST["task"]["new_module_setter"], true);
            $mandatoryFields = VtUtils::getMandatoryFields(getTabid($_POST["task"]["new_module_setter"]));

            $startFields = array();
            $counter = 1;
            $fromFields = $this->fieldSetter->getFromFields();

            $tmpFields = array();
            foreach($mandatoryFields as $fields) {
                $tmpFields[$fields['fieldname']] = 1;
            }
            $mandatoryFields = $tmpFields;

            $tmpFields = array();
            foreach($fromFields as $block) {
                foreach($block as $field) {
                    $tmpFields[$field->name] = 1;
                }
            }

            foreach($allFields as $field) {

                if($field->name != 'crmid') {
                    if(isset($tmpFields[$field->name])) {
                        $startValue = '$'.$field->name;
                    } else {
                        $startValue = '';
                    }

                    if($field->name == 'currency_id' || ($field->uitype != 4 && $field->uitype != 70 && $field->name != 'modifiedby' && $field->displaytype != 3 && $field->displaytype != 5 && $field->displaytype != 2)) {
                        $startFields["".$counter] = array("field" => $field->name, "mode" => "field", "value" => $startValue, 'fixed' => isset($mandatoryFields[$field->name]));
                        $counter++;
                    }
                }
            }

            if($_POST["task"]["new_module_setter"] == "Calendar") {
                $startFields["".$counter] = array("field" => "time_start", "mode" => "value", "value" => "");
            }

            $this->set("setter", $startFields);
            $this->set("new_module", $new_module);

        }

        $workflows = Workflow2::getWorkflowsForModule($new_module, 1);
        $viewer->assign("extern_workflows", $workflows);

        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, "id");

        if(!empty($new_module) && $new_module != -1) {
            $viewer->assign("new_module", $new_module);
        }

        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = $adb->query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            if($row["name"] == "Calendar")
                continue;

            $module[$row["name"]] = getTranslatedString($row["tablabel"],$row["name"]);
        }
        #$module["Events"] =  getTranslatedString($row["tablabel"],"Events");
        asort($module);

        $viewer->assign("avail_module", $module);

        if($this->_productchooser !== null) {
            $viewer->assign('productchooser', true);
        }
    }

}
