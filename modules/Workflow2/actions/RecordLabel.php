<?php
use \Workflow\VTEntity;
use \Workflow\VTTemplate;

global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_RecordLabel_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    public function process(Vtiger_Request $request) {
        $adb = \PearDatabase::getInstance();

        $tmpId = $request->get('ids');
        $ids = array();
        foreach($tmpId as $id) {
            $ids[] = intval($id);
        }

        $sql = 'SELECT label, crmid FROM vtiger_crmentity WHERE crmid IN ('.implode(",", $ids).')';
        $result = $adb->query($sql);

        $return = array();
        while($row = $adb->fetchByAssoc($result)) {
            $return[$row['crmid']] = html_entity_decode($row['label']);
        }

        echo json_encode(array('result' => $return));
        exit();
    }
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}

?>