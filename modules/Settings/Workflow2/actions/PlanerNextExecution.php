<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_PlanerNextExecution_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = (int) $request->get('id');
        $index = (int) $request->get('index');

        $objScheduler = new \Workflow\Scheduler($id);
        $date = \DateTimeField::convertToUserFormat($objScheduler->getNextDate());
        $data = $objScheduler->getData();

        echo json_encode(array('index' => $index, 'execution' => $date.' '.$data['timezone']));
    }
}

