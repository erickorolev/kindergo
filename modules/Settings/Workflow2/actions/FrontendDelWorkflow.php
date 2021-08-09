<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendDelWorkflow_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("id"));

        $sql = 'DELETE FROM vtiger_wf_frontendmanager WHERE id = ?';
        $adb->pquery($sql, array($workflowID));

        $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
        $workflowObj->refreshFrontendJs();

    }
}

