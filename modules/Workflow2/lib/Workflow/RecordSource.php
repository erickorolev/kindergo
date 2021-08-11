<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 07.12.14 13:26
 * You must not use this file without permission.
 */
namespace Workflow;

abstract class RecordSource extends Extendable
{
    /**
     * Current Task
     *
     * @var Task
     */
    protected $_Task = null;

    /**
     * Complete Task configuration of current Task
     *
     * @var array
     */
    protected $_Data = array();

    /**
     * ModuleName, which the Result Records must have
     *
     * @var string
     */
    protected $_TargetModule = '';

    public static function init() {
        self::_init(dirname(__FILE__).'/../../extends/recordsource/');
    }

    public function setData($data) {
        $this->_Data = $data;
    }
    public function setTargetModule($moduleName) {
        $this->_TargetModule = $moduleName;
    }
    public static function getAvailableSources($moduleName) {
        $items = self::getItems();

        $return = array();
        foreach($items as $item) {
            /**
             * @var RecordSource $item
             */
            $configs = $item->getSource($moduleName);
            if(empty($configs)) continue;

            $configs['id'] = $item->getExtendableKey();
            $configs = array($configs);

            foreach($configs as $file) {
                $return[] = $file;
            }
        }


        usort($return, array('\\Workflow\\RecordSource', 'cmp'));

        return $return;

    }

    private static function cmp($a, $b)
    {
        return strcmp($a['sort'], $b['sort']);
    }

    public static function getRecords(\Workflow\VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false) {
        throw new \Exception('Not implemented');
    }

    public function setTask(Task $task) {
        $this->_Task = $task;
        if(empty($this->_TargetModule)) {
            $this->_TargetModule = $task->getModuleName();
        }
    }

    /** Record Source Config JS/CSS Files */
    public function getConfigHTML($data, $parameter) {
        return '';
    }
    public function getConfigInlineJS() {
        return '';
    }
    public function getConfigInlineCSS() {
        return '';
    }
    /*
    public function getConfigJSFiles() {
        return array();
    }
    */

    // return array(array('ID|PATH', 'ID or path to file', ['filename', 'filetype', ...]))
    abstract public function getQuery(\Workflow\VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false);

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>'))
     * @param $moduleName
     * @return mixed
     */
    abstract public function getSource($moduleName);

    public function beforeGetTaskform($viewer) {}

    public function filterBeforeSave($data) { return $data; }
}
