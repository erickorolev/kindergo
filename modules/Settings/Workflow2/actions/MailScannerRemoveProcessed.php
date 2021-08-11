<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_MailscannerRemoveProcessed_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {


        $scannerId = $request->get('scannerid');
        $processedId = $request->get('processedid');

        $obj = new \Workflow\Mailscanner($scannerId);
        $obj->removeProcessed($processedId);

        echo \Workflow\VtUtils::json_encode(array('success' => true));
    }
}