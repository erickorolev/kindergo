<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

namespace Workflow;


class FrontendTypes
{
    public static function getAllAvailable() {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_frontendtype ORDER BY title';
        $result = $adb->query($sql);

        $types = array();

        while($row = $adb->fetchByAssoc($result)) {
            $row['title'] = vtranslate($row['title'], $row['module']);
            $row['options'] = VtUtils::json_decode(html_entity_decode($row['options']));

            $types[] = $row;
        }

        return $types;
    }

    public static function getType($type) {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_frontendtype WHERE `key` = ?';
        $result = $adb->pquery($sql, array($type));

        $row = $adb->fetchByAssoc($result);
        $row['title'] = vtranslate($row['title'], $row['module']);
        $row['options'] = VtUtils::json_decode(html_entity_decode($row['options']));

        return $row;
    }

    public static function getExtraEnvironment($currentEnvironment, $type, $crmid) {
        $type = self::getType($type);
        $crmid = intval($crmid);

        if(class_exists($type['handlerclass']) == false) {
            require_once(vglobal('root_directory') . $type['handlerpath']);
        }
        if(class_exists($type['handlerclass']) == false) {
            return array();
        }

        $classname = $type['handlerclass'];
        /**
         * @var \Workflow2_EnvironmentHandlerAbstract_Model $obj
         */
        $obj = new $classname();

        $return = $obj->retrieve($currentEnvironment, $crmid);

        if($return === null && !empty($currentEnvironment)) {
            $return = $currentEnvironment;
        }

        return $return;
    }

}