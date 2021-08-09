<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 28.09.2016
 * Time: 19:00
 */

namespace Workflow;


use Workflow\Interfaces\IInventoryLoader;

class InventoryLoader
{
    /**
     * @var IInventoryLoader
     */
    private static $_Registrations = array();
    private static $_Options = array();

    public static function register($class) {
        self::$_Registrations[] = $class;
    }

    static $_initialized = false;

    private function _init() {
        if(self::$_initialized === false) {
            $alle = glob(dirname(__FILE__).'/../../extends/inventoryloader/*.inc.php');
            foreach($alle as $datei) { include_once(realpath($datei)); }

            foreach(self::$_Registrations as $className) {
                /**
                 * @var $obj IInventoryLoader
                 */
                $obj = new $className();
                $loader = $obj->getAvailableLoader();

                foreach($loader as $key => $data) {
                    $data['handler'] = $className;
                    self::$_Options[md5($className.'##'.$key)] = $data;
                }
            }

        }
        self::$_initialized = true;
    }

    public function getAvailableLoader() {
        $this->_init();

        return self::$_Options;
    }

    public function getItems($key, $config, VTEntity $context) {
        $this->_init();

        /**
         * @var $Obj IInventoryLoader
         */
        $obj = new self::$_Options[$key]['handler']();

        $items = $obj->getItems($config, $context);

        return $items;
    }
}