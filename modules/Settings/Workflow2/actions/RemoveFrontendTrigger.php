<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_RemoveFrontendTrigger_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = intval($request->get('triggerID'));

        $frontendWorkflowObj = new \Workflow\FrontendWorkflows($id);
        $frontendWorkflowObj->remove();
    }
}

