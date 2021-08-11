<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskFrontendaction extends \Workflow\Task
{

    protected $_frontendDynamical = true;

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$action = $this->get('frontendaction');


        $config = $this->get('action');

        $resultConfig = array();
        foreach( $config[$action]['config'] as $key => $value) {
            $resultConfig[$key] = \Workflow\VTTemplate::parse($value, $context);
        }

        \Workflow\FrontendActions::pushDirectaction($action, $resultConfig);

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        $frontendActions = new \Workflow\PluginFrontendAction();

        $actions = $frontendActions->getSimpleCodes();

        $viewer->assign('actions', $actions);

        $viewer->assign('isFrontendWorkflow', $this->getWorkflow()->isFrontendWorkflow());

        /* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
