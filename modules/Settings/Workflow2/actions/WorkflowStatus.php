<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_WorkflowStatus_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $active = intval($request->get("value"));

        $sql = "UPDATE vtiger_wf_settings SET `active` = ? WHERE id = ?";
        $adb->pquery($sql, array($active, intval($workflowID)));

        if($active == '1') {
            $sql = 'SELECT module_name FROM vtiger_wf_settings WHERE id = ?';
            $result = $adb->pquery($sql, array(intval($workflowID)));
            $result = $adb->fetchByAssoc($result);

            $request->set('workflowModule', $result['module_name']);
            $request->set('hidden', true);
            $request->set('MODE', 'ADD');

            $sidebar = new Settings_Workflow2_SidebarToggle_Action();
            $sidebar->process($request);
        }

        /**
         * @var $workflowObj Workflow2_Module_Model
         */
        $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
        $workflowObj->refreshFrontendJs();

        if($active != '1') {
            $workflowObj = new \Workflow\Main($workflowID);
            $runningCounter = $workflowObj->countRunningInstances();

            if($runningCounter > 0) {
                echo json_encode(array('show_warning' => 1));
                exit();
            }
        }

        echo json_encode(array('show_warning' => 0));
        exit();

    }
}