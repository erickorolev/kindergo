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

class Settings_Workflow2_FrontendWorkflowConfig_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }
    public function process(Vtiger_Request $request) {
        global $current_user, $vtiger_current_version;
        $adb = \PearDatabase::getInstance();

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $asdf = new $className(basename(dirname((dirname(__FILE__)))), $moduleModel->version);
        $stage = $asdf->g1dd63e9ab62a68ac02f481ed3ba709207cb145ae();

        if($stage == "basic") {
            echo "<div style='text-align:center;text-transform:uppercase;padding:35px 0;font-weight:bold;'>Not available in BASIC Version.</div>";
            return;
        } else {

            $moduleName = $request->getModule();
            $qualifiedModuleName = $request->getModule(false);
            $viewer = $this->getViewer($request);

            $sql = 'SELECT module_name, id, title FROM vtiger_wf_settings WHERE `trigger` = "WF2_FRONTENDTRIGGER" AND active = 1';
            $result = $adb->pquery($sql, array());

            $workflows = array();
            while ($row = $adb->fetchByAssoc($result)) {
                $workflows[$row['module_name']][] = $row;
            }
            $viewer->assign('workflows', $workflows);

            $configWFs = \Workflow\FrontendWorkflows::getAll();

            $viewer->assign('configWFs', $configWFs);
            $viewer->view('VT7/FrontendWorkflowConfig.tpl', $qualifiedModuleName);
        }
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
            "modules.$moduleName.views.resources.js.complexecondition",
            "modules.Settings.$moduleName.views.resources.FrontendWorkflows",
            '~modules/Workflow2/views/resources/js/jquery.form.min.js',
            "modules.$moduleName.views.resources.js.Essentials",
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
        return $headerStyleInstances;
    }
}