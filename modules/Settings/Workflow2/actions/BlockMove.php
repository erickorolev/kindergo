<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_BlockMove_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $block = $request->get("blockid");
        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

        $block = explode("__", $block);

        if($block[0] == "block") {
            $sql = "UPDATE vtiger_wfp_blocks SET x = ".$request->get("left").", y = ".$request->get("top")." WHERE id = ".$block[1];
            $adb->query($sql);
        } else {
            $sql = "UPDATE vtiger_wfp_objects SET x = ".$request->get("left").", y = ".$request->get("top")." WHERE id = ".$block[1];
            $adb->query($sql);

        }
    }
}