<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SchedulerAdd_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();
        
        $sql = 'INSERT INTO vtiger_wf_scheduler SET active = 0, workflow_id = 0, hour = "*", minute = "*", dom = "*", month = "*", dow = "*", year = "*" ';
        $adb->query($sql);

    }
}