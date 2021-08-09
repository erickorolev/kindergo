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

class Settings_Workflow2_MailscannerEditor_View extends Settings_Workflow2_Default_View {

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

        $scannerId = $request->get('scanner');
        $obj = new \Workflow\Mailscanner($scannerId);

        if($request->has('savescanner')) {
            $data = $obj->setData($request->getAll());
        }

        $data = $obj->getData();

        $viewer->assign('data', $data);

        $provider = \Workflow\ConnectionProvider::getAvailableConfigurations('imap');
        $viewer->assign('provider', $provider);

        $sql = 'SELECT * FROM  vtiger_wf_mailscanner_done WHERE mailscanner_id = ? ORDER BY done DESC LIMIT 10';
        $result = $adb->pquery($sql, array($scannerId));
        $mails = array();
        while($row = $adb->fetchByAssoc($result)) {
            $mails[] = $row;
        }
        $viewer->assign('ProcessedMails', $mails);

        $sql = 'SELECT module_name, id, title FROM vtiger_wf_settings WHERE `trigger` = "WF2_MAILSCANNER" AND active = 1';
        $result = $adb->pquery($sql, array());

        $workflows = array();
        while ($row = $adb->fetchByAssoc($result)) {
            $workflows[] = $row;
        }
        $viewer->assign('workflows', $workflows);

        $folders = $obj->getImapFolders();
        $viewer->assign('imap_folders', $folders);

        $viewer->view('VT7/MailScanner.editor.tpl', $qualifiedModuleName);
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
            "~/modules/Settings/$moduleName/views/resources/Mailscanner.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}