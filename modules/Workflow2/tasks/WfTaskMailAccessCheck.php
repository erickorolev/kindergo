<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMailAccessCheck extends \Workflow\Task
{

    public function init() {
        $this->addPreset("Condition", "condition", array(
            'toModule' => 'Emails'
        ));
    }

    public function handleTask(&$context) {
        $adb = \PearDatabase::getInstance();
		/* Insert here source code to execute the task */
        $currentTime = microtime(true);
        $benchmark = array();

        $found_rows = $this->get("found_rows");
        if(empty($found_rows) || $found_rows == -1) {
            $found_rows = 1;
        }

        $id = $this->get('recordid', $context);
        if(empty($id) || $id == -1 || !is_numeric($id)) {
            $targetContext = $context;
        } else {
            $targetContext = \Workflow\VTEntity::getForId($id);
        }

        $logger = new \Workflow\ConditionLogger();

        $objMySQL = new \Workflow\ConditionMysql('Emails', $targetContext);
        $objMySQL->setLogger($logger);

        $sqlCondition = $objMySQL->parse($this->get("condition"));

        $newTime = microtime(true);
        $benchmark[] = round(($newTime - $currentTime), 3);$currentTime = $newTime;

        $sqlTables = $objMySQL->generateTables();
        //$sqlTables .= "\nLEFT JOIN vtiger_email_track ON(vtiger_email_track.crmid = vtiger_crmentity.crmid)";

        if(strlen($sqlCondition) > 3) {
            $sqlCondition .= " AND vtiger_crmentity.deleted = 0 AND (vtiger_email_track.crmid IS NULL OR vtiger_email_track.crmid = ".$targetContext->getId().')';
        } else {
            $sqlCondition .= " vtiger_crmentity.deleted = 0 AND (vtiger_email_track.crmid IS NULL OR vtiger_email_track.crmid = ".$targetContext->getId().')';
        }

        $logs = $logger->getLogs();
        $this->setStat($logs);

        $main_module = \CRMEntity::getInstance('Emails');

        $sqlCondition .= " GROUP BY vtiger_crmentity.crmid ";
        $idColumn = $main_module->table_name.".".$main_module->table_index;
        $sqlQuery = "SELECT $idColumn as `idCol`, vtiger_email_track.access_count as access_count ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"");
        $sortField = $this->get("sort_field");
        if(!empty($sortField) && $sortField != -1) {
            $sortField = VtUtils::getColumnName($sortField);
            $sortDirection = $this->get("sortDirection");

            $sqlQuery .= " ORDER BY ".$sortField." ".$sortDirection;
        }
        $numRows = $this->get("found_rows");
        if(!empty($numRows) && $numRows != -1) {
            $sqlQuery .= " LIMIT ".$found_rows;
        }
#var_dump(nl2br($sqlQuery));exit();
        $this->addStat("MySQL Query: ".$sqlQuery);
//echo $sqlQuery;
        $result = $adb->query($sqlQuery, true);

        $newTime = microtime(true);
        $benchmark[] = round(($newTime - $currentTime), 3);$currentTime = $newTime;

        $this->addStat("num Rows: ".$adb->num_rows($result));
        # If no records are found, fo other way
        if($adb->num_rows($result) == 0) {
            return "not_found";
        }

        while($row = $adb->fetchByAssoc($result)) {
            if($row['access_count'] > 0) {
                return 'yes';
            }
        }

		return "no";
    }
	
    public function beforeGetTaskform($viewer) {

        $fields = VtUtils::getFieldsWithBlocksForModule('Emails');
        $viewer->assign("sort_fields", $fields);

		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
