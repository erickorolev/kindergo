<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_BlockAdd_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $baseTop = intval($request->get("top"));
        $block = $request->get("blockid");
        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

        $settings = "";
        $text = "";
        $duplicateId = $request->get("duplicateId");
        if(!empty($duplicateId)) {
            $sql = "SELECT * FROM vtiger_wfp_blocks WHERE id = ".intval($duplicateId);
            $result = $adb->query($sql);
            $duplicate = $adb->raw_query_result_rowdata($result, 0);

            $block = $duplicate["type"];

            if(strlen($duplicate["settings"]) > 4) {
                //Zend_Json::$useBuiltinEncoderDecoder = true;
                $settings = \Workflow\VtUtils::json_decode($duplicate["settings"]);
                $settings = \Workflow\VtUtils::json_encode($settings);
            }
            $text = $duplicate["text"]." Copy";
        }

        if(!empty($_SESSION["mWFB"])) {
            $sql = "SELECT COUNT(*) as num FROM vtiger_wfp_blocks WHERE workflow_id = ".$workflowID;
            $result = $adb->query($sql);if($adb->query_result($result, 0, "num") > $_SESSION["mWFB"]) { die("false"); }
        }

        list($top, $left) = $settingsModel->getFreeBlockPos($workflowID, $baseTop);
        $currentUser = \Users_Record_Model::getCurrentUserModel();

        $sql = "INSERT INTO vtiger_wfp_blocks SET
            workflow_id = ".$workflowID.",
            active = 1,
            text = ?,
            `type` = ?,
            x = '".intval($left)."',
            y = '".intval($top)."',
            settings = ?,
            env_vars = '',
            colorlayer = '',
			modified = NOW(),
			modified_by = ?
        ";
        $adb->pquery($sql, array($text, $block, $settings, $currentUser->id), true);


        $blockID = \Workflow\VtUtils::LastDBInsertID();

        \Workflow2::updateWorkflow($workflowID);
        /*
                $sql = "SELECT * FROM vtiger_wf_types WHERE `type` = '".$block."'";
                $result = $adb->query($sql);

                $type = $adb->raw_query_result_rowdata($result);
        */
//      $outputs = json_decode($type["output"], true);
//
        $designerObj = new \Workflow\Designer();

        $outputPoints = $designerObj->getOutputPoints($block);
        $personInputPoints= $designerObj->getPersonPoints($block);

//        $html = "";
//        $html .= '<div class="context-wfBlock wfBlock hasBackgroundImage '.(!empty($type["styleclass"])?" ".$type["styleclass"]:"").'" id="block__'.$blockID.'" style="display:none;top:'.intval($top).'px;left:'.intval($left).'px;'.(!empty($type["background"])?"background-image:url(modules/".$type["module"]."/icons/".$type["background"].".png);":"").''.(!empty($type["backgroundFile"])?"background-image:url(".$type["backgroundFile"].");":"").'"><span class="blockDescription">'.getTranslatedString($type["text"], $type["module"]).'<span style="font-weight:bold;" id="block__'.$blockID.'_description">'.(!empty($text)?'<br>'.$text.'':'').'</span></span>'.($block!="start"?'<div class="idLayer" style="display:none;">'.$blockID.'</div>':'').'<div data-color="" style="background-color:;" class="colorLayer">&nbsp;</div><img style="z-index:2;position:relative;" class="settingsIcon" src="modules/Workflow2/icons/settings.png"></div>';

        $html = $designerObj->getBlockHtml($blockID, $block, $top, $left);
        //$html .= '<div data-type="'.$block.'"class="context-wfBlock noselect wfBlock '.(!empty($type["styleclass"])?" ".$type["styleclass"]:"").'" id="block__'.$blockID.'" style="top:'.intval($top).'px;left:'.intval($left).'px;"><div class="imgElement '.(!empty($type["styleclass"])?" ".$type["styleclass"]:"").'" style="'.(!empty($type["background"])?"background-image:url(modules/".$type["module"]."/icons/".$type["background"].".png);":"").''.(!empty($type["backgroundFile"])?"background-image:url(".$type["backgroundFile"].");":"").'"></div><span class="blockDescription">'.getTranslatedString($type["text"], $type["module"]).'<span style="font-weight:bold;" id="block__'.$blockID.'_description">'.(!empty($text)?'<br>'.$text.'':'').'</span></span>'.($block!="start"?'<div class="idLayer" style="display:none;">'.$blockID.'</div>':'').'<div data-color="" style="background-color:;" class="colorLayer">&nbsp;</div><img style="z-index:2;position:relative;" class="settingsIcon" src="modules/Workflow2/icons/settings.png"></div>';

        $return = array(
            "blockID" => $blockID,
            "html" => $html,
            "outputPoints" => $outputPoints,
            "personPoints" => $personInputPoints
        );
        echo json_encode($return);
    }
}