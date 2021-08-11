<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
require_once('WfTaskLoop.php');

class WfTaskLoopusers extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;
    public function init()
    {

    }

    public function handleTask(&$context) {
        /* Insert here source code to execute the task */
        //$EnvironmentalKey = '__Loop_'.$this->getBlockId();

       
          
        $sql = 'SELECT * FROM vtiger_users WHERE status=\'Active\';';
        $result = \Workflow\VtUtils::query($sql);

        $users = array();
        while($row = \Workflow\VtUtils::fetchByAssoc($result)) {
            $users[] = $row;
        }
        
        $loopSettings = $this->get('loop');

        foreach($users as $index => $user) {
            $logger = \Workflow\ExecutionLogger::getCurrentInstance();

            if (!empty($loopSettings['path'])) {

                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $context->setEnvironment('user', $user);
                $obj->setContext($context);
                $obj->isSubWorkflow(true);

                $nextTasks = $this->getNextTasks(array('loop'));

                $obj->handleTasks($nextTasks, $this->getBlockId(), 'loop');
            }

            if (!empty($loopSettings['expression'])) {
                $parser = new \Workflow\ExpressionParser($this->get('expression'), $context, false); # Last Parameter = DEBUG
                $context->setEnvironment('user', $user);

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
                $context->setEnvironment('user', $user);
                $obj->isSubWorkflow(true);

                $obj->start();
            }

            \Workflow\ExecutionLogger::setCurrentInstance($logger);
            $context->clearEnvironment('user');
        }

        return "next";
    }

    public function beforeGetTaskform($viewer) {

        $workflows = $workflows = Workflow2::getWorkflowsForModule($this->getModuleName(), 1, "", false);
        $viewer->assign("workflows", $workflows);
        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }

}
