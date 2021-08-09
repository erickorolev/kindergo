<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ConnectionDel_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;

        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));

        $source = explode("__", $request->get("source"));
        $destination = explode("__", $request->get("destination"));

        if($source[1] == $destination[1])
            exit();

        $sql = "UPDATE vtiger_wfp_connections SET deleted = 1, last_changed_userid = ".$current_user->id." WHERE
            workflow_id = ".$workflowID." AND
            source_mode = '".$source[0]."' AND
            source_id = '".$source[1]."' AND
            source_key = '".$source[2]."' AND
            destination_id = '".$destination[1]."' AND
            destination_key = '".$destination[2]."'
        ";

        //$sql = "DELETE FROM vtiger_wfp_connections WHERE
        //    workflow_id = ".$workflowID." AND
        //    source_mode = '".$source[0]."' AND
        //    source_id = '".$source[1]."' AND
        //    source_key = '".$source[2]."' AND
        //    destination_id = '".$destination[1]."' AND
        //    destination_key = '".$destination[2]."'
        //";
        //echo $sql;

        $adb->query($sql);

        \Workflow2::updateWorkflow($workflowID);
    }
}

