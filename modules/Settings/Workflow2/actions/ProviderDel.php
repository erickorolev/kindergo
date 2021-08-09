<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ProviderDel_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $id = $request->get('id');

        $adb->pquery('DELETE FROM vtiger_wf_provider WHERE id = ?', array(intval($id)));

        echo json_encode(array('result' => 'ok'));
    }
}