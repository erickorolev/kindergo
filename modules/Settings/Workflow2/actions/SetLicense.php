<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SetLicense_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = \PearDatabase::getInstance();

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $GenKey = new $className(basename(dirname((dirname(__FILE__)))), $moduleModel->version);

        try {
            $GenKey->removeLicense();
            $GenKey->g7cd354a00dadcd8c4600f080755860496d0c03d5($request->get("license"), array(__FILE__, Vtiger_Loader::resolveNameToPath('modules.Settings.Workflow2.views.Config')));
        } catch(Exception $exp) {
            throw new \Exception('Error');
        }

        $licenseHash = $GenKey->gb8d9a4f2e098e53aee15b6fd5f9456705f64f354();

        $sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE "%.redoo-networks.%"';
        $result = $adb->query($sql, true);

        $repository = new \Workflow\Repository($adb->query_result($result, 0, 'id'));
        $repository->pushPackageLicense(md5($licenseHash));

        if(!defined("DEBUG_MODE") || DEBUG_MODE != true) {
            $repos = \Workflow\Repository::getAll(true);
            foreach ($repos as $repo) {
                /**
                 * @var $repo \Workflow\Repository
                 */
                try {
                    $repo->installAll(\Workflow\Repository::INSTALL_ALL);
                } catch (Exception $exp) {
                    // Don't do any action, because there are probably always task files
                }
            }
        }

        $response = new Vtiger_Response();

        $response->setResult(array("success" => true));

        $response->emit();
    }
}