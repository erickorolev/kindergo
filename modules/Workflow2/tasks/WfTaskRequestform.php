<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskRequestform extends \Workflow\Task
{
    protected $_javascriptFile = "WfTaskRequestform.js";

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
        $blockReqKey = '_reqform_'.$this->getBlockId();

        if($this->isContinued() && $this->getWorkflow()->hasRequestValues($blockReqKey)) {
            return 'yes';
        }

        if($this->notEmpty('form') === false) return 'yes';

        $form = $this->get('form', $context);

        $req = new \Workflow\RequestValuesForm($blockReqKey);

        $settings = \Workflow\VtUtils::json_decode($form['settings']);

        $req->setSettings('width', $settings['width']);
        $req->setHeadline($settings['headline']);
        $req->setContinueText($settings['continuetext']);
        $req->setStopText($settings['stoptext']);
        $req->setTargetScope($settings['scope']);

        foreach($form['rows'] as $row) {
            $newrow = $req->addRow();

            foreach($row['fields'] as $field) {
                $config = \Workflow\VtUtils::json_decode($field);

                $field = $newrow->addField();
                $field->setType($config['type']);
                $field->setLabel(\Workflow\VTTemplate::parse($config['label'], $context));
                $field->setConfig($config);
                $field->setFieldname($config['name']);
            }

        }

        $req->startRequestValues($this, $context);

		return false;
    }
	
    public function beforeGetTaskform($viewer) {

        $availableFileTypes = \Workflow\Fieldtype::getTypes($this->getModuleName());

        $keys = array();
        foreach($availableFileTypes as $config) {
            $keys[$config['id']] = $config['title'];
        }

        $viewer->assign('fieldTypes', $keys);
        $viewer->assign('fields', $availableFileTypes);
		/* Insert here source code to create custom configurations pages */
    }

    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }

    public function getEnvironmentVariables() {
        $variables = array();

        $form = $this->get('form');
        $settings = \Workflow\VtUtils::json_decode($form['settings']);

        $scope = $settings['scope'];
        if(empty($scope) || $scope == -1) {
            $prefix = "['";
        } else {
            $prefix = "['".$scope."']['";
        }

        foreach($form['rows'] as $row) {
            foreach($row['fields'] as $field) {
                $config = \Workflow\VtUtils::json_decode($field);
                $variables[] = $prefix.$config['name']."'";
            }

        }

        return $variables;
    }

}
