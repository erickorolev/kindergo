<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SchedulerUpdate_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $adb = PearDatabase::getInstance();
        
        $id = (int)$request->get('scheduleId');
        $field = $request->get('field');
        $value = $request->get('value');

        if(empty($value) && $value !== '0' && $field != 'condition') {
            $value = '*';
        }

        $validFields = array('active','workflow_id', 'timezone', 'hour', 'minute', 'dom', 'month', 'dow', 'year', 'enable_records', 'condition');
        if(!in_array($field, $validFields)) {
            return;
        }

        $sql = 'UPDATE vtiger_wf_scheduler SET `'.$field.'` = ? WHERE id = '.$id;
        $adb->pquery($sql, array($value), true);

        $objScheduler = new \Workflow\Scheduler($id);
        $objScheduler->setNextDate();
    }
}