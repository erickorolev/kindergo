<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_CreateNewFolder_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $workflowId = intval($request->get('workflowId'));
        $newTitle = $request->get('newfolder');

        $sql = 'UPDATE vtiger_wf_settings SET folder = ? WHERE id = ?';
        $adb->pquery($sql, array($newTitle, $workflowId));

        if(!empty($_COOKIE['wf_visibility'])) {
            $userVisibility = @json_decode($_COOKIE['wf_visibility'], true);
        } else {
            $userVisibility = array();
        }

        $userVisibility[$newTitle] = intval($request->get('visible'));

        setcookie('wf_visibility', json_encode($userVisibility), time() + 86400 * 30);
    }
}