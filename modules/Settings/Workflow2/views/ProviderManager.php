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

class Settings_Workflow2_ProviderManager_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
   	}
	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $providers = \Workflow\ConnectionProvider::getAvailableProviders();
        $viewer->assign('providers', $providers);

        $sql = 'SELECT * FROM  vtiger_wf_provider ORDER BY type';
        $result = $adb->pquery($sql, array());

        while($row = $adb->fetchByAssoc($result)) {
            if (strpos($row['type'], '//') !== false) {

                $parts = explode('//', $row['type']);
                $row['title'] = '<strong>'.$providers[$parts[0]]['provider'][$row['type']] . '</strong> - '.$row['title'];
                $providerLabel[$parts[0]] = $providers[$parts[0]]['label'];
                $connections[$parts[0]][] = $row;
            } else {
                $providerLabel[$row['type']] = $providers[$row['type']];
                $connections[$row['type']][] = $row;
            }
        }

        $viewer->assign('providerLabel', $providerLabel);
        $viewer->assign('connections', $connections);

        $viewer->view('VT7/ProviderManager.tpl', $qualifiedModuleName);
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
			"modules.Settings.$moduleName.views.resources.Workflow2",
			"modules.Settings.$moduleName.views.resources.ProviderManager",
            '~modules/Workflow2/views/resources/js/jquery.form.min.js',
		);

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
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/css/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}