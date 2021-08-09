<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_Authmanager_View extends Settings_Workflow2_Default_View {

    /**
     * @var bool|Settings_Workflow2_Module_Model
     */
    protected $settingsModel = false;

	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $viewer = $this->getViewer($request);

        $workflow_id = (int)$request->get('workflow');
        $save_auth = $request->get('save_auth');

        $objWorkflow = new \Workflow\Main($workflow_id);

        if(!empty($save_auth)) {
            $authmanagement = $request->get('authmanagement');

            if(!empty($authmanagement)) {
                $sql = "UPDATE vtiger_wf_settings SET authmanagement = 1 WHERE id = ?";
                $adb->pquery($sql, array($workflow_id));
            } else {
                $sql = "UPDATE vtiger_wf_settings SET authmanagement = 0 WHERE id = ?";
                $adb->pquery($sql, array($workflow_id));
            }

            $auth = $request->get('auth');

            foreach($auth["roles"] as $roleid => $value) {
                $objWorkflow->setAuthValue("role".$roleid, $value);
            }

            foreach($auth["users"] as $roleid => $value) {
                $objWorkflow->setAuthValue("user".$roleid, $value);
            }

            unset($objWorkflow);
            $objWorkflow = new \Workflow\Main($workflow_id);
        }

        $sql = "SELECT id,user_name,first_name,last_name,is_admin FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);
        while($user = $adb->fetchByAssoc($result)) {
            if($user['is_admin'] == 'on') {
                continue;
            }
            #$user["id"] = "19x".$user["id"];
            $availUser["user"][] = $user;
        }

        $roles = getAllRoleDetails();
        $authData = $objWorkflow->getAuthDataAll();

        $settings = $objWorkflow->getSettings();

        $viewer->assign('enabledAuth', $objWorkflow->hasAuthManagement());
        $viewer->assign('workflowId', $workflow_id);
        $viewer->assign('roles', $roles);
        $viewer->assign('authData', $authData);
        $viewer->assign('settings', $settings);
        $viewer->assign('availUser', $availUser);

        $viewer->view('VT7/Authmanager.tpl', $request->getModule(false));
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.Settings.$moduleName.views.resources.Workflow2"
        );

        if('Settings_Workflow2_Statistic_View' != get_class($this) ) {
            $jsFileNames[] = "modules.Settings.$moduleName.views.resources.Config";
        }

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        foreach($headerScriptInstances as $obj) {
            $src = $obj->get('src');
            if(!empty($src) && strpos($src, $moduleName) !== false) {
                $obj->set('src', $src.'?v='.$moduleModel->version);
            }
        }

        return $headerScriptInstances;
	}
    function getHeaderCss(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = array(
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/$moduleName/resources/css/Workflow2.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/Workflow2.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/$moduleName/resources/css/Essentials.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/TaskConfig.css",
            "~/modules/$moduleName/views/resources/js/textcomplete/jquery.textcomplete.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}