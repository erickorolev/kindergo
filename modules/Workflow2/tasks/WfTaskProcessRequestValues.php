<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskProcessRequestValues extends \Workflow\Task
{
    protected $_envSettings = array("new_record_id", 'was_created_new');

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$scope = $this->get('scope', $context);
        if(empty($scope)) {
            $variables = $context->getEnvironment();
        } else {
            $variables = $context->getEnvironment($scope);
        }

        $fields = $this->get('fields');

        $targetContext = null;
        $targetId = $this->get('targetid', $context);
        if(empty($targetId) || $targetId == -1) {
            $targetContext = $context;

            $context->setEnvironment("was_created_new", 'true', false);
        } else {
            if($targetId == 'new') {
                if ($this->notEmpty('uniquecheck')) {
                    $uniqueCheck = $this->get('uniquecheck');

                    $condition = array();
                    foreach ($uniqueCheck as $checkField) {
                        $condition[$checkField] = $variables[$checkField];
                    }

                    $this->addStat($condition);
                    $records = \Workflow\VtUtils::findRecordIDs($this->get('targetModule'), $condition);

                    if(count($records) > 0) {
                        $this->addStat('found Record '.$records[0].' for update');

                        $updateexisting = $this->get('updateexisting', $context);
                        $newFields = array();
                        $targetContext = \Workflow\VTEntity::getForId($records[0]);

                        if($updateexisting !== -1 && is_array($updateexisting) && !empty($updateexisting)) {

                            foreach($updateexisting as $field) {
                                $newFields[$field] = '1';
                            }

                            $fields = $newFields;
                        }

                        $this->addStat('Updates');
                        $this->addStat($fields);
                        $context->setEnvironment("was_created_new", 'false', $this);
                    } else {
                        $targetContext = \Workflow\VTEntity::create($this->get('targetModule'));

                        $context->setEnvironment("was_created_new", 'true', $this);
                    }
                } else {
                    $targetContext = \Workflow\VTEntity::create($this->get('targetModule'));

                    $context->setEnvironment("was_created_new", 'true', $this);
                }
            }

            if(empty($targetContext)) {
                $targetContext = \Workflow\VTEntity::getForId($targetId);
                $context->setEnvironment("was_created_new", 'true', $this);
            }
        }

        foreach($fields as $field => $dummy) {
            if(isset($variables[$field])) {
                $fieldInfo = \Workflow\VtUtils::getFieldInfo($field, getTabid($this->get('targetModule')));
                $fieldType = \Workflow\VtUtils::getFieldTypeName($fieldInfo['uitype'], $fieldInfo['typeofdata']);

                switch($fieldType) {
                    case 'date':
                        $variables[$field] = date('Y-m-d', strtotime($variables[$field]));
                        break;
                }

                $this->addStat($field.' ['.$fieldType.'] => '.$variables[$field]);
                $targetContext->set($field, $variables[$field]);
            }
        }

        $targetContext->save();

        $context->setEnvironment("new_record_id", $targetContext->getId(), $this);

        return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        $entityModules = \Workflow\VtUtils::getEntityModules();
        $viewer->assign('modules', $entityModules);

        $module = $this->get('targetModule');
        if($module === -1) {
            $module = $this->getModuleName();
            $this->set('targetModule', $module);
        }

        $fields = \Workflow\VtUtils::getFieldsWithBlocksForModule($module, false);

        $viewer->assign('fields', $fields);

    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
