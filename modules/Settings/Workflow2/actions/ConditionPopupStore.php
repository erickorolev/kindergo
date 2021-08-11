<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ConditionPopupStore_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request)
    {
        $task = $request->get('task');

        if(!empty($task['condition'])) {
            $preset = new \Workflow\ComplexeCondition('condition', null, array());

            $condition = $preset->getCondition($task['condition']);
            $text = '';
            //$text = $preset->getHTML($condition, $task['module']);
        } else {
            $condition = '';
            $text = '';
        }

        echo VtUtils::json_encode(array('condition' => base64_encode(VtUtils::json_encode(array('condition' => $condition, 'module' => $task['module']))), 'html' => nl2br($text)));
    }

}

