<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.12.13 17:56
 * You must not use this file without permission.
 */
class Settings_Workflow2_Module_Model extends Settings_Vtiger_Module_Model {
    protected $_types = false;
    protected $_typeCats = false;

    public function getModuleBasicLinks() {
        $basicLinks[] = array(
            'linktype' => 'BASIC',
            'linklabel' => vtranslate('Language Downloader', 'Settings:Workflow2'),
            'linkurl' => 'index.php?module=Workflow2&view=LanguageManager&parent=Settings',
            'linkicon' => 'fa-language'
        );

        return $basicLinks;
    }
    /**
     * Export all information of the blocks you set in the parameter blockIds
     *
     * @param int $workflowID
     * @param array $blockIds
     */
    public function exportBlocks($workflowID, $blockIds) {
        $adb = \PearDatabase::getInstance();

        $ids = $blockIds;

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

        return array('blocks' => $data, 'connections' => $connections);
    }

    public function getFreeBlockPos($workflowID, $startTop = 80, $startLeft = 280) {
        $adb = PearDatabase::getInstance();
        $top = $startTop;
        $left = $startLeft;

        $sql = "SELECT workflow_id FROM vtiger_wfp_blocks WHERE workflow_id = ".$workflowID." AND ABS(x - ".intval($left).") < 60 AND ABS(y - '".intval($top)."') < 40";
        $result = $adb->query($sql);

        if($adb->num_rows($result) == 0) {
            return array($top, $left);
        }

        $found = false;

        do {
            $top += 40;
            $left += 80;

            $sql = "SELECT workflow_id FROM vtiger_wfp_blocks WHERE workflow_id = ".$workflowID." AND ABS(x - ".intval($left).") < 60 AND ABS(y - '".intval($top)."') < 40";
            $result = $adb->query($sql);

            if($adb->num_rows($result) == 0) {
                $found = true;
            }

            if($found == true) {
                $sql = "SELECT workflow_id FROM vtiger_wf_objects WHERE workflow_id = ".$workflowID." AND ABS(x - ".intval($left).") < 60 AND ABS(y - ".intval($top).") < 40";
                $result = $adb->query($sql);

                if($adb->num_rows($result) > 0) {
                    $found = false;
                }
            }

        } while ($found == false);

        return array($top, $left);
    }
    public function getDummyTask($task) {
        $this->_types[$task] = new \Settings_Workflow2_Type_Model(array(
            'visible' => false,
            "output" => false,
            "persons" => false,
            "text" => 'Task not found',
            "input" => true,
            "styleclass" => " hasBackgroundImage ErrorType",
            "background" => 'task_error',
            "backgroundFile" => '',
            "module" => 'Workflow2'
        ));

        return $this->_types[$task];
    }
    public function getTypes($moduleName = false, $executionTrigger = false) {
        if($this->_types !== false) {
            return $this->_types;
        }

        $this->_types = array();
        $adb = PearDatabase::getInstance();
        $this->_typeCats = array();

        $sql = "SELECT * FROM vtiger_wf_types ORDER BY category, sort, id";
        $result = $adb->query($sql);
        while($row = $adb->fetch_array($result)) {
            if(strlen($row["singlemodule"]) > 4 ) {
                $singleModule = \Workflow\VtUtils::json_decode(html_entity_decode($row["singlemodule"]));
                if(
                    count($singleModule) == 3 &&
                    in_array('Quotes', $singleModule) &&
                    in_array('SalesOrder', $singleModule) &&
                    in_array('Invoice', $singleModule)
                ) {
                    $singleModule = 'inventory';
                }
/*
                if(is_array($singleModule) && $moduleName !== false && !in_array($moduleName, $singleModule)) {
                    if(in_array('Inventory', $singleModule) !== false) {
                        $recordModel = \Vtiger_Module_Model::getInstance($moduleName);
                        if(!$recordModel instanceof \Inventory_Module_Model) {
                            continue;
                        }
                    } else {
                        if($executionTrigger != 'WF2_IMPORTER') {
                            continue;
                        } elseif(!in_array('CSVIMPORT', $singleModule)) {
                            continue;
                        }
                    }
                }
*/
            } else {
                $singleModule = array();
            }

            $this->_types[$row["type"]] = new Settings_Workflow2_Type_Model(array(
                'visible' => $row['type'] != 'start' ? true : false,
                "output" => strlen($row["output"]) > 4 ? \Workflow\VtUtils::json_decode(html_entity_decode($row["output"])) : false,
                "persons" => strlen($row["persons"]) > 4 ? \Workflow\VtUtils::json_decode(html_entity_decode($row["persons"])) : false,
                "text" => getTranslatedString($row["text"], "Settings:".$row["module"]),
                "input" => $row["input"] == "0" ? false : true,
                "styleclass" => $row["styleclass"]." hasBackgroundImage",
                "background" => $row["background"],
                "backgroundFile" => !empty($row["file"])?"Smarty/templates/modules/".$row["module"]."/".str_replace(".php", ".png", $row["file"]):"",
                "module" => $row["module"],
                'singleModule' => $singleModule
            ));

            if(!empty($row["category"]) && $this->_types[$row["type"]]->get('visible') == true) {
                $this->_typeCats[getTranslatedString($row["category"], "Settings:".$row["module"])][] = array($row["category"], $row["type"]);
            }
        }

        return $this->_types;
    }

