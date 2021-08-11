<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ObjectRemove_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = (int)$request->get('id');

        $sql = "DELETE FROM vtiger_wf_objects WHERE id = ?";
        $adb->pquery($sql, array($id), true);

   }
}