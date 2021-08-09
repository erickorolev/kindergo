<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready */

class WfTaskExecType extends \Workflow\Task
{
    protected $_ConfigTemplate = false;

    public function handleTask(&$context) {
        $this->addStat($this->getWorkflow()->getExecutionTrigger());
        if($this->getWorkflow()->getExecutionTrigger() == "WF2_MANUELL") {
            return "manu";
        } else {
            return "auto";
        }

    }
}
