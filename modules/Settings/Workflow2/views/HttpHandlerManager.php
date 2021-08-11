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

class Settings_Workflow2_HttpHandlerManager_View extends Settings_Workflow2_Default_View {

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


        if($_GET["act"] == "newentry") {
           $sql = "INSERT INTO vtiger_wf_http_limits SET name = 'NEW', created = NOW()";
           $adb->query($sql);

           $id = \Workflow\VtUtils::LastDBInsertID();
           $sql = "UPDATE vtiger_wf_http_limits SET name = 'Limit ".$id."' WHERE id = ".$id;
           $adb->query($sql);

           echo "<script type='text/javascript'>window.location.href='index.php?module=Workflow2&action=settingsHTTPHandlerEditor&parenttab=Settings&edit_id=".$id."'</script>";
           exit();
       }

       $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $asdf = new $className(basename(dirname((dirname(__FILE__)))), $moduleModel->version);
        $stage = $asdf->g1dd63e9ab62a68ac02f481ed3ba709207cb145ae();

       if($stage == "basic") {
           echo "<div style='text-align:center;text-transform:uppercase;padding:35px 0;font-weight:bold;'>Not available in BASIC Version.</div>";
           return;
       } else {
           $sql = "SELECT * FROM vtiger_wf_http_limits ORDER BY name";
           $result = $adb->query($sql);

           $limits = array();
           while($row = $adb->fetchByAssoc($result)) {
                $sql = "SELECT * FROM vtiger_wf_http_limits_ips WHERE limit_id = ".$row["id"];
                $resultTMP = $adb->query($sql, true);
                $row['ips'] = array();
                while($ip = $adb->fetchByAssoc($resultTMP)) {
                    $row['ips'][] = $ip["ip"];
                }

                $sql = "SELECT vtiger_wf_http_limits_value.*, vtiger_wf_settings.title 'wf_title', vtiger_wf_trigger.label FROM
                          vtiger_wf_http_limits_value
                           LEFT JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_http_limits_value.value)
                           LEFT JOIN vtiger_wf_trigger ON(vtiger_wf_trigger.key = vtiger_wf_http_limits_value.value)
                      WHERE limit_id = ".$row["id"]." ORDER BY mode, title";
                $resultTMP = $adb->query($sql, true);
                $row['items'] = array();

                while($ip = $adb->fetchByAssoc($resultTMP)) {
                    $row['items'][] = ($ip["mode"]=="trigger"?"<strong>Trigger:</strong> ".$ip["label"]:"<strong>Workflow:</strong> ".$ip['value'].' - '.$ip["wf_title"]);
                }

               $limits[] = $row;
           }
           $viewer->assign('limits', $limits);

           while($row = $adb->fetchByAssoc($result)) {

           }
           echo "</table>";

       }

        /* Error Logs */

        $sql = 'SELECT * FROM vtiger_wf_http_logs WHERE created > "'.date('Y-m-d', time() - (86400 * 7)).'" ORDER BY created DESC LIMIT 100';
        $result = $adb->query($sql, true);
        $showLog = false;
        $logs = array();
        if($adb->num_rows($result) > 0) {
            $showLog = true;
        }
        while($row = $adb->fetchByAssoc($result)) {
            $logs[] = '['.\DateTimeField::convertToUserFormat($row['created']).']['.$row['ip'].'] '.$row['log'].PHP_EOL;
        }
        $viewer->assign('showLog', $showLog);
        $viewer->assign('logs', $logs);

        /* Render Page */

        $viewer->view('VT7/HttpHandlerManager.tpl', $qualifiedModuleName);

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
			"modules.Settings.$moduleName.views.resources.HttpHandlerManager",
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