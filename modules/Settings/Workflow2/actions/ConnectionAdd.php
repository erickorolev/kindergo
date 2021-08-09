<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ConnectionAdd_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));

        $source = explode("__", $request->get("source"));
        $destination = explode("__", $request->get("destination"));

        if($source[1] == $destination[1])
            exit();

        $sql = "SELECT id FROM vtiger_wfp_connections WHERE
            workflow_id = ".$workflowID." AND
            source_mode = '".$source[0]."' AND
            source_id = '".$source[1]."' AND
            source_key = '".$source[2]."' AND
            destination_id = '".$destination[1]."' AND
            destination_key = '".$destination[2]."'
        ";
        if($adb->num_rows($adb->query($sql)) == 0) {
            $sql = "INSERT INTO vtiger_wfp_connections SET
                workflow_id = ".$workflowID.",
                source_mode = '".$source[0]."',
                source_id = '".$source[1]."',
                source_key = '".$source[2]."',
                destination_id = '".$destination[1]."',
                destination_key = '".$destination[2]."'
            ";
        } else {
            $sql = "UPDATE vtiger_wfp_connections SET deleted = 0 WHERE
                workflow_id = ".$workflowID." AND
                source_mode = '".$source[0]."' AND
                source_id = '".$source[1]."' AND
                source_key = '".$source[2]."' AND
                destination_id = '".$destination[1]."' AND
                destination_key = '".$destination[2]."'
            ";
        }

        $adb->query($sql);

        \Workflow2::updateWorkflow($workflowID);
    }
}

