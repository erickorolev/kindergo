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

class Settings_Workflow2_Mailscanner_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }
    public function process(Vtiger_Request $request) {
        global $current_user;
        global $root_directory;
        $adb = PearDatabase::getInstance();

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $asdf = new $className(basename(dirname((dirname(__FILE__)))), $moduleModel->version);
        $stage = $asdf->g1dd63e9ab62a68ac02f481ed3ba709207cb145ae();

        if($stage == "basic") {
            echo "<div style='text-align:center;text-transform:uppercase;padding:35px 0;font-weight:bold;'>Not available in BASIC Version.</div>";
            return;
        }

        $sql = 'SELECT vtiger_wf_settings.title, vtiger_wf_mailscanner.* 
                  FROM vtiger_wf_mailscanner 
                  LEFT JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_mailscanner.workflow_id)
                ORDER BY active DESC, vtiger_wf_mailscanner.title';
        $result = $adb->query($sql);

        $scanner = array();
        while($row = $adb->fetchByAssoc($result)) {
            $scanner[] = $row;
        }


        $viewer->assign('scanner', $scanner);

        $LogFiles = array();
        if(is_dir(vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'Workflow2' . DIRECTORY_SEPARATOR . 'Mailscanner-Log')) {
            $files = glob(vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'Workflow2' . DIRECTORY_SEPARATOR . 'Mailscanner-Log' . DIRECTORY_SEPARATOR . '*.log');

            foreach($files as $file) {
                if(filemtime($file) > time() - 86400 * 2) {
                    $LogFiles[basename($file)] = filesize($file);
                }
            }
        }
        $viewer->assign('LogFiles', $LogFiles);

        $viewer->view('VT7/MailScanner.list.tpl', $qualifiedModuleName);
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
            "modules.$moduleName.views.resources.js.Essentials",
            "modules.Settings.$moduleName.views.resources.Workflow2",
            "modules.Settings.$moduleName.views.resources.Mailscanner",
            '~modules/Workflow2/views/resources/js/jquery.form.min.js',
            //"modules.Settings.$moduleName.views.resources.ConditionPopup",
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
            "~/modules/$moduleName/views/resources/Essentials.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}