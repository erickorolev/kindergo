<?php
use \Workflow\VTEntity;

global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_WorkflowInfo_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    public function process(Vtiger_Request $request) {
        $workflowId = intval($request->get('workflow_id'));

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT withoutrecord, collection_process FROM vtiger_wf_settings WHERE id = ?';
        $result = $adb->pquery($sql, array($workflowId));

        $workflowInfo = $adb->fetchByAssoc($result);

        echo \Workflow\VtUtils::json_encode($workflowInfo);
    }

    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}

?>