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

class Settings_Workflow2_SettingsScheduler_View extends Settings_Workflow2_Default_View {

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

        $sql = 'SELECT laststart FROM vtiger_cron_task WHERE name = "Workflow2 Queue"';
        $result = $adb->query($sql);
        $cronCheck = $adb->query_result($result, 0, 'laststart');
        if($cronCheck < time() - 86400) {
            $viewer->assign("SHOW_CRON_NOTICE", true);
        } else {
            $viewer->assign("SHOW_CRON_NOTICE", false);
        }

        $sql = 'SELECT
                    vtiger_wf_scheduler.*,
                    vtiger_wf_settings.title as workflow_title
                FROM
                    vtiger_wf_scheduler
                    LEFT JOIN vtiger_wf_settings
                        ON (vtiger_wf_settings.id = vtiger_wf_scheduler.workflow_id)
                    ORDER BY id';
        $result = $adb->query($sql);

        $schedules = array();
        while($row = $adb->fetchByAssoc($result)) {
            if($row['timezone'] == 'default') {
                $row['timezone_display'] = vglobal('default_timezone');
            } else {
                $row['timezone_display'] = 'UTC';
            }

            $row['next_execution'] = \DateTimeField::convertToUserFormat($row['next_execution']);

            $schedules[] = $row;
        }

        $viewer->assign('schedules', $schedules);

        $sql = "SELECT * FROM vtiger_wf_settings WHERE active = 1 AND module_name != ''";
        $result = $adb->query($sql);

        while($row = $adb->fetchByAssoc($result)) {
            $workflows[getTranslatedString($row['module_name'], $row['module_name'])][$row['id']] = $row;
        }

        ksort($workflows);
        foreach($workflows as $index => $workflow) {
            natcasesort($workflows[$index]);
        }


        $viewer->assign('workflows', $workflows);

        $old = date_default_timezone_get();
        date_default_timezone_set("UTC");
        $viewer->assign('currentUTCTime', date("H:i:s", time()));
        date_default_timezone_set($old);

        $viewer->assign('DEFAULT_TIMEZONE', vglobal('default_timezone'));
        $viewer->view('VT7/SettingsScheduler.tpl', $qualifiedModuleName);
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
			"modules.Settings.$moduleName.views.resources.Scheduler",
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
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/css/Workflow2.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/$moduleName/resources/css/Essentials.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}