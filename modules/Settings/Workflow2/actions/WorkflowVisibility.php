<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_WorkflowVisibility_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("id"));
        $value = ($request->get("value"));

        //$adb->query("SET NAMES latin");

        $sql = "UPDATE vtiger_wf_settings SET `invisible` = ? WHERE id = ?";
        $adb->pquery($sql, array($value == '1' ? 0 : 1, intval($workflowID)));

        /**
         * @var $workflowObj Workflow2_Module_Model
         */
        $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
        $workflowObj->refreshFrontendJs();

    }
}