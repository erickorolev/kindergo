<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FolderSetValue_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $folder = $request->get('folder');
        $key = $request->get('key');
        $value = $request->get('value');

        if(!in_array($key, array('color'))) {
            return;
        }

        $sql = 'SELECT INTO vtiger_wf_folder WHERE title = ?';
        $result = $adb->pquery($sql, array($folder));

        if($adb->num_rows($result) > 0) {
            $sql = 'UPDATE vtiger_wf_folder SET ' . $key . ' = ? WHERE title = ?';
            $adb->pquery($sql, array($value, $folder));
        } else {
            $sql = 'INSERT INTO vtiger_wf_folder SET title = ?, ' . $key . ' = ?';
            $adb->pquery($sql, array($folder, $value));
        }
    }
}