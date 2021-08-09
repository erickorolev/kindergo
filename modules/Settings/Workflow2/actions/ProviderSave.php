<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ProviderSave_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $params = $request->getAll();

        if(!empty($params['connectionId'])) {
            $sql = 'UPDATE vtiger_wf_provider SET title = ? WHERE id = ?';
            $adb->pquery($sql, array($params['title'], $params['connectionId']));
        } else {
            $sql = 'INSERT INTO vtiger_wf_provider SET type = ?, title = ?';
            $adb->pquery($sql, array($params['type'], $params['title']), true);
            $params['connectionId'] = \Workflow\VtUtils::LastDBInsertID();;
        }

        $connection = \Workflow\ConnectionProvider::getConnection($params['connectionId']);
        $connection->saveConfiguration($params['settings']);

        /*try {
            $return = $connection->test();
        } catch (\Exception $exp) {
            echo json_encode(array('result' => 'error', 'message' => $exp->getMessage()));
            exit();
        }*/


        echo json_encode(array('result' => 'ok'));
    }
}