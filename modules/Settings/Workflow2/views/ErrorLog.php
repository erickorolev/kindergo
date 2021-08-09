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

class Settings_Workflow2_ErrorLog_View extends Vtiger_Popup_View {

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

        $workflowID = (int)$request->get('workflow_id');

        $sql = "SELECT * FROM vtiger_wf_errorlog WHERE workflow_id = ".$workflowID.' AND datum_eintrag >= "'.date('Y-m-d', time() - (14 * 86400)).'"';
        $result = $adb->query($sql);

        $viewer->assign("workflow_id", $workflowID);


        $errors = array();

        while($row = $adb->fetchByAssoc($result)) {
            $row["datum_eintrag"] = VtUtils::formatUserDate($row["datum_eintrag"]);
            $errors[] = $row;
        }

        $viewer->assign("errors", $errors);

        echo $viewer->view('ErrorLog.tpl',$qualifiedModuleName,true);
	}

}