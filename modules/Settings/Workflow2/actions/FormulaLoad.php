<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FormulaLoad_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $currentUser = \Users_Record_Model::getCurrentUserModel();

        $formulaId = intval($request->get('formulaId'));

        $sql = 'SELECT * FROM vtiger_wf_formulas WHERE id = ?';
        $result = $adb->pquery($sql, array($formulaId));
        $dat = $adb->fetchByAssoc($result);

        $variables = unserialize(html_entity_decode($dat['variables']));

        echo \Workflow\VtUtils::json_encode(array('formula' => $dat['formula'], 'variables' => $variables, 'id' => $dat['id'], 'name' => $dat['name']));
        exit();
    }
}