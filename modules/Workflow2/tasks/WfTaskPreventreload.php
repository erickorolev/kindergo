<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskPreventreload extends \Workflow\Task
{
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        $this->getWorkflow()->preventReload();

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
