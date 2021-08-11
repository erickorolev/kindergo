<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskExternalsetvalues extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;

    public function init() {
        if(!empty($_GET['parent'])) {
            $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

            $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
            $asdf = new $className("Workflow2", $moduleModel->version);
            $showRelatedField = $asdf->g1dd63e9ab62a68ac02f481ed3ba709207cb145ae()=='pr'.'o';
        } else {
            $showRelatedField = true;
        }

        $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
            //'module' => VtUtils::getModuleName($parts[1]),
            'default' => 'condition',
            'moduleselect' => true
        ));

        $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
            'fromModule' => $this->getModuleName(),
            'toModule' => $this->RecordSources->getTargetModule(),
            'refFields' => $showRelatedField
        ));

        if($this->RecordSources->isModuleChanged()) {
            $this->fieldSetter->clearFields();
        }
    }

    public function handleTask(&$context) {

        if($this->notEmpty('agreeagb') == false) {
            throw new \Exception('Please do not use the "set values on external records" task, if you are not carefully with configuration.');
        }
        $recordIds = $this->RecordSources->getRecordIds($context);

        $setterMap = $this->get("setter");

        if($this->notEmpty('dryrun')) {
            $this->addStat('DRY RUN - Do not modify any records');
        }

        $this->addStat('Modify Record IDs: ');
        $this->addStat($recordIds);

        // If Dry Run, do not modify records
        if($this->notEmpty('dryrun')) {
            return 'yes';
        }

        foreach($recordIds as $crmid) {
            $targetContext = \Workflow\VTEntity::getForId($crmid, $this->RecordSources->getTargetModule());

            $targetRecordData = $targetContext->getData();
            if(is_object($targetRecordData) && method_exists($targetRecordData, 'getColumnFields')) {
                $targetRecordData = $targetRecordData->getColumnFields();
            }

            $context->setEnvironment('record', $targetRecordData);

            $this->fieldSetter->apply($targetContext, $setterMap, $context, $this);
            $targetContext->save();
        }

		/* Insert here source code to execute the task */
		
		return "yes";
    }

    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
