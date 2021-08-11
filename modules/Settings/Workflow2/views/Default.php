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

class Settings_Workflow2_Default_View extends Settings_Vtiger_Index_View {

    public function preProcessDisplay (Vtiger_Request $request) {
        $qualifiedModuleName = $request->getModule(false);
   		$viewer = $this->getViewer($request);

        $this->moduleName = $request->getModule();
        $this->qualifiedModuleName = $request->getModule(false);
        $this->settingsModel = Settings_Vtiger_Module_Model::getInstance($this->qualifiedModuleName);
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $viewer->assign('VERSION', $moduleModel->version);
        $viewer->assign('VIEW', $request->get('view'));

        if($request->has('page')) {
            $viewer->assign('PAGE', $request->get('page'));
        } else {
            $viewer->assign('PAGE', '');
        }

        $viewer->assign('MODULE', $this->moduleName);
        $viewer->assign('QUALIFIED_MODULE', $this->qualifiedModuleName);

        global $adb;
        $sql = 'SELECT * FROM vtiger_wf_repository_types
                INNER JOIN vtiger_wf_types ON (vtiger_wf_types.type = vtiger_wf_repository_types.name AND vtiger_wf_types.repo_id = vtiger_wf_repository_types.repos_id)
                WHERE vtiger_wf_repository_types.version > vtiger_wf_types.version LIMIT 1
                ';

        $result = $adb->query($sql);
        if($adb->num_rows($result) > 0) {
            $viewer->assign('AVAILABLE_TASK_UPDATE', true);
        } else {
            $viewer->assign('AVAILABLE_TASK_UPDATE', false);
        }

        $em = new VTEventsManager($adb);
        $em->triggerEvent("redoo.wfd.sidebar", array());
        $adminMenu = \Workflow\AdminSidebar::getInstance();

        $menu = $adminMenu->getMenu();
        $viewer->assign('MENUITEMS', $menu);

        $viewer->assign('MODE', 'Overview');

        parent::preProcessDisplay($request);
   		//$viewer->view('IndexMenuStart.tpl', $qualifiedModuleName);
   	}

    function getHeaderCss(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = array(
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/Workflow2/resources/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}