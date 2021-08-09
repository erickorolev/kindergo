<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_Vanilla_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $response = new Vtiger_Response();

        try {
            $response->setResult(array("success" => true));
        } catch(Exception $exp) {
            $response->setResult(array("success" => false, "error" => $exp->getMessage()));
        }

        $response->emit();
    }
}