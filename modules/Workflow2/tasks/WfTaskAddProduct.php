<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

require_once('WfTaskCreator.php');
/*
vt6 ready 2014/04/14
*/

class WfTaskAddProduct extends \Workflow\Task
{
    protected $_fields = array();
    protected $_hiddenValues = array();

    /**
     * @var \Workflow\Preset\ProductChooser
     */
    private $_productchooser = null;

    public function init() {
        $this->_productchooser = $this->addPreset("ProductChooser", "product", array(
            'module' => $this->getModuleName()
        ));
    }

    public function handleTask(&$context) {
        /* INSERT PRODUCT */
        $context = $this->_productchooser->addProducts2Entity($this->get('product'), $context, $context);
        $context->save();

        return 'yes';
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $viewer->assign("orig_module_name", $this->getModuleName());

    }

}
