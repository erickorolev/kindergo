<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ColorSet_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $blockid = (int)$request->get('block_id');
        $color = $request->get('color');

        $color = preg_replace("/[^a-zA-Z0-9]/", "", $request->get("color"));

        $sql = "UPDATE vtiger_wfp_blocks SET colorlayer = ? WHERE id = ".$blockid;
        $adb->pquery($sql, array($color));

    }
}