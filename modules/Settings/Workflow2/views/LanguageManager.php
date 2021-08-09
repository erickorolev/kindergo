<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
$moduleName = basename(dirname(dirname(__FILE__)));
global $root_directory;
require_once($root_directory."/modules/".$moduleName."/autoload_wf.php");

class Settings_Workflow2_LanguageManager_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }
    public function process(Vtiger_Request $request) {
        global $current_user;
        global $root_directory;
        $adb = PearDatabase::getInstance();

        $viewer = $this->getViewer($request);

        $languages = array();
        $languages[] = 'it_it';

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $objLM = new \Workflow\SWExtension\LanguageManager($moduleName);
        if(!empty($_REQUEST['download'])) {
            $objLM->downloadLanguage($_REQUEST['download']);
        }

        $languages = $objLM->getLanguages();

        $languageDir = vglobal('root_directory').DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR;

        $languages = $languages['languages'];
        foreach($languages as $index => $lang) {
            if(!file_exists($languageDir.$lang['code'].DIRECTORY_SEPARATOR.$moduleName.'.php')) {
                $languages[$index]['update'] = true;
            } else {
                $langFileDownloaded = '0000-00-00 00:00:00';

                require($languageDir.$lang['code'].DIRECTORY_SEPARATOR.$moduleName.'.php');
                if($langFileDownloaded < $languages[$index]['updated_at']) {
                    $languages[$index]['update'] = true;
                }
            }
            $languages[$index]['updated_at'] = \DateTimeField::convertToUserFormat($lang['updated_at']);
        }

        $viewer->assign('languages', $languages);
        $viewer->view('VT7/LanguageManager.tpl', $qualifiedModuleName);
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
            "modules.Settings.$moduleName.views.resources.LicenseManager",
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
            "~/modules/Settings/$moduleName/views/resources/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}