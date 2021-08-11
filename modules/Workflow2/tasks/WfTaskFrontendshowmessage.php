<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskFrontendshowmessage extends \Workflow\Task
{

    protected $_frontendDynamical = true;

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
        $adb = PearDatabase::getInstance();

        $type = $this->get('type', $context);
        $subject = $this->get('subject', $context);
        $message = $this->get('message', $context);
        $position = $this->get('position', $context);
        $timeout = intval($this->get('timeout', $context));

        \Workflow\FrontendActions::pushDirectaction('message', array(
            'position' => $position,
            'subject' => $subject,
            'message' => $message,
            'type' => $type,
            'timeout' => $timeout
        ));

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        $viewer->assign('isFrontendWorkflow', $this->getWorkflow()->isFrontendWorkflow());

        /* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
