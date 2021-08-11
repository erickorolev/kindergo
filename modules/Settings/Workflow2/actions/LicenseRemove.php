<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_LicenseRemove_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $params = $request->getAll();

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $as2df = new $className("Workflow2", $moduleModel->version);

        $as2df->removeLicense();

        echo json_encode(array('result' => 'ok'));
    }
}