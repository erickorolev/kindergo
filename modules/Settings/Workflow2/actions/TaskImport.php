<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskImport_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        if(!is_uploaded_file($_FILES['file']['tmp_name'])) {
            throw new Exception('no taskfile');
        }

        $enableUpgrade = $request->get('enableUpgrade');
        $enableDowngrade = $request->get('enableDowngrade');
        \Workflow\Repository::installFile($_FILES['file']['tmp_name'], 1, 0, (!empty($enableUpgrade)), (!empty($enableDowngrade)));

        $response = new Vtiger_Response();
        try {
            $response->setResult(array("success" => true));
        } catch(Exception $exp) {
            $response->setResult(array("success" => false, "error" => $exp->getMessage()));
        }

        $response->emit();
    }


}