<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskChangecontext extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    public function init() {
        $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
            //'module' => VtUtils::getModuleName($parts[1]),
            'default' => 'condition',
            'moduleselect' => true
        ));
    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        $records = $this->RecordSources->getRecordIds($context);

        $environment = $context->getEnvironment();

        $currentInstance = \Workflow\ExecutionLogger::getCurrentInstance();

        // Loop all records
        foreach($records as $record) {
            \Workflow\ExecutionLogger::getCurrentInstance()->log('Execute with record ID '.$record);
            $tmpContext = \Workflow\VTEntity::getForId($record);
            $tmpContext->loadEnvironment($environment);

            $obj = new \Workflow\Main($this->getWorkflowId(), false, $context->getUser());
            $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
            $obj->setContext($tmpContext);
            $obj->isSubWorkflow(true);

            $nextTasks = $this->getNextTasks(array('special'));

            \Workflow\ExecutionLogger::setCurrentInstance($currentInstance);
            $obj->handleTasks($nextTasks, $this->getBlockId(), 'loop');
            \Workflow\ExecutionLogger::setCurrentInstance($currentInstance);

            $environment = $tmpContext->getEnvironment();

            unset($tmpContext);
            unset($obj);
        }

        // Restore Context
        $context->loadEnvironment($environment);
        \Workflow\ExecutionLogger::setCurrentInstance($currentInstance);

		return "next";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
