<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_DeleteHttpHandler_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $editId = intval($request->get("id"));

        $sql = 'DELETE FROM vtiger_wf_http_limits WHERE id = ?';
        $adb->pquery($sql, array($editId));

        $sql = 'DELETE FROM vtiger_wf_http_limits_ips WHERE limit_id = ?';
        $adb->pquery($sql, array($editId));

        $sql = 'DELETE FROM vtiger_wf_http_limits_value WHERE limit_id = ?';
        $adb->pquery($sql, array($editId));
   }
}