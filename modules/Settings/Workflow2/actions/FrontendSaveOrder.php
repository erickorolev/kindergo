<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendSaveOrder_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = \PearDatabase::getInstance();
        $sort = 1;

        $indexes = $request->get('indexes');
        foreach($indexes as $id) {
            $sql = 'UPDATE vtiger_wf_frontendmanager SET `order` = ? WHERE id = ?';
            $adb->pquery($sql, array($sort, $id), true);

            $sort++;
        }
    }
}

