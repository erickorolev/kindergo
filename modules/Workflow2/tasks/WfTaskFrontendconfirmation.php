<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskFrontendconfirmation extends \Workflow\Task
{

    protected $_frontendDynamical = true;
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
            $this->_SC->setColumnCount(1);

            $provider = $this->_SC->get('provider');

            $this->_SC->addFields('content', 'Confirmation Text', 'template', array(
                //'description' => 'Choose Placetel Account to use'
            ));

        }
    }

    public function handleTask(&$context) {
        $blockKey = 'ConfirmBlock'.$this->getBlockId();

        if($this->isContinued()) {
            $environmentValue = $context->getEnvironment($blockKey);

            if (!empty($environmentValue)) {
                if ($environmentValue == 'yes') {
                    return 'yes';
                } else {
                    return 'no';
                }
            }

            return array(
                "delay" => time() + 1,
                "checkmode" => "static",
                'hidden' => true,
                'locked' => true,
            );
        }

		/* Insert here source code to execute the task */
        $message = $this->_SC->get('content');

        \Workflow\FrontendActions::pushDirectaction('Confirmation', array(
            'key' => $blockKey,
            'message' => $message,
        ));

        return array(
            "delay" => time() + 1,
            "checkmode" => "static",
            'hidden' => true,
            'locked' => true,
        );
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
