<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendAddObject_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $moduleName = $request->get('moduleName');
        $type = $request->get('type');

        $sql = 'SELECT MAX(`order`) + 1 as num FROM vtiger_wf_frontendmanager WHERE module = ?';
        $result = $adb->pquery($sql, array($moduleName));

        $order = intval($adb->query_result($result, 0, 'num'));

        $sql = 'INSERT INTO vtiger_wf_frontendmanager SET workflow_id = ?, label = ?, position = ?, color = ?, `order` = ?, `module` = ?';
        $adb->pquery($sql, array(0, '', 'sidebar', $type, $order, $moduleName), true);


    }
}

