<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_WorkflowImport_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $vtiger_current_version;

        $adb = PearDatabase::getInstance();
        $parameter = $request->getAll();

        $content = file($_FILES["import"]["tmp_name"]);

        if(1==1||$content[1] == sha1(trim($content[0]))) {
            $data = base64_decode(trim($content[0]));

            if(!empty($parameter["password"])) {
                $content = @unserialize($data);
            } else {
                $content = @unserialize(gzuncompress($data));
            }


            if($content == false) {
                die(json_encode(array('result' => 'error', 'message' => 'Security Exception! Probably no Workflow File'.(!empty($parameter["password"])?' or WRONG password!':''))));
            } else {
                try{
                    \Workflow\Main::import($request->get("workflow_name"), $content, (!empty($parameter["workflow_module"]) ? $parameter["workflow_module"] : false));
                } catch (Exception $exp) {
                    die(json_encode(array('result' => 'error', 'message' => $exp->getMessage())));
                }

                die(json_encode(array('result' => 'ok')));
                // echo "<p style='margin:10px 50px;font-weight:bold;color:#188725;'>".getTranslatedString("LBL_IMPORT_SUCCESS", "Workflow2")."!</p>";
            }
        } else {
            die(json_encode(array('result' => 'error', 'message' => 'Security Exception! Probably no Workflow File')));
        }

        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        //$settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

    }

}