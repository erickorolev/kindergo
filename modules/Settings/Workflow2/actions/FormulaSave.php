<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_FormulaSave_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $currentUser = \Users_Record_Model::getCurrentUserModel();

        $formulaId = intval($request->get('formulaId'));
        $variables = $request->get('variables');
        $formula = $request->get('formula');
        $formulaName = $request->get('formulaName');

        if(empty($formulaId)) {
            $sql = 'INSERT INTO vtiger_wf_formulas SET
                      formula = ?,
                      variables = ?,
                      modifiedby = ?,
                      name = ?,
                      modified = NOW()';
            $params = array(
                $formula,
                serialize($variables),
                $currentUser->id,
                $formulaName
            );
        } else {
            $sql = 'UPDATE vtiger_wf_formulas SET
                      formula = ?,
                      variables = ?,
                      modifiedby = ?,
                      name = ?,
                      modified = NOW()
                  WHERE id = ?';
            $params = array(
                $formula,
                serialize($variables),
                $currentUser->id,
                $formulaName,
                $formulaId
            );
        }

        $adb->pquery($sql, $params);

        if(empty($formulaId)) {
            $formulaId = \Workflow\VtUtils::LastDBInsertID();
        }
        echo json_encode(array('id' => $formulaId));
        exit();
    }
}