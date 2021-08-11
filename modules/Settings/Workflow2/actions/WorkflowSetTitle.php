<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_WorkflowSetTitle_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $title = ($request->get("title"));

        //$adb->query("SET NAMES latin");

        $sql = "UPDATE vtiger_wf_settings SET `title` = ? WHERE id = ?";
        $adb->pquery($sql, array($title, intval($workflowID)));

    }
}