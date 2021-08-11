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

class Settings_Workflow2_StatistikPopup_View extends Settings_Vtiger_Index_View {

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

        $taskID = (int)$request->get('id');

        $sql = "SELECT * FROM vtiger_wfp_blocks WHERE id = ?";
        $result = $adb->pquery($sql, array($taskID));

        if($adb->num_rows($result) == 0) {
            die("ERROR");
        }

        $configArray = $adb->fetch_array($result);

        $taskType = ucfirst(strtolower($configArray["type"]));

        $sql = "SELECT handlerclass, `file`, `module`, `helpurl`  FROM vtiger_wf_types WHERE `type` = '".preg_replace("/[^a-zA-z0-9]/", "", strtolower($taskType))."'";
        $result = $adb->query($sql);
        $taskObj = $adb->fetch_array($result);

        if(!empty($taskObj["file"])) {
            require_once("modules/".$taskObj["module"]."/".$taskObj["file"]);
        } else {

            $taskDir = $root_directory."/modules/".$taskObj["module"]."/";

            if(!file_exists($taskDir."/tasks/".preg_replace("/[^a-zA-z0-9]/", "", $taskObj["handlerclass"]).".php")) {
                die("Classfile for task not found! [".$taskDir."/tasks/".preg_replace("/[^a-zA-z0-9]/", "", $taskObj["handlerclass"]).".php"."]");
                exit();
            }

            require_once($taskDir."tasks/".preg_replace("/[^a-zA-z0-9]/", "", $taskObj["handlerclass"]).".php");
        }

        $className = '\\' . $taskObj["handlerclass"];

        $execId = $request->get('execId');
        if(empty($execId)) {
            $execId = false;
        }

        /**
         * @var \Workflow\Task $obj
         */
        $obj = new $className($taskID);

        $obj->getStatistikForm($execId);

        //echo $viewer->view('StatistikPopup.tpl',$qualifiedModuleName,true);
	}

}