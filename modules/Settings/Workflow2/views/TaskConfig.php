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

class Settings_Workflow2_TaskConfig_View extends Vtiger_Footer_View {

    function checkPermission(Vtiger_Request $request) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if(!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

	public function process(Vtiger_Request $request) {
		global $adb, $current_user;

        $adb->dieOnError = false;
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

        if (get_magic_quotes_gpc()) {
           $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
           while (list($key, $val) = each($process)) {
               foreach ($val as $k => $v) {
                   unset($process[$key][$k]);
                   if (is_array($v)) {
                       $process[$key][stripslashes($k)] = $v;
                       $process[] = &$process[$key][stripslashes($k)];
                   } else {
                       $process[$key][stripslashes($k)] = stripslashes($v);
                   }
               }
           }
           unset($process);
        }

       $taskID = intval($request->get("taskid"));

       $sql = "SELECT * FROM vtiger_wfp_blocks WHERE id = ?";
       $result = $adb->pquery($sql, array($taskID));

       if($adb->num_rows($result) == 0) {
           die("ERROR");
       }

        $configArray = $adb->fetch_array($result);

        $taskType = ucfirst(strtolower($configArray["type"]));

        $sql = "SELECT handlerclass, `file`, `module`, `helpurl`, repo_id FROM vtiger_wf_types WHERE `type` = '".preg_replace("/[^a-zA-z0-9]/", "", strtolower($taskType))."' OR `type` = '".preg_replace("/[^a-zA-z0-9]/", "", $configArray["type"])."' ";
        $result = $adb->query($sql, true);
        $taskObj = $adb->fetch_array($result);

        if(!empty($_GET['stefanDebug'])) {
            var_dump($sql, $taskObj);
        }

        if(!empty($taskObj["file"])) {
            require_once("modules/".$taskObj["module"]."/".$taskObj["file"]);
        } else {
           $taskDir = $root_directory."/modules/".$taskObj["module"]."/";

           if(!file_exists($taskDir."/tasks/".preg_replace("/[^a-zA-z0-9]/", "", $taskObj["handlerclass"]).".php")) {
               die("Classfile for task not found! [".$taskDir."/tasks/".preg_replace("/[^a-zA-z0-9]/", "", $taskObj["handlerclass"]).".php"."]");
               exit();
           }

           require_once($taskDir."tasks/".preg_replace("/[^a-zA-z0-9-_]/", "", $taskObj["handlerclass"]).".php");
       }

       //Zend_Json::$useBuiltinEncoderDecoder = true;

       $className = $taskObj["handlerclass"];

       /**
        * @var \Workflow\Task $obj
        */
       $obj = new $className($taskID, false, true);

       $taskFormMessage = "";
        $requestValues = $request->getAll();

        if($request->has("save") && $request->has("editID")) {
           $values = $requestValues["task"];
           $taskSettings = $requestValues["taskSettings"];

           $return = $obj->_beforeSave($values);

           // Only save, if the beforeSave Event didn't return false
           if($return !== false) {
               $currentUser = \Users_Record_Model::getCurrentUserModel();

               $json = \Workflow\VtUtils::json_encode($values); $json = str_replace(array("<!--?", "?-->"), array("<?", "?>"), $json);

               $sql = "UPDATE vtiger_wfp_blocks SET modified = NOW(), modified_by = ?, text = ?, settings = ?, active = ? WHERE id = ".$taskID;
               $adb->pquery($sql, array($currentUser->id, $taskSettings["text"], $json, $request->get("active") == "true" ? "1" : "0"), true);

               $successHint = true;
               /**
                * @var \Workflow\Task $obj
                */
               $obj = new $className($taskID,false, true);

               $envVars = $obj->getEnvironmentVariables();

               foreach($envVars as $varIndex => $varValue) {
                   $envVars[$varIndex] = str_replace('$env', '', $envVars[$varIndex]);
                   $envVars[$varIndex] = rtrim($envVars[$varIndex], '"\'');
                   $envVars[$varIndex] .= "'";

                   if(substr($envVars[$varIndex], 0, 2) != "['") {
                       $envVars[$varIndex] = "['".$varValue."'";
                   }
               }

               $sql = "UPDATE vtiger_wfp_blocks SET env_vars = ? WHERE id = ".$taskID;
               $adb->pquery($sql, array(str_replace('$env', "", str_replace("'", '"',implode("#~~#",$envVars)))), true);

               $obj->_afterSave();
           }


           Workflow2::$enableError = false;
           $syntaxValidator = $obj->validateSyntax();
           Workflow2::$enableError = true;

           if($syntaxValidator !== true) {
               $taskFormMessage = "<b>Found some syntax errors in custom Expressions:</b><br>".$syntaxValidator[1]." [Line ".$syntaxValidator[2]."]<br><div style='font-weight:bold;cursor:pointer;' onclick='jQuery(\"#wrongExpression\").toggle();'>show/hide wrong expression:</div><div style='display:none;' id='wrongExpression'>".htmlentities($syntaxValidator[3])."</div>";
               #var_dump($syntaxValidator);
           }
       }

//        ob_end_flush();
        ob_end_flush();

       $taskContent = $obj->getTaskform(array("hint" => array($taskFormMessage)));

       if($successHint == true) {
           echo "<script type='text/javascript'>opener.setBlockActive('".$taskID."', ".($_POST["active"]).");opener.setTaskText('".$taskID."', \"".addslashes($_POST["taskSettings"]["text"])."\");</script>";
       }

        echo $taskContent;
       #Workflow2::$enableError = false;
        #require_once("modules/Workflow2/admin.php");
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
            'libraries.bootstrap.js.eternicode-bootstrap-datepicker.js.bootstrap-datepicker',
            '~libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/locales/bootstrap-datepicker.'.Vtiger_Language_Handler::getShortLanguageName().'.js',
            '~libraries/jquery/timepicker/jquery.timepicker.min.js',
            '~modules/Workflow2/views/resources/js/textcomplete/jquery.textcomplete.min.js',
			"modules.$moduleName.views.resources.js.Essentials",
			"modules.Settings.$moduleName.views.resources.Workflow2",
			"modules.Settings.$moduleName.views.resources.TaskConfig",
            'modules.Vtiger.resources.Popup',
            'libraries.jquery.jquery_windowmsg',
            'modules.Vtiger.resources.List',
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
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $cssFileNames = array(
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/$moduleName/resources/css/Workflow2.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/Workflow2.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/$moduleName/resources/css/Essentials.css",
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/TaskConfig.css",
            "~/modules/$moduleName/views/resources/js/textcomplete/jquery.textcomplete.css",
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
}