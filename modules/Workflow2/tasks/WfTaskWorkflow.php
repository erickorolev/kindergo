<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready 2014/04/28 */
class WfTaskWorkflow extends \Workflow\Task {
    /**
     * @param $context \Workflow\VTEntity
     * @return array
     */
    public function handleTask(&$context) {
        $wf_chooser = $this->get("wf_chooser");

        if($wf_chooser == -1 || $wf_chooser == "1") {
            $workflow_id = $this->get("workflow_id");
        } else if($wf_chooser == "2") {
            $wf_name = $this->get("wf_name", $context);
            if(empty($wf_name) || $wf_name == -1) {
                return "yes";
            }

            global $adb;

            $this->addStat("execute Workflow '".$wf_name."'");

            $sql = "SELECT id FROM vtiger_wf_settings WHERE module_name = '".$this->getModuleName()."' AND title = ? AND `active` = 1";
            $result = $adb->pquery($sql, array($wf_name));
            $workflow_id = $adb->query_result($result, 0, "id");
        }

        $this->addStat("found Workflow ID '".$workflow_id."'");

        if(!empty($workflow_id)) {
            $obj = new \Workflow\Main($workflow_id, false, $context->getUser());

            if($this->get('condition') != '1' || $obj->checkExecuteCondition($context)) {
                $obj->setExecutionTrigger($this->getWorkflow()->getExecutionTrigger());
                $obj->isSubWorkflow(true);

                $obj->setContext($context);

                $obj->start();
            }

            return 'yes';
        }
        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $sql = "SELECT * FROM vtiger_wf_settings WHERE module_name = '".$this->getModuleName()."' AND active = 1 AND id != ".$this->getWorkflowId();
        $result = $adb->query($sql);

        $workflows = array();
        while($row = $adb->fetch_array($result)) {
            $workflows[] = $row;
        }
        $viewer->assign("workflows", $workflows);
    }

    public function onTaskFormSave() {

    }

}