<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SchedulerDel_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();

        $id = (int)$request->get('scheduleId');

        $sql = 'DELETE FROM vtiger_wf_scheduler WHERE id = '.$id;
        $adb->query($sql);
    }
}