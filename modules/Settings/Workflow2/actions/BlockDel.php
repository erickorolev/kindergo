<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_BlockDel_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $block = ($request->get("blockid"));
        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

        //echo $workflowID;
        $blockParts = explode("__", $block);

        $sql = "UPDATE vtiger_wfp_connections SET deleted = 1, last_changed_userid = ".$current_user->id." WHERE
            workflow_id = '".$workflowID."' AND (
            (
                source_mode = 'block' AND
                source_id = '".$blockParts[1]."'
            ) OR
                destination_id = '".$blockParts[1]."')
        ";

        $adb->query($sql, true);

        \Workflow2::updateWorkflow($workflowID);

        if($blockParts[0] == "person") {
            $sql = "DELETE FROM vtiger_wfp_objects WHERE
                id = '".$blockParts[1]."' AND
                workflow_id = '".$workflowID."'
            ";
        //    echo $sql;
            $adb->query($sql, true);
        } else {
            $sql = "DELETE FROM vtiger_wfp_blocks WHERE
                id = '".$blockParts[1]."' AND
                workflow_id = '".$workflowID."'
            ";
        //    echo $sql;
            $adb->query($sql, true);
        }
    }
}