<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ConditionPopupCalculator_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $task = $request->get('task');

        $preset = new \Workflow\Preset\Condition('condition', null, array(
            'fromModule' => $request->get('fromModule'),
            'toModule' => $request->get('toModule'),
            'enableHasChanged' => false,
            'container' => 'conditionalPopupContainer',
        ));

        $condition = $preset->beforeSave($task);

        $moduleName = $task['module'];
        $main_module = \CRMEntity::getInstance($moduleName);

        if(!empty($condition)) {
            $objMySQL = new \Workflow\ConditionMysql($condition['module'], \Workflow\VTEntity::getDummy());
            $objMySQL->setLogger(false);

            $sqlCondition = $objMySQL->parse($condition['condition']);

            $sqlTables = $objMySQL->generateTables();
        } else {
            $sqlTables = "FROM ".$main_module->table_name.' INNER JOIN vtiger_crmentity ON (crmid = `'.$main_module->table_name.'`.`'.$main_module->table_index.'` AND deleted = 0)';
            $sqlCondition = '';
        }

        if(strlen($sqlCondition) > 3) {
            $sqlCondition .= " AND vtiger_crmentity.deleted = 0";
        } else {
            $sqlCondition .= " vtiger_crmentity.deleted = 0";
        }

        $idColumn = $main_module->table_name.".".$main_module->table_index;
        $sqlQuery = "SELECT COUNT(*) as num ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"");
        //$sqlQuery .= ' GROUP BY crmid ';
//echo $sqlQuery;
        $result2 = $adb->query($sqlQuery, true);

        echo $adb->query_result($result2, 0, 'num');

        exit();
    }
}

