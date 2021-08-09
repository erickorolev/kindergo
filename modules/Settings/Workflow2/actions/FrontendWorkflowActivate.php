<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendWorkflowActivate_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = intval($request->get('id'));
        $status = intval($request->get('status'));


        $frontendWorkflowObj = new \Workflow\FrontendWorkflows($id);
        $frontendWorkflowObj->setActive($status == '1' ? 1 : 0);
    }
}

