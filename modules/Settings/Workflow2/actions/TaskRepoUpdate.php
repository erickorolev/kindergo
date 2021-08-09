<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskRepoUpdate_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        $repositories = \Workflow\Repository::getAll();

        foreach($repositories as $repository) {
            /**
             * @var $repository \Workflow\Repository
             */
            $repository->checkRepoForUpdate();
            $repository->update();
        }

    }


}