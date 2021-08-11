<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_MailscannerTest_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();

        $scannerId = $request->get('scannerid');
        $obj = new \Workflow\Mailscanner($scannerId);

        $mails = array();

        while (count($mails) < 5) {
            $mail = $obj->getNextMail(true);
            if(empty($mail)) break;

            $mails[] = array(
                'subject' => $mail->getSubject(),
                'from' => htmlentities($mail->getFrom()->getAddress()),
                'date' => $mail->getDate()->format('Y-m-d H:i:s'),
                'size' => \Workflow\VtUtils::formatFilesize($mail->getSize())
            );
        };

        echo \Workflow\VtUtils::json_encode(array('success' => true, 'mails' => $mails));
    }
}