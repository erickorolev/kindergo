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

class Settings_Workflow2_FrontendWorkflowEditor_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }
    public function process(Vtiger_Request $request) {
        global $current_user, $vtiger_current_version;
        $adb = \PearDatabase::getInstance();

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $id = intval($request->get('id'));
        $frontendWorkflowObj = new \Workflow\FrontendWorkflows($id);
        $data = $frontendWorkflowObj->getData();

        $fields = \Workflow\VtUtils::getFieldsWithBlocksForModule($data['module_name'], false);

        $preset = new \Workflow\Preset\Condition('condition', null, array(
            'fromModule' => $data['module_name'],
            'toModule' => $data['module_name'],
            'enableHasChanged' => true,
            'container' => 'conditionalPopupContainer',
            'references' => false,
            'operators' => \Workflow\FrontendCondition::getOperators()
        ));

        $preset->beforeGetTaskform(array(
                array(
                    'condition' => $data['condition']
                ),
                $viewer
            )
        );

        $viewer->assign('toModule', $data['module_name']);
        $viewer->assign('javascript', $preset->getInlineJS());

        $viewer->assign('config', $data);
        $viewer->assign('fields', $fields);

        $viewer->view('VT7/FrontendWorkflowEditor.tpl', $qualifiedModuleName);
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
            "modules.Settings.$moduleName.views.resources.FrontendWorkflows",
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