<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_StopAllRunning_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = \PearDatabase::getInstance();
        $workflow_id = $request->get('workflowID');

        $adb->pquery('DELETE FROM vtiger_wf_queue WHERE workflow_id = ?', array(intval($workflow_id)));
    }
}