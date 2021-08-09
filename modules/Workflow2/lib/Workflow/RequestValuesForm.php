<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

namespace Workflow;


use Workflow\RequestValuesForm\Field;
use Workflow\RequestValuesForm\Row;

class RequestValuesForm
{
    /**
     * @var Row[]
     */
    private $_Rows = array();

    private $_ContinueText = 'Execute process';
    private $_Headline = 'This process need some information';
    private $_StopText = 'Stop process';


    private $_HTML = '';
    private $_JS = '';

    private $_Settings = array(
        'width' => '550px',
    );
    private $_FieldKey = '';
    private $_TargetScope = 'value';

    public function __construct($fieldKey) {
        $this->_FieldKey = $fieldKey;
    }

    public function setSettings($key, $value) {
        $this->_Settings[$key] = $value;
    }

    public function setTargetScope($scope) {
        $this->_TargetScope = $scope;
    }
    public function getTargetScope() {
        return $this->_TargetScope;
    }

    public function setContinueText($text) {
        $this->_ContinueText = $text;
    }
    public function setHeadline($text) {
        $this->_Headline = $text;
    }
    public function setStopText($text) {
        $this->_StopText = $text;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getHTML() {
        if(empty($this->_HTML)) throw new \Exception('Execute render Function before get HTML');
        return $this->_HTML;
    }

    /**
     * @return string
     */
    public function getJS() {
        return $this->_JS;
    }

    public function startRequestValues(Task $task, VTEntity $context) {
        $queue_id = \Workflow\Queue::addEntry($task, $context->getUser(), $context, 'static', false, 1, false);

        if(empty($options['successText'])) $options['successText'] = 'Execute Workflow';

//        ExecutionLogger::getCurrentInstance()->log('Start Request values: '.$message);

        $userQueueId = \Workflow\Userqueue::add(
            'requestForm',
            $queue_id,
            '',
            $task->getExecId(),
            array(
                "result" => "requestForm",
                'form_settings' => $this->_Settings,
                'crmId' => $context->getId(),
                'blockId' => $task->getBlockId(),
                'execId' => $task->getExecId().'##'.$task->getBlockId(),
                'fields_key' => $this->_FieldKey,
                'language' => array(
                    'Execute Workflow' => VTTemplate::parse($this->_ContinueText, $context),
                    'Headline' => VTTemplate::parse($this->_Headline, $context),
                    'StopText' => VTTemplate::parse($this->_StopText, $context),
                ),
                'handler' => '\Workflow\RequestValuesForm',
                'handlerConfig' => array(
                    'obj' => $this
                ),
                'settings' => 'requestForm',
            )
        );

        $workflowObj = $task->getWorkflow();
        if($workflowObj->getExecutionTrigger() != Main::MANUAL_START) {
            $objFrontendAction = new \Workflow\FrontendActions($context->getModuleName());
            $objFrontendAction->push($context->getId(), 'requestValues', array(
                'crmid' => $context->getId(),
                'execid' => $task->getExecId(),
                'blockid' => $task->getBlockId(),
            ), 'edit');
        }


/*
        if($this->getExecutionTrigger() != self::MANUAL_START) {
            $objFrontendAction = new \Workflow\FrontendActions($context->getModuleName());
            $objFrontendAction->push($context->getId(), 'requestValues', array(
                'crmid' => $context->getId(),
                'execid' => $task->getExecId(),
                'blockid' => $task->getBlockId(),
            ), 'edit');
        }
*/
    }

    /**
     * @return Row
     */
    public function addRow() {
        $row = new Row($this);

        $this->_Rows[] = $row;

        return $row;
    }

    /**
     * @param VTEntity $context
     * @return string
     * @throws \Exception
     */
    public function render(VTEntity $context) {
        $this->_HTML = '';
        $this->_HTML .= '<div>';
            foreach($this->_Rows as $row) {

                $row->render($context);

                $this->_HTML .= $row->getHTML();
                $this->_JS .= $row->getJS();

            }
        $this->_HTML .= '</div>';
    }

    /**
     * @return Field[]
     */
    public function getFieldList() {
        $fields = array();

        foreach($this->_Rows as $row) {
            $fieldList = $row->getFieldList();

            foreach($fieldList as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param RequestValuesForm $config
     * @param VTEntity $context
     * @throws \Exception RequestValueForm called wrong
     * @return array
     */
    public static function generateUserQueueHTML($config, $context) {
        $form = $config['obj'];
        //$render = $row['render'];
        $form->render($context);

        $return = array(
            'html' => $form->getHTML(),
            'javascript' => $form->getJS(),
        );

        return $return;
    }

    public static function processInput($workflowData, $userQueueData, $requestValues) {
        $requestValuesKey = $userQueueData['settings']['fields_key'];
        $context = $workflowData['context'];

        $env = $context->getEnvironment('_reqValues');
        $env[$requestValuesKey] = true;
        $context->setEnvironment("_reqValues", $env);

        /**
         * @var RequestValuesForm $reqValForm
         */
        $reqValForm = $userQueueData['settings']['handlerConfig']['obj'];
        $targetScope = $reqValForm->getTargetScope();
        $env = $context->getEnvironment($targetScope);
        if (empty($env) || !is_array($env)) {
            $env = array();
        }

        \Workflow2::$currentBlockObj = $workflowData['task'];
        \Workflow2::$currentWorkflowObj = $workflowData['task']->getWorkflow();

        $fieldList = $reqValForm->getFieldList();

        foreach ($fieldList as $field) {
            $fieldName = $field->getFieldName();

            if(!isset($requestValues[$fieldName])) {
                continue;
            }

            $value = $requestValues[$fieldName];

            if (is_string($value)) {
                $value = trim($value);
            }

            $value = $field->getValue(
                $value,
                $fieldName,
                $requestValues,
                $context,
                $workflowData['task']
            );

            if (is_string($value)) {
                $value = trim($value);
            }

            $requestValues[$fieldName] = $value;
        }


        $env = array_merge($env, $requestValues);
        $context->setEnvironment($targetScope, $env);
    }
}