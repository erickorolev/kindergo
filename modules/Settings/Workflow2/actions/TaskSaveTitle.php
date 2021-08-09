<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskSaveTitle_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $block = $request->get("blockid");

        $sql = "UPDATE vtiger_wfp_blocks SET text = ? WHERE id = ?";
        $adb->pquery($sql, array($request->get("text"), intval($request->get("block_id"))), true);

    }
}