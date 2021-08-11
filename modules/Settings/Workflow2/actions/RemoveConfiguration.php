<?php
global $root_directory;
require_once($root_directory."/modules/Colorizer/autoloader.php");

class Settings_Colorizer_RemoveConfiguration_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();

        $rid = $request->get("rId");

        $sql = "DELETE FROM vtiger_colorizer WHERE id = ?";
        $db->pquery($sql, array($rid));
    }
}