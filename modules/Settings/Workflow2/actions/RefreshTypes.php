<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_RefreshTypes_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $repo_id = (int)$request->get('repo_id');

        $repo = new \Workflow\Repository($repo_id);

        if($request->get('mode') == 'new') {
            $repo->installAll(\Workflow\Repository::INSTALL_NEW);
        } else {
            $repo->installAll(\Workflow\Repository::INSTALL_ONLY_UPDATES);
        }

    }

}