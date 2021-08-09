<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskRequestValue extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\FormGenerator
     */
    private $_formgenerator = null;

    public function init() {
        $this->_formgenerator = $this->addPreset("FormGenerator", "fields", array(
                'module' => $this->getModuleName()
            ));
    }

    public function handleTask(&$context) {
        if($this->getWorkflow()->isSubWorkflow()) {
            $this->addStat('RequestValue Task in SubWorkflow currently not supported!');
            return 'yes';
        }

        $blockKey = 'block_'.$this->getBlockId();

        if(!$this->getWorkflow()->hasRequestValues($blockKey)) {

            $export = $this->_formgenerator->exportUserQueue($this->_settings, $context);

            if($this->get('pausable') == -1) {
                $pausable = true;
            } else {
                $pausable = $this->get('pausable') == '1';
            }
            if($this->get('stoppable') == -1) {
                $stoppable = false;
            } else {
                $stoppable = $this->get('stoppable') == '1';
            }
            if($this->get('stoponclose') == -1) {
                $stopOnClose = false;
            } else {
                $stopOnClose = $this->get('stoponclose') == '1';
            }

            if($this->notEmpty('successtext')) {
                $successText = $this->get('successtext', $context);
            } else {
                $successText = vtranslate('Execute Workflow', 'Settings:Workflow2');
            }

            $options = array(
                'stopOnClose' => $stopOnClose,
                'successText' => $successText
            );

            $this->getWorkflow()->requestValues(
                $blockKey,
                $export,
                $this,
                $this->get('message', $context),
                $context,
                $stoppable,
                $pausable,
                $options
            );

            return false;
        }

        $this->getWorkflow()->resetRequestValueKey($blockKey);

		return "yes";
    }

    public function exportUserQueueHTML($context) {
        return $this->_formgenerator->renderFrontend($context, $this->_settings);
    }

    public function beforeGetTaskform($viewer) {
//        $types = \Workflow\Fieldtype::getTypes();

        if($this->get('pausable') == -1) {
            $this->set('pausable', '1');
        }
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
        if(empty($values['pausable'])) {
            $values['pausable'] = 0;
        }
        if(empty($values['stoppable'])) {
            $values['stoppable'] = 0;
        }
		/* Insert here source code to modify the values the user submit on configuration */
    }

    public function getEnvironmentVariables() {
        $variables = array();

        $fields = $this->get('fields');
        foreach($fields as $field) {
            $variables[] = "['value']['".$field['name']."'";
        }
        return $variables;
    }
}
