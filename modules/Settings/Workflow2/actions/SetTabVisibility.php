<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SetTabVisibility_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        if(!empty($_COOKIE['wf_visibility'])) {
            $userVisibility = @json_decode($_COOKIE['wf_visibility'], true);
        } else {
            $userVisibility = array();
        }

        if($request->has('foldermode') === true) {
            $userVisibility[$request->get('target')] = intval($request->get('visible'));
        } else {
            $userVisibility[getTabid($request->get('target'))] = intval($request->get('visible'));
        }

        setcookie('wf_visibility', json_encode($userVisibility), time() + 86400 * 30);

    }
}