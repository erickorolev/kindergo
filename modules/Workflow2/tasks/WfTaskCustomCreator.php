<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

require_once('WfTaskCreator.php');
/* vt6 ready */
class WfTaskCustomCreator extends WfTaskCreator
{
    protected $_javascriptFile = array("WfTaskSetter.js");
    protected $_fields = array();

    protected $_activityType = 'Event';
    protected $_customModule = "Calendar";
    protected $_hiddenValues = array();

    public function init() {
        if(!empty($this->_customModule)) {
            $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
                'fromModule' => $this->getModuleName(),
                'toModule' => $this->_customModule,
                'activityType' => $this->_activityType,
                'refFields' => false
            ));
        }
    }

    public function handleTask(&$context) {
        $setter = $this->get("setter");
        $this->set("new_module", $this->_customModule);

        if($setter != -1 && is_array($setter)) {
            foreach($this->_hiddenValues as $key => $value) {
                $setter[] = array("field" => $key, "mode" => "value", "value" => $value);
            }
            $this->set("setter", $setter);
        }

        return parent::handleTask($context);
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $tasktext = $this->get("setter");

        if($tasktext == -1) {
            $counter = 1;
            foreach($this->_fields as $field) {
                $startFields["".$counter] = array("field" => $field, "mode" => "value", "value" => "", "fixed" => true);
                $counter++;
            }
            $this->set("setter", $startFields);
        }

        require_once("WfTaskSetter.php");
//        WfTaskSetter::initSetterForm($viewer, $this, $this->_customModule, $this->getModuleName(), false, array("refFields" => false));

        $viewer->assign("new_module", $this->_customModule);

    }


}
