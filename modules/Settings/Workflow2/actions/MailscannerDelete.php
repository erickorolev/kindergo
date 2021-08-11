<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_MailscannerDelete_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();

        $mailNumber = $request->get('mailnumber');
        $markDone = $request->get('markdone') == 'true';

        $scannerId = intval($request->get('scannerid'));

        $sql = 'DELETE FROM vtiger_wf_mailscanner WHERE id = ?';
        $adb->pquery($sql, array($scannerId));

        $sql = 'DELETE FROM vtiger_wf_mailscanner_done WHERE mailscanner_id = ?';
        $adb->pquery($sql, array($scannerId));

        $sql = 'DELETE FROM vtiger_wf_mailscanner_folder WHERE mailscanner_id = ?';
        $adb->pquery($sql, array($scannerId));
    }
}