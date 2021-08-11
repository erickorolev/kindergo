<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ObjectSetPos_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = (int)$request->get('id');
        $x = $request->get('x');
        $y = $request->get('y');

        $sql = "UPDATE vtiger_wf_objects SET x = ?, y = ? WHERE id = ?";
        $adb->pquery($sql, array(intval($x), intval($y), intval($id) ), true);

   }
}