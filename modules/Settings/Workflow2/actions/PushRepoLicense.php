<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_PushRepoLicense_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        $obj = new \Workflow\Repository(intval($params['repoid']));
        $obj->pushPackageLicense($params['license']);
        $obj->update();
    }


}