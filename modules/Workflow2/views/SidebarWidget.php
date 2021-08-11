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

class Workflow2_SidebarWidget_View extends Vtiger_BasicAjax_View {

    public function process(Vtiger_Request $request) {
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $module = $request->get('source_module');
        $crmid = (int)$request->get('record');

        /*if($module == 'Events') {
            $module = 'Calendar';
        }*/

        $TMPworkflows = Workflow2::getWorkflowsForModule($module);

        $workflows = array();
        foreach($TMPworkflows as $workflow) {
            if($workflow['invisible'] == '1') continue;
            $objWorkflow = new \Workflow\Main($workflow["id"]);
            if(($workflow["authmanagement"] == "0" || $objWorkflow->checkAuth("view")) && $objWorkflow->checkExecuteCondition($crmid)) {
                $workflows[] = $workflow;
            }
        }

        $sql = "SELECT vtiger_wf_settings.title, vtiger_wf_settings.authmanagement, vtiger_wfp_blocks.text, vtiger_wf_queue.nextStepTime, vtiger_wf_queue.workflow_id, vtiger_wf_queue.crmid, vtiger_wf_queue.execID, vtiger_wf_queue.block_id
                FROM
                    vtiger_wf_queue
                    INNER JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_queue.workflow_id)
                    INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_queue.block_id)
                WHERE vtiger_wf_queue.crmid = ".$crmid." AND hidden = 0";
        $waitingRST = $adb->query($sql, true);

        $waiting = array();
        while($row = $adb->fetchByAssoc($waitingRST)) {
            $waiting[] = $row;
        }

        $sql = 'SELECT * FROM vtiger_wf_frontend_config WHERE module = ?';
        $result = $adb->pquery($sql, array($module));
        if($adb->num_rows($result) > 0) {
            $frontendconfig = $adb->fetchByAssoc($result);
            $viewer->assign('show_listview', $frontendconfig['hide_listview'] == '0');
        } else {
            $viewer->assign('show_listview', true);
        }

        \Workflow\Sidebar::assignMessages($crmid, $viewer);

        $viewer->assign('isAdmin', $current_user->is_admin == 'on');

        $processSettings = array();
        foreach($workflows as $wf) {
            $processSettings[intval($wf['id'])] = $wf;
        }
        $viewer->assign('processSettings', $processSettings);

        $viewer->assign('waiting', $waiting);
        $viewer->assign('workflows', $workflows);
        $viewer->assign('source_module', $module);
        $viewer->assign('crmid', $crmid);

        $frontendManager = new \Workflow\FrontendManager();
        $buttons = $frontendManager->getByPosition($module, 'sidebar', $crmid);

        $viewer->assign('buttons', $buttons);

        $viewer->view("SidebarWidget.tpl", 'Workflow2');
    }
}

?>