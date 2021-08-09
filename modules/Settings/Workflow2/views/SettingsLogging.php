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

class Settings_Workflow2_SettingsLogging_View extends Settings_Workflow2_Default_View {

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

        /**
         * @var $settingsModel Settings_Colorizer_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);

        if($request->has("save")) {
            $sql = "UPDATE vtiger_wf_config SET error_handler = ?, error_handler_value = ?, log_handler = ?, log_handler_value = ?, minify_logs_after = ?, remove_logs_after = ?";
            $adb->pquery($sql, array($request->get("error_handler"), $request->get("error_handler_value"), $request->get("log_handler"), $request->get("log_handler_value"), $request->get("minify_logs_after"), $request->get("remove_logs_after")));
        }
        if(!empty($_GET['debug'])) {
            if($_GET['debug'] == 'enable') {
                setcookie('WFDDebugMode', 1, time() + 3600);
                $_COOKIE['WFDDebugMode'] = 1;
                header('Location:index.php?module=Workflow2&view=SettingsLogging&parent=Settings');
                exit();
            } else {
                setcookie('WFDDebugMode', '', time() - 3600);
                unset($_COOKIE['WFDDebugMode']);
                header('Location:index.php?module=Workflow2&view=SettingsLogging&parent=Settings');
                exit();
            }
        }

        $sql = "SELECT * FROM  vtiger_wf_config LIMIT 1";
        $result = $adb->query($sql);
        $rowConfig = $adb->fetchByAssoc($result);

        if($rowConfig["log_handler"] == "file" && $_GET["clearAllLog"] == "1") {
            file_put_contents($rowConfig["log_handler_value"], "");
        }
        if($rowConfig["log_handler"] == "table" && $_GET["clearAllLog"] == "1") {
            $sql = "TRUNCATE TABLE `vtiger_wf_logtbl`;";
            $adb->query($sql);
        }

        $logs = '';
        if($rowConfig["log_handler"] == "file") {
            if(filesize($rowConfig["log_handler_value"]) < (1048 * 100)) {
                $logs = '<pre>' . file_get_contents($rowConfig["log_handler_value"]) . '</pre>';
            } else {
                $logs = '<pre>Logfile too big to view in browser</pre>';
            }
        }

        if($rowConfig["log_handler"] == "table") {
            $sql = "SELECT * FROM vtiger_wf_logtbl ORDER BY date LIMIT 500";
            $result = $adb->query($sql);
            $logs = "<pre>";
            while($row = $adb->fetchByAssoc($result)) {
                $logs .= "[".$row["date"]."] - ".str_pad($row["log"], 20)." # Wf: ".str_pad($row["workflow"], 6)." # Block:".str_pad($row["blockid"], 6)." # CrmID: ".str_pad($row["crmid"], 10)."\n";
            }
            $logs .= '</pre>';
        }

        if(!empty($_COOKIE['WFDDebugMode'])) {
            $viewer->assign('DEBUG', true);
        } else {
            $viewer->assign('DEBUG', false);
        }

        $viewer->assign('logs', $logs);
        $viewer->assign('config', $rowConfig);

        $viewer->view('VT7/SettingsLogging.tpl', $qualifiedModuleName);
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