    public function getTypeCats($moduleName = false) {
        if($this->_typeCats === false) {
            $this->getTypes($moduleName);
        }

        return $this->_typeCats;
    }

    public function getWorkflowObjects($workflowID) {
        $adb = PearDatabase::getInstance();
        $workflowID = intval($workflowID);

        $sql = "SELECT
                    *,
                    vtiger_users.last_name, vtiger_users.first_name, vtiger_users.user_name,
                    vtiger_wfp_objects.id as object_id
                FROM vtiger_wfp_objects
                    LEFT JOIN vtiger_users ON(vtiger_users.id = crmid)
                WHERE vtiger_wfp_objects.module_name = 'Users' AND workflow_id = ".$workflowID;
        $result = $adb->query($sql);

        $persons = array();

        while($row = $adb->fetch_array($result)) {
            $persons[] = array(
                "id" => "person__".$row["object_id"],
                "y" => $row["y"],
                "x" => $row["x"],
                "name" => trim($row["first_name"]." ".$row["last_name"]),
                "userid" => $row["crmid"]
            );
        }

        return $persons;
    }
    public function getWorkflowBlocks($workflowID) {
        global $adb;
        $workflowID = intval($workflowID);
        $sql = "SELECT *
                FROM vtiger_wfp_blocks
                WHERE workflow_id = ".$workflowID;
        $result = $adb->query($sql);

        $elements = array();

        while($row = $adb->fetch_array($result)) {
            $elements[] = array(
                "id" => "block__".$row["id"],
                "block_id" => $row["id"],
                "y" => $row["y"],
                "x" => $row["x"],
                "type" => $row["type"],
                "text" => $row["text"],
                "active" => $row["active"],
                "colorlayer" => $row["colorlayer"],
            );
        }

        return $elements;
    }

    public function getWorkflowConnections($workflowID, $deleted = false) {
        global $adb;

        $sql = "SELECT *
                FROM vtiger_wfp_connections
                WHERE workflow_id = ".$workflowID.($deleted==false?" AND deleted = 0":"");
        $result = $adb->query($sql);

        $connections = array();

        while($row = $adb->fetch_array($result)) {
            $connections[] = array(
               $row["source_mode"]."__".$row["source_id"]."__".$row["source_key"],
               "block__".$row["destination_id"]."__".$row["destination_key"],
                ($row["deleted"]=="1"?true:false)
            );
        }

        return $connections;
    }
}