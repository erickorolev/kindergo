<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_GetFieldList_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $tabid = getTabid($request->get('module_name'));

        if(empty($tabid)) exit();

        $fields = \Workflow\VtUtils::getFieldsWithBlocksForModule($request->get('module_name'));

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