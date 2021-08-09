<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendAddWorkflow_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflowId"));


        $sql = 'SELECT title, module_name FROM vtiger_wf_settings WHERE id = ?';
        $result = $adb->pquery($sql, array($workflowID));
        $title = $adb->query_result($result, 0, 'title');
        $moduleName =  $adb->query_result($result, 0, 'module_name');

        $sql = 'SELECT MAX(`order`) + 1 as num FROM vtiger_wf_frontendmanager WHERE module = ?';
        $result = $adb->pquery($sql, array($moduleName));

        $order = intval($adb->query_result($result, 0, 'num'));

        $sql = 'INSERT INTO vtiger_wf_frontendmanager SET workflow_id = ?, label = ?, position = ?, color = ?, `order` = ?, `module` = ?';
        $adb->pquery($sql, array($workflowID, $title, 'sidebar', '#3D57FF', $order, $moduleName), true);

        echo json_encode(array('module' => $moduleName));
    }
}

