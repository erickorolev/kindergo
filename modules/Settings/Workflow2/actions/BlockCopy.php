<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_BlockCopy_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));
        $ids = $request->get('blockids');

        $data = array();
        $sql = 'SELECT text, `type`, settings, colorlayer, x, y FROM vtiger_wfp_blocks WHERE id = ?';
        $idRelationship = array();

        foreach($ids as $id) {
            $result = $adb->pquery($sql, array($id));
            $tmp = $adb->fetchByAssoc($result);
            foreach($tmp as $key => $value) {
                $tmp[$key] = html_entity_decode($value);
            }
            $data[] = $tmp;
            $idRelationship[$id] = count($data) - 1;
        }

        $connections = array();

        $sqlConnection = 'SELECT source_id,source_key,destination_id,destination_key FROM vtiger_wfp_connections WHERE workflow_id = ? AND source_mode = "block" AND deleted = 0 AND (source_id IN ('.generateQuestionMarks($ids).') AND destination_id IN ('.generateQuestionMarks($ids).'))';
        $result = $adb->pquery($sqlConnection, array($workflowID, $ids, $ids));
        while($row = $adb->fetchByAssoc($result)) {
            $row['source_id'] = $idRelationship[$row['source_id']];
            $row['destination_id'] = $idRelationship[$row['destination_id']];
            $connections[] = $row;
        }

        $randHash = md5(microtime(true).rand(10000,99999));
        $_SESSION['_copy_'.$randHash] = array('blocks' => $data, 'connections' => $connections);

        setcookie('lastCopyHash', $randHash);

        echo $randHash;
   }
}