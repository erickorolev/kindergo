<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_BlockTextImport_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = $request->get("workflow_id");
        $position = $request->get('position');
        $data = $request->get('data');

        $data = trim($data);
        $check = substr($data, 0, 32);
        $data = substr($data, 32);
        if(md5($data) != $check) {
            echo 'Checksum';
            exit();
        }

        $data = base64_decode($data);
        $data = gzuncompress($data);
        $data = unserialize($data);

        $position['x'] += 85;
        $position['y'] += 50;

        $minY = PHP_INT_MAX;
        $minX = PHP_INT_MAX;

        /**
         * @var $settingsModel Settings_Workflow2_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance("Settings:Workflow2");

        $designerObj = new \Workflow\Designer();

        foreach($data['blocks'] as $block) {
            if($block['y'] < $minY) {
                $minY = $block['y'];
            }
            if($block['x'] < $minX) {
                $minX = $block['x'];
            }

            $sql = 'SELECT type FROM vtiger_wf_types WHERE type = ?';
            $result = $adb->pquery($sql, array($block['type']));

            if($adb->num_rows($result) == 0) {
                throw new \Exception('Type '.$block['type'].' not found in this system! Please install first');
            }
        }

        $response = array('blocks' => array(), 'connections' => array());
        $idRelationshop = array();

        foreach($data['blocks'] as $index => $block) {
            $top = intval($position['y']) + ($block['y'] - $minY);
            $left = intval($position['x']) + ($block['x'] - $minX);

            //echo $top.'-'.$left.'->';
            list($top, $left) = $settingsModel->getFreeBlockPos($workflowID, $top, $left);
            //echo $top.'-'.$left.PHP_EOL;
            //continue;
            $sql = "INSERT INTO vtiger_wfp_blocks SET
            workflow_id = ".$workflowID.",
            active = 1,
            text = ?,
            `type` = ?,
            x = '".intval($left)."',
            y = '".intval($top)."',
            env_vars = ?,
            settings = ?,
            colorlayer = ?
        ";

            $adb->pquery($sql, array($block['text'], $block['type'], $block['settings'], $block['colorlayer']));

            $blockID = \Workflow\VtUtils::LastDBInsertID();

            $idRelationshop[$index] = $blockID;

            $outputPoints = $designerObj->getOutputPoints($block['type']);
            $personInputPoints= $designerObj->getPersonPoints($block['type']);

            $html = $designerObj->getBlockHtml($blockID, $block['type'], $top, $left);

            $response['blocks'][] = array(
                "blockID" => $blockID,
                'type' => $block['type'],
                "html" => $html,
                "outputPoints" => $outputPoints,
                "personPoints" => $personInputPoints
            );

        }

        foreach($data['connections'] as $connection) {
            $connection['source_id'] = $idRelationshop[$connection['source_id']];
            $connection['destination_id'] = $idRelationshop[$connection['destination_id']];

            $sql = 'INSERT INTO vtiger_wfp_connections SET workflow_id = ?, source_id = ?, source_key = ?, destination_id = ?, destination_key = ?';
            $adb->pquery($sql, array(
                $workflowID,
                $connection['source_id'],
                $connection['source_key'],
                $connection['destination_id'],
                $connection['destination_key'],
            ));

            $response['connections'][] = $connection;
        }

        \Workflow2::updateWorkflow($workflowID);

        echo \Workflow\VtUtils::json_encode($response);
    }
}