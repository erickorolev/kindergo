<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ObjectSetText_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = (int)$request->get('id');
        $text = $request->get('text');

        $sql = "UPDATE vtiger_wf_objects SET content = ? WHERE id = ?";
        $adb->pquery($sql, array($text, $id), true);

   }
}