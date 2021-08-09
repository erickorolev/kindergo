<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
/**s
 * INCLUDE Autoload
 */
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_WorkflowImporter_View extends Settings_Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
   		return true;
   	}

    public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);

        $modules = VtUtils::getEntityModules(true);
        $viewer->assign('modules', $modules);

        $viewer->view('VT7/WorkflowImporter.tpl',$qualifiedModuleName);
   	}

    function getHeaderScripts(Vtiger_Request $request) {

   	}

}
