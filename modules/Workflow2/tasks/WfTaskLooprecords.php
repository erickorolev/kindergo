<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskLooprecords extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    public function handleTask(&$context) {
        unset($_FILES);

        /* Insert here source code to execute the task */
        $records = $this->RecordSources->getRecordIds($context);

        $loopSettings = $this->get('loop');

        foreach($records as $recordId) {
            $loopContext = \Workflow\VTEntity::getForId($recordId);
            /**
             * @var \TrackableObject $data
             */
            $data = $loopContext->getData();
            if($data instanceof \TrackableObject) {
                $data = $data->getColumnFields();
            }
            $data['crmid'] = $data['id'] = $loopContext->getId();

            $this->addStat('Run Loop with '.$loopContext->getModuleName().' ID ' . $recordId . '');

            $logger = \Workflow\ExecutionLogger::getCurrentInstance();

            if (!empty($loopSettings['path'])) {

                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $context->setEnvironment('record', $data);
                $obj->setContext($context);
                $obj->isSubWorkflow(true);

                $nextTasks = $this->getNextTasks(array('loop'));

                $obj->handleTasks($nextTasks, $this->getBlockId(), 'loop');

            }

            if (!empty($loopSettings['expression'])) {
                $parser = new \Workflow\ExpressionParser($this->get('expression'), $context, false); # Last Parameter = DEBUG
                $context->setEnvironment('record', $data);

                try {
                    $parser->run();
                } catch (\Workflow\ExpressionException $exp) {
                    Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                }
            }

            if (!empty($loopSettings['workflow'])) {
                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $obj->setContext($context);
                $context->setEnvironment('record', $data);
                $obj->isSubWorkflow(true);

                $obj->start();
            }

            \Workflow\ExecutionLogger::setCurrentInstance($logger);
            $context->clearEnvironment('record');
        }

		return "next";
    }

    public function init() {

        $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
            'moduleselect' => true,
            'default' => 'condition',
        ));

    }

    public function beforeGetTaskform($viewer) {
        $targetModule = $this->RecordSources->getTargetModule();
        if(!empty($targetModule)) {
            $workflows = $workflows = Workflow2::getWorkflowsForModule($targetModule, 1, "", false);
        } else {
            $workflows = array();
        }

        $viewer->assign("workflows", $workflows);

		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
