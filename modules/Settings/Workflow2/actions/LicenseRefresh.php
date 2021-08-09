<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_LicenseRefresh_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $params = $request->getAll();

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $as2df = new $className("Workflow2", $moduleModel->version);

        $as2df->g7cd354a00dadcd8c4600f080755860496d0c03d5();

        $licenseHash = $as2df->gb8d9a4f2e098e53aee15b6fd5f9456705f64f354();

        $sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE "%.redoo-networks.%"';
        $result = $adb->query($sql, true);

        $repository = new \Workflow\Repository($adb->query_result($result, 0, 'id'));
        $repository->pushPackageLicense(md5($licenseHash));

        echo json_encode(array('result' => 'ok'));
    }
}