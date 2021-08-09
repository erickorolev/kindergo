<?php


class Workflow2_Getter_Model
{
    public static function getWorkflows($selection = array()) {
        $adb = \PearDatabase::getInstance();

        $params = $where = array();

        foreach($selection as $key => $value) {
            switch($key) {
                case 'trigger':
                case 'active':
                case 'module_name':
                    $where[] = '`'.$key.'` = ?';
                    $params[] = $value;
                    break;
            }
        }

        $sql = 'SELECT * FROM vtiger_wf_settings WHERE '.implode(' AND ', $where);
        $result = $adb->pquery($sql, $params);

        $return = array();
        while($row = $adb->fetchByAssoc($result)) {
            $return[] = $row;
        }

        return $return;
    }

}