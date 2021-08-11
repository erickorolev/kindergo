<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskRepoSave_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        \Workflow\Repository::testLicense($params['repo_url'], $params['repo_license'], '', false, $params['_nonce']);

        \Workflow\Repository::register($params['repo_url'], $params['repo_license'], '', false, $params['_nonce'], $params['push-package']);

        header('Location:index.php?module=Workflow2&view=TaskRepoManager&parent=Settings');
    }


}