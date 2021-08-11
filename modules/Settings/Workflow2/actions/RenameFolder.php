<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_RenameFolder_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $oldTitle = $request->get('oldtitle');
        $newTitle = $request->get('newtitle');

        $sql = 'UPDATE vtiger_wf_settings SET folder = ? WHERE folder = ?';
        $adb->pquery($sql, array($newTitle, $oldTitle));
    }
}