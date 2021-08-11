<?php

use Workflow\VTTemplate;

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));


class WfTaskCommunicateSMS extends \Workflow\Task
{
    public function handleTask(&$context) {
        if($this->get('provider') == -1) {
            throw new \Exception('Please configure SMS Provider!');
        }

        /**
         * @var $obj \Workflow\Plugins\ConnectionProvider\Communicate
         */
        $obj = \Workflow\ConnectionProvider::getConnection($this->get('provider'));

        // If continued, check if SMS was received!
        if($this->isContinued()) {
            $continued = intval($context->getEnvironment('_continued_'.$this->getBlockId()));

            $data = $context->getEnvironment('_smsdata_'.$this->getBlockId());

            $return = $obj->SMS_check($data);

            if($return === false) {
                switch ($continued) {
                    case 1:
                    case 2:
                    case 3:
                        $time = 10;
                        break;
                    case 4:
                    case 5:
                        $time = 60;
                        break;
                    case 6:
                    case 7:
                        // 12h
                        $time = 720;
                        break;
                    default:
                        // After 6 retry, cancel check
                        \Workflow\ExecutionLogger::getCurrentInstance()->log('SMS Deliverycheck failed. Stop further checks.');
                        return false;
                        break;
                }
                $context->setEnvironment('_continued_'.$this->getBlockId(), $continued + 1);
                $this->addQueue($context, time() + (60 * $time), true);
            }

            // STOP Workflow after this task to not reexecute something
            return false;
        }

        $objTemplate = new VTTemplate($context);
        $data = $objTemplate->render($this->get('data'));

        \Workflow\ExecutionLogger::getCurrentInstance()->log('Send SMS');
        \Workflow\ExecutionLogger::getCurrentInstance()->log($data);

        $return = $obj->SMS($data);

        if(!empty($return)) {
            $context->setEnvironment('_smsdata_'.$this->getBlockId(), $return);
            $context->setEnvironment('_continued_'.$this->getBlockId(), 1);

            \Workflow\ExecutionLogger::getCurrentInstance()->log('Response from SMS Gateway');
            \Workflow\ExecutionLogger::getCurrentInstance()->log($return);

            $this->addQueue($context, time() + 600, true);
        }


        return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        $AvailableProvider = \Workflow\ConnectionProvider::getAvailableConfigurations('communicate');

        $supportedProvider = array();
        foreach($AvailableProvider as $id => $provider) {
            /**
             * @var $obj Workflow\CommunicationPlugin
             */
            $obj = \Workflow\ConnectionProvider::getConnection($id);

            if($obj->isSupported('SMS')) {
                $supportedProvider[$id] = $provider;
            }
        }

        $viewer->assign('provider', $supportedProvider);

        if($this->get('provider') != -1) {
            $obj = \Workflow\ConnectionProvider::getConnection($this->get('provider'));
            $dataFields = $obj->getDataFields('SMS');

            foreach($dataFields as $name => $data) {
                $dataFields[$name]['name'] = 'task[data]['.$name.']';
            }

            $viewer->assign('dataFields', $dataFields);
        }

		/* Insert here source code to create custom configurations pages */
    }	

}
