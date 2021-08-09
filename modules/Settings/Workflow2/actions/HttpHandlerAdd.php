<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_HttpHandlerAdd_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $sql = "INSERT INTO vtiger_wf_http_limits SET name = 'NEW', created = NOW()";
        $adb->query($sql);

        $id = \Workflow\VtUtils::LastDBInsertID();

        $options = array(
              'handler_path' => 'modules/Workflow2/HTTPHandler.php',
              'handler_class' => 'Workflow2_HTTPHandler_Handler',
              'handler_function' => 'handle',
              'handler_data' => array(
                'handlerid'=>$id,
            )
        );
        $trackURL = Vtiger_ShortURL_Helper::generateURL($options);

        $sql = "UPDATE vtiger_wf_http_limits SET name = 'Limit " . $id . "', url = '" . $trackURL . "' WHERE id = ".$id;
        $adb->query($sql, true);

        echo json_encode(array('id' => $id));
    }
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}