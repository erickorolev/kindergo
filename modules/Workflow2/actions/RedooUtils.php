<?php

require_once(vglobal('root_directory')."/modules/".basename(dirname(dirname(__FILE__)))."/autoload_wf.php");

class Workflow2_RedooUtils_Action extends Vtiger_Action_Controller {
    private $ModuleName = null; // Will Auto-Filled

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    function __construct() {
        $this->ModuleName = basename(dirname(dirname(__FILE__)));
        parent::__construct();

        $this->exposeMethod('GetFieldList');
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function GetFieldList(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $tabid = getTabid($request->get('module_name'));

        if(empty($tabid)) exit();

        $fields = call_user_func_array('\\Workflow\\VtUtils::getFieldsWithBlocksForModule', array($request->get('module_name')));

        $return = array();
        foreach($fields as $blockLabel => $fields) {
            foreach($fields as $field) {
                $return[$blockLabel][] = array(
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type->name
                );
            }
        }

        echo json_encode(array('success' => true, 'fields' => $return));
        exit();
    }
}