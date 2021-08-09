<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SetWorkflowFolder_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $folder = $request->get('folder');

        $sql = 'UPDATE vtiger_wf_settings SET folder = ? WHERE id = '.$workflowID;
        $adb->pquery($sql, array($folder));
    }
}