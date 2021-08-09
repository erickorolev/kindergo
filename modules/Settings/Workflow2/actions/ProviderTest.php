<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ProviderTest_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $params = $request->getAll();


        $provider = \Workflow\ConnectionProvider::getProvider($params['type']);
        $provider->setConfiguration($params['settings']);

        try {
            $provider->test();
        } catch (\Exception $exp) {
            echo json_encode(array('result' => 'error', 'message' => $exp->getMessage()));
            exit();
        }


        echo json_encode(array('result' => 'ok'));
    }
}