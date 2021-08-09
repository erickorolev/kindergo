<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TypesSort_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        $block = $request->get('block');
        $sortTypes = $request->get('sort');

        $counter = 1;
        $sql = 'UPDATE vtiger_wf_types SET sort = ?, category = ? WHERE type = ?';
        foreach($sortTypes as $types) {
            $adb->pquery($sql, array($counter, $block, $types));
            $counter++;
        }
    }


}