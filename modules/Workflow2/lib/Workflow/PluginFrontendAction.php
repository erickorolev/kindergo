<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 16.11.2016
 * Time: 15:24
 */

namespace Workflow;

class PluginFrontendAction
{
    private static $Registrations = array(
        'standalone' => array(),
        'simple' => array()
    );

    public static function registerSimple($label, $type, $function, $config = array(), $hint = '') {
        if(!is_callable($function)) {
            return false;
        }

        self::$Registrations['simple'][$type] = array(
            'label' => $label,
            'function' => $function,
            'config' => $config,
            'hint' => $hint
        );
    }

    public static function registerStandalone($type, $function) {
        if(!is_callable($function)) {
            return false;
        }

        self::$Registrations['standalone'][$type] = array(
            'function' => $function
        );
    }

    public function loadActions() {
        if(empty(self::$Registrations['simple'])) {
            $alle = glob(dirname(__FILE__) . '/../../extends/frontendactions/*.inc.php');
            foreach ($alle as $datei) {
                include_once(realpath($datei));
            }
        }
    }
    public function getCodes() {
        $this->loadActions();

        $return = array();
        foreach(self::$Registrations['standalone'] as $type => $function) {
            $return[$type] = call_user_func($function['function']);
        }
        foreach(self::$Registrations['simple'] as $type => $function) {
            $return[$type] = call_user_func($function['function']);
        }

        return $return;
    }

    public function getSimpleCodes() {
        $this->loadActions();

        $return = array();
        foreach(self::$Registrations['simple'] as $key => $config) {
            $return[$key] = array(
                'label' => $config['label'],
                'config' => $config['config'],
                'hint' => $config['hint']
            );
        }

        return $return;
    }

    public function generateScripts() {
        $actions = $this->getCodes();
        if(empty($actions)) return '';

        $script = 'var WorkflowFrontendActions = {};';
        foreach($actions as $action => $code) {
            $script .= 'WorkflowFrontendActions["'.$action.'"] = function(config, callback) {
                '.$code.'
            };';
        }

        return $script;
    }
}