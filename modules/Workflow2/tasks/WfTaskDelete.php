<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

/* vt6 ready */
class WfTaskDelete extends \Workflow\Task
{
    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        $crmid = $this->get('crmid', $context);

        if(empty($crmid) || $crmid == -1 || $crmid == $context->getId()) {
            $context->delete();
        } else {
            $record = \Workflow\VTEntity::getForId($crmid);
            $record->delete();
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {

    }

    public function beforeSave(&$values) {


    }
}
