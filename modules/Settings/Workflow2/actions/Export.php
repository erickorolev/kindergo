<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_Export_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $vtiger_current_version;

        $adb = PearDatabase::getInstance();
        $workflowID = (int)$request->get("workflow");
        $password = $request->get('password', '');

        $workflow = new \Workflow\Main($workflowID);
        $data = $workflow->export();

        $hashValue = sha1("aPw94gtcA-.,-n".serialize($data["blocks"]).serialize($data["connections"]).serialize($data["objects"]));
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        if($request->get('version', '') != '') {
            $modulVersion = $request->get('version', $moduleModel->version);
        } else {
            $modulVersion = $moduleModel->version;
        }

        $data["main"] = array(
            "workflow_version" => $modulVersion,
            "vtiger_version" => $vtiger_current_version,
            "export_date" => date("Y-m-d H:i:s"),
            "hash" => sha1("aPw94gtcA-.,-n".$hashValue)
        );

        if(!empty($_REQUEST["version"])) {
            $data["main"]["workflow_version"] = preg_replace("/[^0-9.]/", "", $_REQUEST["version"]);
        }

        if(!empty($password)) {
            $content = \Workflow\SWExtension\Utils::encrypt(serialize($data), $password);
        } else {
            $content = gzcompress(serialize($data));
        }

        $content = base64_encode($content);

        $content = $content."\n".sha1($content);

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT\n");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

        header("Content-Disposition: attachment; filename=\"workflow_".intval($workflowID).".bin\";" );

        header("Content-Type: application/force-download");
        header('Content-Description: File Transfer');
        header("Content-Transfer-Encoding: binary");

        echo $content;

        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        //$settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

    }
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}