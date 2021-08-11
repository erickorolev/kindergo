<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskCreate_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        global $root_directory;
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        $params['className'] = preg_replace('/[^a-zA-Z0-9-_]/', '_', trim($params['className']));
        $params['typeName'] = preg_replace('/[^a-zA-Z0-9-_]/', '_', trim($params['typeName']));

        \Workflow\Repository::installFile($root_directory.'/modules/Workflow2/dummytype.zip', 1, 0, 1, 1);

        $sql = 'DELETE FROM vtiger_wf_types WHERE type = ?';
        $adb->pquery($sql, array($params['typeName']));

        $sql = 'UPDATE vtiger_wf_types SET type = ?, handlerclass = ?, background = ?, text = ? WHERE type = "dummy_type"';
        $adb->pquery($sql, array(
            $params['typeName'],
            $params['className'],
            'task_'.$params['typeName'],
            $params['typeLabel'],
        ));

        $taskFile = $root_directory.'/modules/Workflow2/tasks/dummyTypeClass.php';
        $newTaskFile = $root_directory.'/modules/Workflow2/tasks/'.$params['className'].'.php';
        @unlink($newTaskFile);
        rename($taskFile, $newTaskFile);

        $content = file_get_contents($newTaskFile);
        $content = str_replace('dummyTypeClass', $params['className'], $content);
        file_put_contents($newTaskFile, $content);

        $taskFile = $root_directory.'/modules/Workflow2/tasks/WfTaskDummytypeclass.js';
        $newTaskFile = $root_directory.'/modules/Workflow2/tasks/WfTask'.ucfirst(strtolower(str_replace("WfTask", "", $params['className']))).'.js';
        @unlink($newTaskFile);
        rename($taskFile, $newTaskFile);

        $taskFile = $root_directory.'/modules/Workflow2/icons/task_dummy_type.png';
        $newTaskFile = $root_directory.'/modules/Workflow2/icons/task_'.$params['typeName'].'.png';
        @unlink($newTaskFile);
        rename($taskFile, $newTaskFile);

        $taskFile = $root_directory.'/layouts/vlayout/modules/Settings/Workflow2/taskforms/WfTaskDummy_type.tpl';
        $newTaskFile = $root_directory.'/layouts/vlayout/modules/Settings/Workflow2/taskforms/WfTask'.ucfirst(strtolower($params['typeName'])).'.tpl';
        @unlink($newTaskFile);
        rename($taskFile, $newTaskFile);

        $response = new Vtiger_Response();
        try {
            $response->setResult(array("success" => true));
        } catch(Exception $exp) {
            $response->setResult(array("success" => false, "error" => $exp->getMessage()));
        }

        $response->emit();
    }


}