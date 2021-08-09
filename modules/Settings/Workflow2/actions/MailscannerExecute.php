<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_MailscannerExecute_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();

        $mailNumber = $request->get('mailnumber');
        $markDone = $request->get('markdone') == 'true';

        $scannerId = $request->get('scannerid');
        $obj = new \Workflow\Mailscanner($scannerId);

        if($markDone == false) {
            $obj->testRun();
        }

        $obj->execute($mailNumber);

        $counter = $obj->getCounterExecutedMails();

        echo \Workflow\VtUtils::json_encode(array('success' => true, 'counter' => $counter));
    }
}