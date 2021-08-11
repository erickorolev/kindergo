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

        $page = $request->get('page');
        $scannerId = intval($request->get('scannerid'));
        $offset = ($page - 1) * 25;

        $sql = 'SELECT * FROM vtiger_wf_mailscanner_done WHERE mailscanner_id = ? LIMIT 25 OFFSET '.$offset.' ORDER BY done DESC';
        $result = $adb->pquery($sql, array($scannerId));

        $mails = array();
        while($row = $adb->fetchByAssoc($result)) {
            $row['done'] = DateTimeField::convertToUserFormat($row['done']);

            $mails[] = $row;
        }

        $viewer->assign('mails', $mails);
        if($page == '1') {
            $viewer->view('MailscannerHistoryPopup.tpl', $qualifiedModuleName);
        } else {
            $viewer->view('MailscannerHistoryContent.tpl', $qualifiedModuleName);
        }
    }

    function getHeaderScripts(Vtiger_Request $request) {

    }

}

?>