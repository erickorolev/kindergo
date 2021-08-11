<?php
/**
This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

It belongs to the Workflow Designer and must not be distributed without complete extension
 **/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskStart extends \Workflow\Task {
    /**
     * @var \Workflow\Preset\FormGenerator
     */
    private $_formgenerator = null;

    public function init() {
        $this->addPreset("Condition", "condition", array(
            'mode' => 'field'
        ));

        $this->_formgenerator = $this->addPreset("FormGenerator", "fields", array(
            'module' => $this->getModuleName(),
        ));
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|string
     */
    public function handleTask(&$context) {
        if($this->get("start") == "asynchron" && !$this->isContinued()) {
            return array("delay" => time() + 1, "checkmode" => "static");
        }

        if($this->notEmpty('view_condition_lv')) {
            if (!$this->getWorkflow()->checkExecuteCondition($context)) {
                return 'stop';
            }
        }

        $collection_process = $this->get('collection_process');
        $collection_variable = $this->get('collection_variable');

        if($this->notEmpty('currenciesformat')) {
            $context->setEnvironment('_int_format_currency', true);
        }

        if(!empty($collection_process) && $collection_process != -1 && !empty($collection_variable) && $collection_variable != -1) {
            $recordids = $context->getEnvironment('_collection_recordids');
            $context->setEnvironment($collection_variable, $recordids);
        }

        $workflowSettings = $this->getWorkflow()->getSettings();

        if(!empty($workflowSettings["startfields"])) {
            if($this->getWorkflow()->isSubWorkflow()) {
                $this->addStat('RequestValue Task in SubWorkflow currently not supported!');
                return 'start';
            }

            if(!$this->getWorkflow()->hasRequestValues('startfields')) {

                $export = $this->_formgenerator->exportUserQueue($this->_settings, $context);

                $this->getWorkflow()->requestValues('startfields', $export, $this, getTranslatedString('LBL_ENTER_VALUES_TO_START', 'Workflow2'), $context, true, false);
                return false;
            }
        }

        $startvalues = $context->getEnvironment("value");
        if($startvalues !== false) {
            $this->addStat("requested values:");
            foreach($startvalues as $key => $value) {
                $this->addStat("'".$key."' = '".$value."'");
            }
        }

        return "start";
    }

    public function beforeSave(&$values) {
        global $adb;
        $values2 = array();
        $columns = array();

        if(isset($values["runtime"])) {
            $values2[] = $values["runtime"];
            $columns[] = "`trigger` = ?";
        }

        $values2[] = !empty($values["execute_only_once_per_record"]) ? 1 : 0;
        $columns[] = "`once_per_record` = ?";

        $values2[] = !empty($values["nologging"]) ? 1 : 0;
        $columns[] = "`nologging` = ?";

        $values2[] = !empty($values["withoutrecord"]) ? 1 : 0;
        $columns[] = "`withoutrecord` = ?";

        $values2[] = !empty($values["collection_process"]) ? 1 : 0;
        $columns[] = "`collection_process` = ?";

        if(isset($values["runtime2"])) {
            $values2[] = $values["runtime2"];
            $columns[] = "`simultan` = ?";
        }
        if(isset($values["execution_user"])) {
            $values2[] = $values["execution_user"];
            $columns[] = "`execution_user` = ?";
        }
        if(isset($values["fields"]) && count($values["fields"]) > 0) {
            $values2[] = serialize($values["fields"]);
            $columns[] = "`startfields` = ?";
        } else {
            $values2[] = "";
            $columns[] = "`startfields` = ?";
        }

        if(isset($_POST["task"]["condition"])) {
            $values2[] = \Workflow\VtUtils::json_encode($values["condition"]);
            $columns[] = "`view_condition` = ?";
            unset($values["task"]["condition"]);
        } else {
            $values2[] = '';
            $columns[] = "`view_condition` = ?";
        }
        /*
                if(isset($_POST["task"]["view_condition_lv"])) {
                    $columns[] = "`view_condition_lv` = 1";
                } else {
                    $columns[] = "`view_condition_lv` = 0";
                }
        */
        $sql = "UPDATE vtiger_wf_settings SET ".implode(",", $columns)." WHERE id = ".$this->getWorkflowId();
        $adb->pquery($sql, array($values2));

        \Workflow\Main::setOption($this->getWorkflowId(), 'timezone', $values['timezone']);

    }

    public function beforeGetTaskform($viewer) {
        global $adb;
        $userModuleModel = Users_Module_Model::getInstance('Users');

        if($this->get("trigger") == -1) {
            $sql = "SELECT `trigger` FROM vtiger_wf_settings WHERE id = ".$this->getWorkflowId();
            $result = $adb->query($sql);
            $this->set("runtime", $adb->query_result($result, 0, "trigger"));
        }
        if($this->getModuleName() != 'Users') {
            $sql = "SELECT * FROM vtiger_wf_trigger WHERE deleted = 0 ORDER BY custom, `module`, `label`";
        } else {
            $sql = "SELECT * FROM vtiger_wf_trigger WHERE deleted = 0 AND `key` = 'WF2_FRONTENDTRIGGER' ORDER BY custom, `module`, `label`";
        }

        $result = $adb->query($sql);

        $trigger = array();
        while($row = $adb->fetchByAssoc($result)) {
            $trigger[
            $row["custom"]=="1"?getTranslatedString("LBL_CUSTOM_TRIGGER", "Settings:Workflow2"):getTranslatedString("LBL_SYS_TRIGGER", "Settings:Workflow2")
            ][$row["key"]] = array(
                'label' => getTranslatedString($row["label"], "Settings:".$row["module"]),
                'description' => !empty($row['description'])?vtranslate($row['description'], 'Settings:'.$row["module"]):''
            );
        }
        $timezones = $userModuleModel->getTimeZonesList();
        $viewer->assign('timezones', $timezones);

        foreach($trigger as $key => $value) {
            asort($trigger[$key]);
        }
        $viewer->assign("trigger", $trigger);
    }

    public function getEnvironmentVariables() {
        $variables = array();

        if($this->get('runtime') == 'WF_REFERENCE') {
            $variables[] = "['source_module'";
            $variables[] = "['source_record'";
        }

        $fields = $this->get('fields');
        $collection_variable = $this->get('collection_variable');
        if(!empty($collection_variable) && $collection_variable != -1) {
            $variables[] = $collection_variable;
        }

        if(!empty($fields) && $fields !== -1) {
            foreach($fields as $field) {
                $variables[] = "['value']['".$field['name']."'";
            }
            return $variables;
        }

        return $variables;
    }

}