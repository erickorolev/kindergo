<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskLoop extends \Workflow\Task
{

    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;
    public function init()
    {
        $this->_SC = $this->addPreset("SimpleConfig", "details", array(
            'templatename' => 'mainconfig',
        ));

        if($this->isConfiguration()) {
            $this->_SC->setColumnCount(2);

            $this->_SC->addFields('initialize', 'Initialize Loop <span style="float:right;">$loop = </span>', 'expressionfield', array(
                'placeholder' => 'example: return 1;'
            ));
            $this->_SC->addFields('until', 'run Until', 'expressionfield', array(
                'placeholder' => 'example: return $loop < 100;'
            ));

            $this->_SC->addFields('incremental', 'Increment Script <span style="float:right;">$loop = </span>', 'expressionarea', array(
                'fullwidth' => 1,
                'placeholder' => 'example: return $loop + 1;'
            ));

            $this->_SC->nextRow();
            $this->_SC->addFields('limit', 'Limit loop turns to', 'template', array(
                'default' => '2500',
                'description' => 'regardless your configuration, loop stops after this number of iterations',
                'default_on_empty' => true
            ));

        }
    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
        //$EnvironmentalKey = '__Loop_'.$this->getBlockId();

        unset($_FILES);

        $init = $this->_SC->get('initialize', true);
        $until = $this->_SC->get('until', true);
        $incremental = $this->_SC->get('incremental', true);

        $parser = new \Workflow\ExpressionParser($init, $context, false); # Last Parameter = DEBUG

        try {
            $parser->run();
        } catch(\Workflow\ExpressionException $exp) {
            Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
        }

        $loopReturn = $parser->getReturn();
        //$context->setEnvironment($EnvironmentalKey, $loopReturn);

        $loopSettings = $this->get('loop');

        $endlessSecure = 0;
        $limit = $this->_SC->get('limit');
        if(empty($limit)) {
            $limit = 2500;
        }

        do {
            $this->addStat('Run Loop with $loop = '.$loopReturn);
            $parser = new \Workflow\ExpressionParser($until, $context, false); # Last Parameter = DEBUG
            $parser->setVariable('loop', $loopReturn);

            try {
                $parser->run();
            } catch(\Workflow\ExpressionException $exp) {
                Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
            }
            $loopCheck = $parser->getReturn();

            if(empty($loopCheck)) {
                $this->addStat('Finish Loop');
                // Finish Loop, when parser return false
                break;
            }


            if(!empty($loopSettings['path'])) {
                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $context->set('loop', $loopReturn);
                $obj->setContext($context);
                $obj->isSubWorkflow(true);

                $nextTasks = $this->getNextTasks(array('loop'));

                $obj->handleTasks($nextTasks, $this->getBlockId(), 'loop');
            }

            if (!empty($loopSettings['expression'])) {
                $parser = new \Workflow\ExpressionParser($this->get('expression'), $context, false); # Last Parameter = DEBUG
                $parser->setVariable('loop', $loopReturn);

                try {
                    $parser->run();
                } catch (\Workflow\ExpressionException $exp) {
                    Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                }
            }

            if(!empty($loopSettings['workflow'])) {
                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $obj->setContext($context);
                $obj->isSubWorkflow(true);

                $obj->start();
            }

            $parser = new \Workflow\ExpressionParser($incremental, $context, false); # Last Parameter = DEBUG
            $parser->setVariable('loop', $loopReturn);

            try {
                $parser->run();
            } catch(\Workflow\ExpressionException $exp) {
                Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
            }
            $loopReturn = $parser->getReturn();

            $endlessSecure++;
        } while($endlessSecure < ($limit + 1));
        if($endlessSecure >= $limit) {
            throw new \Exception('You crashed your server with a Loop, without a valid Final check. Loop stopped after '.$limit.' Iterations.');
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
