<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ObjectAdd_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");
        $type = $request->get('type');

        switch($type) {
            case "text":
                $position = $settingsModel->getFreeBlockPos($workflowID);
                $text = "Sample Text";

                $sql = "INSERT INTO vtiger_wf_objects SET x = ?, y = ?, type = ?, content = ?, workflow_id = ?";
                $result = $adb->pquery($sql, array($position[1], $position[0], "text", $text, $workflowID), true);

                $newId = \Workflow\VtUtils::LastDBInsertID();

                $return = array("id" => "workflowDesignerObject_".$newId."", "content" => "<div  style='top:".$position[0]."px;left:".$position[1]."px;' id='workflowDesignerObject_".$newId."' class='workflowDesignerObject_text'>".$text."</div>");
            break;
        }

        echo json_encode($return);

        \Workflow2::updateWorkflow($workflowID);

   }
}