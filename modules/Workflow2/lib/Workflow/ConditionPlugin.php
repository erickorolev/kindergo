<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 04.06.14 15:48
 * You must not use this file without permission.
 */
namespace Workflow;

abstract class ConditionPlugin extends Extendable {
    private $records = array();
    /**
     * @var \SWSearchPlusEventHandler
     */
    public static $tableHander = false;

    public static function init() {
        self::_init(dirname(__FILE__).'/../../extends/conditions/');
    }

    public static function getSQLCondition($key, $moduleName, $columnName, $config, $not = false) {
        $types = self::getAvailableOperators($moduleName);

        if(strpos($key, '/') === false) {
            $group = 'core';
        } else {
            $parts = explode('/', $key);
            $key = $parts[1];
            $group = $parts[0];
        }

        /**
         * @var $item ConditionPlugin
         */
        $item = self::getItem($group);

        return $item->generateSQLCondition($key, $columnName, $config, $not);
    }

    public static function checkCondition(VTEntity $context, $moduleName, $key, $fieldValue, $config, $checkConfig) {
        $void = self::getAvailableOperators($moduleName);

        if(strpos($key, '/') === false) {
            $group = 'core';
        } else {
            $parts = explode('/', $key);
            $key = $parts[1];
            $group = $parts[0];
        }

        /**
         * @var $item ConditionPlugin
         */
        $item = self::getItem($group);

        return $item->checkValue($context, $key, $fieldValue, $config, $checkConfig);
    }

    public static function addJoinTable() {

    }

    public static function getAvailableOperators($moduleName, $mode = 'field') {
        $items = self::getItems();

        $return = array();
        foreach($items as $item) {
            $configs = $item->getOperators($moduleName);

            foreach($configs as $key => $file) {
                if($mode == 'mysql' && isset($file['mysqlmode']) && $file['mysqlmode'] === false) {
                    continue;
                }
                if($mode == 'field' && isset($file['fieldmode']) && $file['fieldmode'] === false) {
                    continue;
                }

                $file['label'] = vtranslate($file['label'], 'Settings:Workflow2');
                $return[$item->getExtendableKey().'/'.$key] = $file;
            }
        }

        return $return;
    }

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>'))
     * @param $moduleName
     * @return mixed
     */
    abstract public function getOperators($moduleName);
    abstract public function generateSQLCondition($key, $columnName, $value, $not);
    abstract public function checkValue($context, $key, $fieldValue, $config, $checkConfig);

    public function isAvailable($moduleName) {
        return true;
    }

}

?>