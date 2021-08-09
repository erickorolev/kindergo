<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FrontendWorkflowConfigSave_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = intval($request->get('editRecordId'));
        $frontendWorkflowObj = new \Workflow\FrontendWorkflows($id);

        $condition = array();
        if($request->has('task')) {
            $task = $request->get('task');

            $preset = new \Workflow\Preset\Condition('condition', null, array());

            $condition = $preset->beforeSave($task);
            $condition = $condition['condition'];
        }

        $frontendWorkflowObj->update(array(
            'pageload' => $request->has('pageload') ? 1 : 0,
            'active' => $request->has('active') ? 1 : 0,
            'fields' => $request->has('fields') ? $request->get('fields') : array(),
            'condition' => $condition,
        ));

        /**
         * @var $workflowObj Workflow2_Module_Model
         */
        $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
        $workflowObj->refreshFrontendJs();
    }
}

