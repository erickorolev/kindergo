<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_CreateWorkflow_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $moduleName = \Workflow\VtUtils::getModuleName(intval($request->get('workflow_module')));

        $trigger = $request->get('workflow_trigger');
        $folderName = $request->get('workflow_folder');

        \Workflow2::log(0,0,0, 'New WF '.$moduleName.' -'.intval($request->get('workflow_module')).'');

        $sql = 'SELECT MAX(sort) as sort FROM vtiger_wf_settings WHERE folder = ?';
        $result = $adb->pquery($sql, array($folderName));
        $sortOrder = $adb->query_result($result, 0, 'sort');

        $hash = md5(microtime(true));
        $sql = "INSERT INTO vtiger_wf_settings SET active = 0, module_name = ?, folder = ?, sort = ?";
        $adb->pquery($sql, array($moduleName, $folderName, $sortOrder + 1), true);

        $workflow_id = \Workflow\VtUtils::LastDBInsertID();

        $sql = "UPDATE vtiger_wf_settings SET title = 'Workflow ".$workflow_id."', `trigger` = '".$trigger."' WHERE id = ".$workflow_id;
        $adb->query($sql, true);

        $sql = "INSERT INTO vtiger_wfp_blocks SET workflow_id = ".$workflow_id.", active = 1, env_vars = '', type = 'start', `x` = 300, y = 300";
        $adb->query($sql, true);

        echo json_encode(array('id' => $workflow_id));
    }
}

?>