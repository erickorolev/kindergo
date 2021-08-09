<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready 2014/04/23 */
class WfTaskEntityDataGet extends \Workflow\Task
{
    protected $_envSettings = array();
    //protected $_javascriptFile = "WfTaskEntityDataGet.js";

    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        $values = $this->get("cols");

        $srcrecord = $this->get('srcrecord');
        if(empty($srcrecord) || $srcrecord == -1) {
            $srcrecord = 'crmid';
        }

        if($srcrecord !== 'crmid') {
            $targetContext = \Workflow\VTEntity::getForId($context->get($srcrecord));
        } else {
            $targetContext = $context;
        }

        foreach($values["key"] as $index => $value) {
            $keyValue = $values["value"][$index];

            $context->setEnvironment($keyValue, $targetContext->getEntityData($value));
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $pause_rows = $this->get("pause_rows");
        if($pause_rows == -1) {
            $this->set("pause_rows", 50);
        }

        $cols = $this->get("cols");

        if($cols == -1) {
            $cols = array();
        }

        foreach($cols["key"] as $index => $col) {
            if(empty($col)) {
                unset($cols["key"][$index]);
                unset($cols["value"][$index]);
            }
        }

        $viewer->assign("cols", $cols);

        $references = \Workflow\VtUtils::getReferenceFieldsForModule($this->getModuleName());
        $viewer->assign("reference", $references);
    }

    public function beforeSave(&$values) {

    }

    public function getEnvironmentVariables() {
        $variables = array();

        $fields = $this->get('cols');
        if(!empty($fields) && $fields !== -1) {
            foreach($fields["value"] as $value) {
                $variables[] = "['".$value."'";
            }
            return $variables;
        }

        return array();
    }
}
