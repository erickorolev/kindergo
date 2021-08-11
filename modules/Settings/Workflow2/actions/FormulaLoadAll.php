<?php
//global $root_directory;
//require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FormulaLoadAll_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_formulas';
        $result = $adb->query($sql);
        $formulas = array();
        while($row = $adb->fetchByAssoc($result)) {
            if(empty($row['name'])) $row['name'] = 'Formula ' . $row['formula'];
            $formulas[$row['id']] = $row['name'];
        }

        echo json_encode($formulas);
        exit();
    }
}

