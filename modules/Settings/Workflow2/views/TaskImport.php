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

class Settings_Workflow2_TaskImport_View extends Settings_Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
   	}
	public function process(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);

		$viewer = $this->getViewer($request);


        echo $viewer->view('VT7/TaskImport.tpl',$qualifiedModuleName,true);
	}

}