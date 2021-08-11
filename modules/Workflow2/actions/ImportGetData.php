<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_ImportGetData_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        global $current_user;

        $ImportHash = $request->get('ImportHash');

        $objImporter = \Workflow\Importer::getInstance($ImportHash);

        $return = array(
            'totalrows' => $objImporter->getTotalRows(true),
        );

        echo \Workflow\VtUtils::json_encode($return);
    }

    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}
