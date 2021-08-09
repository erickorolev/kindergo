<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskAdjustglobaltax extends \Workflow\Task
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

            $taxes = getAllTaxes('available');

            $this->_SC->addHeadline('Inventory Taxes');

            foreach($taxes as $tax) {
                $this->_SC->addField('enable_'.$tax['taxname'],  'active tax? '.$tax['taxlabel'].'', 'checkbox');
                $this->_SC->addField($tax['taxname'], $tax['taxlabel'].' (%)', 'template', array('default' => $tax['percentage']));
            }
/*
            $this->_SC->addHeadline('Shipping / Fee Taxes');

            $taxes = getAllTaxes('available', 'sh');

            foreach($taxes as $tax) {
                $this->_SC->addField('enable_'.$tax['taxname'].'_sh',  'active tax? '.$tax['taxlabel'].'', 'checkbox');
                $this->_SC->addField($tax['taxname'].'_sh', $tax['taxlabel'].' (%)', 'template', array('default' => $tax['percentage']));
            }
*/
        }
    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        $taxes = getAllTaxes('available');
        $setTaxes = array();
        foreach($taxes as $tax) {
            $enabled = $this->_SC->get('enable_'.$tax['taxname']);

            if(!empty($enabled)) {
                $setTaxes[$tax['taxname'] . '_group_percentage'] = floatval($this->_SC->get($tax['taxname']));
            }
        }

        $context->setGroupTaxes($setTaxes);
        /*
                $taxes = getAllTaxes('available', 'sh');

                $setTaxes = array();
                foreach($taxes as $tax) {
                    $enabled = $this->_SC->get('enable_'.$tax['taxname'].'_sh');

                    if(!empty($enabled)) {
                        $setTaxes[$tax['taxname'] . '_sh_percent'] = floatval($this->_SC->get($tax['taxname'].'_sh'));
                    }
                }

                $context->setShipTaxes($setTaxes);
        */
		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
