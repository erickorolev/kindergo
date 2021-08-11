<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan
 * Date: 24.06.12
 * Time: 12:11
 * To change this template use File | Settings | File Templates.
 */
require_once('modules/Workflow2/autoload_wf.php');

require_once('modules/SMSNotifier/SMSNotifier.php');
/* vt6 ready */
class WfTaskSMSnotifier extends \Workflow\Task
{
	/**
	 * @param $context VTEntity
	 */
	public function handleTask(&$context) {

        $text = $this->get("sms_text", $context);
        $receiver = $this->get("number", $context);

        $this->addStat("Send SMS to ".$receiver);
        $this->addStat("SMS Text ".$text);

		SMSNotifier::sendsms($text, $receiver, $context->getUser()->id, $context->getId(), $context->getModuleName());

        return "yes";
	}

	public function beforeGetTaskform($viewer) {
	}
	
    public function beforeSave(&$values) {

    }	
}
