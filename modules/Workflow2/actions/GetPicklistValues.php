<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_GetPicklistValues_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();

        $picklist = $params["picklist"];

        $values = GetAllPicklistValues($picklist);

        die(json_encode($values));
    }
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}
?>