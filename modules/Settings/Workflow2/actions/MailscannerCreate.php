<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_MailscannerCreate_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();

        \Workflow\Mailscanner::checkCron();

        $sql = 'INSERT INTO vtiger_wf_mailscanner SET title = "New Mailscanner", workflow_id = 0, `provider_id` = 0, `condition` = "", active = 0';
        $adb->query($sql);

        echo json_encode(array('id' => VtUtils::LastDBInsertID()));

    }
}