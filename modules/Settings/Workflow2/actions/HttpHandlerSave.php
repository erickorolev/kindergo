<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_HttpHandlerSave_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $params = $request->getAll();

        $sql = "UPDATE vtiger_wf_http_limits SET name = ? WHERE id = ".$params['edit_id'];
        $adb->pquery($sql, array($params["limit_name"]));

        $ips = explode("\n", $params["limit_ips"]);
        $sql = "DELETE FROM vtiger_wf_http_limits_ips WHERE limit_id = ".$params['edit_id'];
        $adb->query($sql);
        foreach($ips as $ip) {
            $ip = trim($ip);
            $sql = "INSERT INTO vtiger_wf_http_limits_ips SET limit_id = ?, ip = ?";
            $adb->pquery($sql, array($params['edit_id'], $ip));
        }

        $sql = "DELETE FROM vtiger_wf_http_limits_value WHERE limit_id = ".$params['edit_id'];
        $adb->query($sql);
        $sql = "INSERT INTO vtiger_wf_http_limits_value SET limit_id = ?, mode = ?, value = ?";
        foreach($params["values"]["trigger"] as $value) {
            $value = trim($value);
            $adb->pquery($sql, array($params['edit_id'], "trigger", $value));
        }
        foreach($params["values"]["workflow"] as $value) {
            $value = trim($value);
            $adb->pquery($sql, array($params['edit_id'], "id", $value));
        }

        echo json_encode(array('result' => 'ok'));
    }
}