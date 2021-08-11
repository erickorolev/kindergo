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

use Workflow\VtUtils;
use Workflow\Main;

class Settings_Workflow2_Statistic_View extends Settings_Workflow2_Config_View {

    protected $isReadonly = false;
    protected $workflowData = false;
    protected $qualifiedModuleName = false;
    protected $moduleName = false;
    /**
     * @var bool|Settings_Workflow2_Module_Model
     */
    protected $settingsModel = false;

    public function preProcessDisplay (Vtiger_Request $request) {
        $this->initView($request);

        $viewer = $this->getViewer($request);

        $viewer->assign('MODE', 'Statistic');

        $this->initView($request);

        $viewer = $this->getViewer($request);

        $selectedMenuId = $request->get('block');
        $fieldId = $request->get('fieldid');
        $settingsModel = Settings_Vtiger_Module_Model::getInstance();
        $menuModels = $settingsModel->getMenus();

        if(!empty($selectedMenuId)) {
            $selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($selectedMenuId);
        } elseif(!empty($this->moduleName) && $this->moduleName != 'Vtiger') {
            $fieldItem = Settings_Vtiger_Index_View::getSelectedFieldFromModule($menuModels,$this->moduleName);
            if($fieldItem){
                $selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($fieldItem->get('blockid'));
                $fieldId = $fieldItem->get('fieldid');
            } else {
                reset($menuModels);
                $firstKey = key($menuModels);
                $selectedMenu = $menuModels[$firstKey];
            }
        } else {
            reset($menuModels);
            $firstKey = key($menuModels);
            $selectedMenu = $menuModels[$firstKey];
        }

        $statistic_from = date('Y-m-d', strtotime('-1 weeks'));
        $statistic_from_display = DateTimeField::convertToUserFormat($statistic_from);

        $statistic_to = date('Y-m-d', time());
        $statistic_to_display = DateTimeField::convertToUserFormat($statistic_to);

        $viewer->assign('STATISTIC_FROM', $statistic_from);
        $viewer->assign('STATISTIC_FROM_DISPLAY', $statistic_from_display);
        $viewer->assign('STATISTIC_TO', $statistic_to);
        $viewer->assign('STATISTIC_TO_DISPLAY', $statistic_to_display);

        $viewer->assign('SELECTED_FIELDID',$fieldId);
        $viewer->assign('SELECTED_MENU', $selectedMenu);
        $viewer->assign('SETTINGS_MENUS', $menuModels);
        $viewer->assign('MODULE', $this->moduleName);
        $viewer->assign('QUALIFIED_MODULE', $this->qualifiedModuleName);

        $viewer = $this->getViewer($request);
        $displayed = $viewer->view($this->preProcessTplName($request), $request->getModule(false));

        //$viewer->view('ConfigMenuStart.tpl', $this->qualifiedModuleName);
    }
/*
    public function preProcessSettings (Vtiger_Request $request) {


   		$viewer->view('StatisticMenuStart.tpl', $this->qualifiedModuleName);

   	}
*/
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.views.resources.Statistic",
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
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/$moduleName/resources/css/Essentials.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);


        foreach($headerStyleInstances as $obj) {
            $src = $obj->get('href');
            if(!empty($src) && strpos($src, $moduleName) !== false) {
                $obj->set('href', $src.'?v='.$moduleModel->version);
            }
        }

        return $headerStyleInstances;
    }

    public function process(Vtiger_Request $request) {
           $viewer = $this->getViewer($request);

           $viewer->view('VT7/Statistic.tpl', $this->qualifiedModuleName);
   	}

}