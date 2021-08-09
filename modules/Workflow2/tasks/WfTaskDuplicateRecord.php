<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

/* vt6 ready 2014/04/22 */
class WfTaskDuplicateRecord extends \Workflow\Task
{
    protected $_javascriptFile = array("WfTaskSetter.js");
    protected $_envSettings = array("new_record_id");
    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;

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
        $recordID = trim($this->get("recordid", $context));

        if(!empty($recordID) && $recordID != -1) {
            $contextRecord = \Workflow\VTEntity::getForId($recordID);
        } else {
            $contextRecord = $context;
        }

        $newObj = \Workflow\VTEntity::create($contextRecord->getModuleName());

        $oldData = $contextRecord->getData();

        foreach($oldData as $key => $value) {
            $newObj->set($key, $value);
        }

        if($contextRecord->isInventory()) {
            $products = $contextRecord->exportInventory();
            $this->addStat($products);
            $newObj->importInventory($products);
            $newObj->save();
        }

        /* After this line, copy from Creator */
        $setterMap = $this->get("setter");


        if(is_array($setterMap)) {
            $this->fieldSetter->apply($newObj, $setterMap, $contextRecord, $this);
        }

        $newObj->save();

        $context->setEnvironment("new_record_id", $newObj->getId(), $this);

        if($this->get("redirectAfter") == "1") {
            $this->getWorkflow()->setSuccessRedirection("index.php?module=".$newObj->getModuleName()."&view=Detail&record=".$newObj->getId(), 'same');
        }

        if($this->get("exec_workflow") !== "" && $this->get("exec_workflow") != -1) {
            $newContext = \Workflow\VTEntity::getForId($newObj->getId(), $newObj->getModuleName());
            $objWorkflow = new \Workflow\Main($this->get("exec_workflow"), false, $context->getUser());

            $objWorkflow->setContext($newContext);
            $objWorkflow->isSubWorkflow(true);

            $objWorkflow->start();
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $new_module = $this->get("new_module");


        if(!empty($_POST["task"]["new_module_setter"])) {
            $recordID = trim($this->get("recordid"));
            $new_module = $_POST["task"]["new_module_setter"];

            if(-1 == $recordID || empty($recordID)) {
                $this->set('recordid', '$id');
            }
        }

        if(!empty($new_module) && $new_module != -1) {
            $viewer->assign("new_module", $new_module);
        }

        $workflows = Workflow2::getWorkflowsForModule($new_module, 1);
        $viewer->assign("extern_workflows", $workflows);

        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = $adb->query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            if($row["name"] == "Calendar")
                continue;

            $module[$row["name"]] = getTranslatedString($row["tablabel"],$row["name"]);
        }

        asort($module);

        $viewer->assign("avail_module", $module);
    }

    public function beforeSave(&$values) {
        global $vtiger_current_version;

        #unset($values["setter"]["##SETID##"]);

    }
}
