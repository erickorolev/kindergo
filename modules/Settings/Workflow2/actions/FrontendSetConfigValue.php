<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendSetConfigValue_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $field = $request->get("field");
        $value = $request->get("value");

        $module = $request->get("moduleName");

        $fields = array('hide_listview');
        if(!in_array($field, $fields)) {
            $sql = 'SELECT position FROM vtiger_wf_frontendmanager WHERE id = ? ';
            $result = $adb->pquery($sql, array($id));
            $config = $adb->fetchByAssoc($result);

            $type = \Workflow\FrontendTypes::getType($config['position']);
            var_dump($type, $field);
            if(!isset($type['options'][$field])) {
                echo 'not allowed';
                return;
            }
        }

        $sql = 'SELECT module FROM vtiger_wf_frontend_config WHERE module = ?';
        $result = $adb->pquery($sql, array($module));

        if($adb->num_rows($result) > 0) {
            $sql = 'UPDATE vtiger_wf_frontend_config SET '.$field.' = ? WHERE module = ?';
            $adb->pquery($sql, array($value, $module), true);
        } else {
            $sql = 'INSERT INTO vtiger_wf_frontend_config SET module = ?, '.$field.' = ?';
            $adb->pquery($sql, array($module, $value), true);
        }

        if($field == 'position') {
            $objFM = new \Workflow\FrontendManager();
            $objFM->checkListViewBasic();
        }

        $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
        $workflowObj->refreshFrontendJs();

    }
}

