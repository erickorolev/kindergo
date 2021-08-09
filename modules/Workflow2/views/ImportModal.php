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

class Workflow2_ImportModal_View extends Vtiger_BasicAjax_View {

    public function process(Vtiger_Request $request) {
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $module = $request->get('target_module');
        $crmid = (int)$request->get('target_record');

        $objWorkflow = new Workflow2();
        $ImportWorkflows = $objWorkflow->getWorkflowsForModule($module, 1, 'WF2_IMPORTER', true);

        if(is_writable(vglobal('root_directory')."/test/") === false) {
            $viewer->assign('SHOW_WARNING', true);
        } else {
            $viewer->assign('SHOW_WARNING', false);
        }

        $importer = \Workflow\Importer::create();

        $viewer->assign('ImportHash', $importer->getHash());
        $viewer->assign('Workflows', $ImportWorkflows);
        if(function_exists('mb_convert_encoding')) {
            $viewer->assign('ShowEncoding', true);
        } else {
            $viewer->assign('ShowEncoding', false);
        }

        $viewer->view("VT7/ImportModal.tpl", 'Workflow2');
    }
}
