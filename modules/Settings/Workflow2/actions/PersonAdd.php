<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_PersonAdd_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

        $workflowID = (int)$request->get('workflow');

        $module_name = $_POST["module_name"];

        list($top, $left) = $settingsModel->getFreeBlockPos($workflowID);

        $sql = "INSERT INTO vtiger_wfp_objects SET
            workflow_id = ".$workflowID.",
            x = '".$left."',
            y = '".$top."',
            module_name = '".$module_name."'
        ";

        $adb->query($sql);

        $blockID = \Workflow\VtUtils::LastDBInsertID();

        echo json_encode(array(
            "element_id" => "person__".$blockID,
            "topPos" => $top,
            "leftPos" => $left,
        ));
   }
}


