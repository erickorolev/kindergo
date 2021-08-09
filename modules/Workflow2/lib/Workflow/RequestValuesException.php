<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 16.08.14 13:11
 * You must not use this file without permission.
 */
namespace Workflow;

class RequestValuesException extends \Exception
{
    private $_reqValuesKey = null;
    private $_fields = null;
    private $_task = null;

    /**
     * @var null|VTEntity
     */
    private $_context = null;

    public function __construct($key, $fields, $message, \Workflow\Task $task, \Workflow\VTEntity $context) {
        $this->code = 100;

        $this->_reqValuesKey = $key;
        $this->_fields = $fields;
        $this->_task = $task;
        $this->_context = $context;

        $this->message = $message;
    }

    /**
     * @return null|VTEntity
     */
    public function getContext() {
        return $this->_context;
    }
    /**
     * @return Task
     */
    public function getTask() {
        return $this->_task;
    }
    public function getKey() {
        return $this->_reqValuesKey;
    }

    public function getFields() {
        return $this->_fields;
    }
}

?>