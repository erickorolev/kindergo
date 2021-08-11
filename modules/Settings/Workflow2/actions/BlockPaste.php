<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_BlockPaste_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = $request->get("workflow");
        $copyHash = $request->get("hash");
        $position = $request->get('position');

        $position['x'] += 85;
        $position['y'] += 50;

        $data = $_SESSION['_copy_'.$copyHash];

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
            env_vars = '',
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