<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ObjectRecordConnection_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $selected = (int)$request->get("recordID");
        $objectID = (int)$request->get("objectID");

        $sql = "UPDATE vtiger_wfp_objects SET crmid = ".$selected." WHERE id = ".$objectID;
        $adb->query($sql);

        $sql = "SELECT first_name, last_name FROM vtiger_users WHERE id = ".$selected;
        $result = $adb->query($sql);

        $row = $adb->fetch_array($result);

        echo trim($row["first_name"]." ".$row["last_name"]);
   }
}