<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendWorkflowAdd_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $wfid = intval($request->get('wfid'));
        $status = intval($request->get('status'));

        $sql = 'INSERT INTO vtiger_wf_frontendtrigger SET `workflow_id` = ?, `active` = 0, `pageload` = 0, `condition` = "", `conditiontext` = "", `fields` = "", `sort` = 0';
        $adb->pquery($sql, array($wfid));

    }
}

