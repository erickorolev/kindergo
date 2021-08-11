<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 07.12.14 13:26
 * You must not use this file without permission.
 */
namespace Workflow;

abstract class FileAction extends Extendable
{
    public static function init() {
        self::_init(dirname(__FILE__).'/../../extends/fileactions/');
    }

    protected $BackendConfiguration = null;

    public static function getAvailableActions($moduleName, $configuration) {
        /**
         * @var $items FileAction[]
         */
        $items = self::getItems();

        $return = array();
        foreach($items as $item) {
            $item->setBackendConfiguration($configuration);

            /**
             * @var FileAction $item
             */
            $configs = $item->getActions($moduleName);
            $configs['id'] = $item->getExtendableKey();
            $configs = array($configs);

            foreach($configs as $file) {
                $return[] = $file;
            }
        }

        return $return;

    }

    public static function doActions($configuration, $filepath, $filename, $context, $targetRecordIds = array(), \Workflow\Main $workflow = null) {
        $key = $configuration['option'];
        $configuration = $configuration['config'];

        if(empty($key)) return;

        /**
         * @var $item FileAction
         */
        $item = self::getItem($key);


        $item->setWorkflow($workflow);

        if($item === false) {
            return array();
        }

        if(!is_array($targetRecordIds)) {
            $targetRecordIds = array($targetRecordIds);
        }

        return $item->doAction($configuration, $filepath, $filename, $context, $targetRecordIds);
    }

    /**
     * @param $configuration
     * @internal
     */
    private function setBackendConfiguration($configuration) {
        $this->BackendConfiguration = $configuration;
    }
    // return array(array('ID|PATH', 'ID or path to file', ['filename', 'filetype', ...]))
    abstract public function doAction($configuration, $filepath, $filename, $context, $targetRecordIds = array());

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>'))
     * @param $moduleName
     * @return mixed
     */
    abstract public function getActions($moduleName);
}

?>