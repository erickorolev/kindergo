<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

/* vt6 Ready 2014/04/09 */
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskSetter extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;

    protected $_frontendDynamical = true;

    public function init() {
        if(!empty($_GET['parent'])) {
            $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

            $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
            $asdf = new $className(basename(dirname((dirname(__FILE__)))), $moduleModel->version);
            $showRelatedField = $asdf->g1dd63e9ab62a68ac02f481ed3ba709207cb145ae()=='pr'.'o';
        } else {
            $showRelatedField = true;
        }

        $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
            'fromModule' => $this->getModuleName(),
            'toModule' => $this->getModuleName(),
            'refFields' => $showRelatedField
        ));
    }

    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        $setterMap = $this->get("setter");

        $this->fieldSetter->apply($context, $setterMap, null, $this);

        return "yes";
    }

}
