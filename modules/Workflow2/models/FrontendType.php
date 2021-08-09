<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

class Workflow2_FrontendType_Model
{
    const TYPE_ICON = 'icon';
    const TYPE_COLORPICKER = 'colorpicker';
    const TYPE_FIELDDSELECT = 'field';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_TEXTBOX = 'checkbox';

    private $Config = array();
    private $Key = '';
    private $Title = '';
    private $LangModule = '';
    private $HandlerPath = '';
    private $HandlerClass = '';

    private $JSRender = 0;

    /**
     * @param $key
     */
    public function setKey($key) {
        if(strlen($key) > 18) {
            throw new \Exception('Only 18 chars are allowed in Key');
        }
        $this->Key = $key;
    }

    /**
     * @param $moduleName
     */
    public function setRelatedName($moduleName) {
        $this->LangModule = $moduleName;
    }

    /**
     * @param $title
     */
    public function setTitle($title) {
        $this->Title = $title;
    }
/*
    public function enableJSRendering() {
        $this->JSRender = 1;
    }
    public function disableJSRendering() {
        $this->JSRender = 0;
    }
*/
    /**
     * @param $key
     * @param $label
     * @param $type1
     */
    public function addConfig($key, $label, $type, $defaultValue = '') {
        $this->Config[$key] = array(
            'label' => $label,
            'type' => $type,
            'default' => $defaultValue
        );
    }

    public function setEnvironmentHandler($class, $filepath) {
        $filepath = str_replace(vglobal('root_directory'), '', $filepath);

        if(class_exists($class) === false) {
            require_once(vglobal('root_directory') . DIRECTORY_SEPARATOR . $filepath);
        }
        if(class_exists($class) === false) {
            throw new \Exception('EnvironmentHandler class cannot be loaded');
        }
        $obj = new $class();

        if($obj instanceof \Workflow2_EnvironmentHandlerAbstract_Model === false) {
            throw new \Exception('EnvironmentHandler must extend Workflow2_EnvironmentHandlerAbstract_Model class');
        }

        $this->HandlerClass = $class;
        $this->HandlerPath = $filepath;
    }

    /**
     * @throws \Exception
     */
    public function save() {
        if(class_exists('\\Workflow2\\Autoload') === false) {
            require_once(vglobal('root_directory')."/modules/Workflow2/autoload_wf.php");

        }

        $adb = \PearDatabase::getInstance();

        if(empty($this->Key)) throw new \Exception('No Key defined');
        if(empty($this->Title)) throw new \Exception('No Title defined');
        if(empty($this->LangModule)) throw new \Exception('No related Module defined');


        $sql = 'SELECT `key` FROM vtiger_wf_frontendtype WHERE `key` = ?';
        $result = $adb->pquery($sql, array($this->Key), true);

        if($adb->num_rows($result) == 0) {
            $sql = 'INSERT INTO vtiger_wf_frontendtype SET jsrender = ?, title = ?, handlerpath = ?, handlerclass = ?, module = ?, options = ?, `key` = ?';
        } else {
            $sql = 'UPDATE vtiger_wf_frontendtype SET jsrender = ?, title = ?, handlerpath = ?, handlerclass = ?, module = ?, options = ? WHERE `key` = ?';
        }

        $adb->pquery($sql, array(
            $this->JSRender,
            $this->Title,
            $this->HandlerPath,
            $this->HandlerClass,
            $this->LangModule,
            VtUtils::json_encode($this->Config),
            $this->Key,
        ));

    }
}