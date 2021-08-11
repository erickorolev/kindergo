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

class Settings_Workflow2_CreateWorkflowPopup_View extends Settings_Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
   		return true;
   	}

    public function process(Vtiger_Request $request) {
        $adb = \PearDatabase::getInstance();

        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $targetModule = intval($request->get('targetModule'));

        $modules = VtUtils::getEntityModules(true);
        $modules[getTabId('Users')] = array('Users', getTranslatedString('Users', 'Users'));
        $viewer->assign('modules', $modules);

        $sql = "SELECT * FROM vtiger_wf_trigger WHERE deleted = 0 ORDER BY custom, `module`, `label`";
        $result = $adb->query($sql);

        $trigger = array();
        while($row = $adb->fetchByAssoc($result)) {
            $trigger[$row["custom"] == "1" ?
                getTranslatedString("LBL_CUSTOM_TRIGGER", "Settings:Workflow2") :
                getTranslatedString("LBL_SYS_TRIGGER", "Settings:Workflow2")
            ][$row["key"]] = getTranslatedString($row["label"], "Settings:".$row["module"]);
        }

        foreach($trigger as $key => $value) {
            asort($trigger[$key]);
        }

        $currentView = \Workflow\Options::get(0, 'default_view', 'module');
        if($currentView == 'folder') {
            $viewer->assign('folderView', true);
        } else {
            $viewer->assign('folderView', false);
        }

        $viewer->assign("presetFolderName", '');

        $viewer->assign("trigger", $trigger);
        $viewer->assign("targetModule", \Workflow\VtUtils::getModuleName($targetModule));

        $viewer->view('VT7/CreateWorkflowPopup.tpl',$qualifiedModuleName);
   	}

    function getHeaderScripts(Vtiger_Request $request) {

   	}

}

?>