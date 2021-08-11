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

class Settings_Workflow2_Attachmentspopup_View extends Settings_Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    public function process(Vtiger_Request $request) {
        $adb = \PearDatabase::getInstance();

        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);

        $attachmentsModule = $request->get('attachmentsModule');

        $availableSpecialAttachments = \Workflow\Attachment::getAvailableOptions($attachmentsModule);
        $attachmentHTML = array();
        $attachmentJAVASCRIPT = array();

        foreach($availableSpecialAttachments as $item) {
            $attachmentHTML[] = '<div>'.$item['html'].'</div>';
            $attachmentJAVASCRIPT[] = !empty($item['script'])?$item['script']:'';
        }

        // implode the array to one string
        $viewer->assign('attachmentsField', $this->field);

        $viewer->assign('attachmentsHTML', implode("\n", $attachmentHTML));
        // transmit array to create single script tags
        $viewer->assign('attachmentsJAVASCRIPT', $attachmentJAVASCRIPT);

        $modules = VtUtils::getEntityModules(true);

        $viewer->view('helpers/AttachmentsPopup.tpl', $qualifiedModuleName);
    }

    function getHeaderScripts(Vtiger_Request $request) {

    }

}

?>