<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMailscanner_markread extends \Workflow\Task
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
            $this->_SC->setColumnCount(1);

            $this->_SC->addFields('read', 'Set Read/Unread', 'select', array(
                'options' => array(
                    'read' => 'Set mail to read',
                    'unread' => 'Set mail to unread',
                )
            ));

        }
    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

		$mail = \Workflow\Mailscanner::getCurrentMessage();

		if($this->_SC->get('read') == 'read' && $mail->isSeen() == false) {
            $mail->setFlag('\Seen');
        } elseif($this->_SC->get('read') == 'unread' && $mail->isSeen() == true) {
            $mail->clearFlag('\Seen');
        }

		return "yes";
    }

    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
