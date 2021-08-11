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

class Settings_Workflow2_CoreWFImport_View extends Settings_Workflow2_Default_View {
    function checkPermission(Vtiger_Request $request) {
        return true;
   	}
	public function process(Vtiger_Request $request) {
        global $current_user, $vtiger_current_version;
        $adb = \PearDatabase::getInstance();

        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $sql = 'SELECT * FROM com_vtiger_workflows ORDER BY summary';
        $result = $adb->query($sql);
        $workflows = array();
        while($row = $adb->fetchByAssoc($result)) {
            $workflows[$row['module_name']][] = $row;
        }

        $viewer->assign('workflows', $workflows);
        $viewer->view('CoreWFImport.tpl', $qualifiedModuleName);
    }
}
