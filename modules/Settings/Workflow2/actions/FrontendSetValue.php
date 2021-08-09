<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendSetValue_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $field = $request->get("field");
        $value = $request->get("value");

        $id = intval($request->get("id"));

        if(substr($field, 0, 7) == 'config-') {
            $sql = 'SELECT config FROM vtiger_wf_frontendmanager WHERE id = ? ';
            $result = $adb->pquery($sql, array($id));
            $config = $adb->fetchByAssoc($result);
            $config = \Workflow\VtUtils::json_decode(html_entity_decode($config['config']));

            $config[substr($field, 7)] = $value;
            $sql = 'UPDATE vtiger_wf_frontendmanager SET config = ? WHERE id = ?';
            $adb->pquery($sql, array(\Workflow\VtUtils::json_encode($config), $id), true);
        } else {
            $fields = array('position', 'color', 'label', 'listview');
            if (!in_array($field, $fields)) {
                return;
            }

            $sql = 'UPDATE vtiger_wf_frontendmanager SET ' . $field . ' = ? WHERE id = ?';
            $adb->pquery($sql, array($value, $id), true);

            $objFM = new \Workflow\FrontendManager();

            if($field == 'position') {
                $objFM->checkListViewBasic();
            }

            if($field == 'label' || $field == 'color') {
                $sql = 'SELECT position FROM vtiger_wf_frontendmanager WHERE id = ? AND position = "listviewbtn"';
                $result = $adb->pquery($sql, array($id));

                if($adb->num_rows($result) > 0) {
                    $objFM->checkListViewBasic();
                }
            }
        }

        /**
         * @var $workflowObj Workflow2_Module_Model
         */
        $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
        $workflowObj->refreshFrontendJs();
    }
}

