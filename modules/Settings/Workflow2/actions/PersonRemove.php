<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_PersonRemove_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $workflowID = (int)$request->get('workflow_id');
        $person_id = $request->get('block_id');
        $parts = explode('__', $person_id);
        $person_id = intval($parts[1]);
        $sql = "DELETE FROM vtiger_wfp_connections WHERE
            workflow_id = '".$workflowID."' AND
            source_mode = 'person' AND
            source_id = '".$person_id."'

        ";
        echo $sql;
        $adb->query($sql);

        $sql = "DELETE FROM vtiger_wfp_objects WHERE
            id = '".$person_id."' AND
            workflow_id = '".$workflowID."'
        ";
        $adb->query($sql);
    }
}

