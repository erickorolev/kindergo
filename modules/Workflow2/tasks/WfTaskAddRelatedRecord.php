<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskAddRelatedRecord extends \Workflow\Task
{
    public function init() {
        $related_module = $this->get("related_module");

        if($this->notEmpty('target_module') === false) {
            $this->set('target_module', $this->getModuleName());
        }

        if(!empty($_POST["task"]["related_module"])) {
            $toModule = $_POST["task"]["related_module"];
        } elseif(!empty($related_module) && $related_module != -1) {
            $toModule = $related_module;
        }

        if(isset($toModule)) {
            //$parts = explode("#~#", $toModule);
            $related_module_name = $toModule;

            if(strpos($related_module_name, '@') !== false) {
                $parts = explode('@', $related_module_name);
                $related_module_name = end($parts);
            }

            $this->addPreset("Condition", "condition", array(
                'fromModule' => $this->getModuleName(),
                'toModule' => str_replace('REL@', '', $related_module_name),
                'mode' => 'mysql',
            ));
        }
    }

    public function handleTask(&$context) {
        $adb = \PearDatabase::getInstance();

        if($this->get("related_module") == -1) {
            return "yes";
        }
        $currentModule = $this->getModuleName();

        $found_rows = $this->get("found_rows");
        if(empty($found_rows) || $found_rows == -1) {
            $found_rows = 0;
        }

        $CoreRelation = false;
        $related_module = $this->get("related_module");
        if(strpos($related_module, 'REL@') !== false) {
            $CoreRelation = true;
            $related_module = str_replace('REL@', '', $related_module);
        }

        if(strpos($related_module, '@') !== false) {
            $parts = explode('@', $related_module);
            $related_module_name = end($parts);
        } else {
            $related_module_name = $related_module;
        }

        $objMySQL = new \Workflow\ConditionMysql($related_module_name, $context);

        $main_module = CRMEntity::getInstance($related_module_name);
        #$sqlTables = $main_module->generateReportsQuery($related_module);

        $sqlCondition = $objMySQL->parse($this->get("condition"));

        if(strlen($sqlCondition) > 3) {
            $sqlCondition .= "AND vtiger_crmentity.deleted = 0";
        } else {
            $sqlCondition .= "vtiger_crmentity.deleted = 0";
        }

        $sqlTables = $objMySQL->generateTables();

        $idColumn = $main_module->table_name.".".$main_module->table_index;
        $sqlQuery = "SELECT $idColumn as idcol ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"").' GROUP BY vtiger_crmentity.crmid';
        if(!empty($found_rows)) {
           $sqlQuery .= ' LIMIT '.$found_rows;
        }

        $this->addStat("MySQL Query: ".$sqlQuery);

        $result = $adb->query($sqlQuery);

        $targetRecord = $this->get('target', $context);
        if(empty($targetRecord)) {
            $targetRecord = $context->getId();
        }

        while($row = $adb->fetchByAssoc($result)) {
            $recordId = $row['idcol'];

            if($CoreRelation === true) {
                $sql = 'SELECT crmid from vtiger_crmentityrel WHERE crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?';
                $resultCheck = $adb->pquery($sql, array($targetRecord, \Workflow\VtUtils::getModuleNameForCRMID($targetRecord), $recordId, $related_module));
                if($adb->num_rows($resultCheck) == 0) {
                    $sql = "INSERT INTO vtiger_crmentityrel SET crmid = ?, module = ?, relcrmid = ?, relmodule = ?";
                    $adb->pquery($sql, array($targetRecord, \Workflow\VtUtils::getModuleNameForCRMID($targetRecord), $recordId, $related_module));
                }

            } else {
                $relation = \Workflow\RelationAddExtend::getRelation($related_module);
                $relation->addRelatedRecord($recordId, $targetRecord);
            }
        }

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        $adb = \PearDatabase::getInstance();

        /**
         * @var \Workflow\RelationAddExtend[] $related
         */
        $related = \Workflow\RelationAddExtend::getItems($this->get('target_module'));
        $relatedTabIds = array();
        $relatedLists = array();
        foreach($related as $relation) {
            if($relation->hasSupport('add') === false) continue;

            $title = $relation->getTitle();
            $moduleName = $relation->getRelatedModule();

            $tabId = getTabid($moduleName);
            $relatedTabIds[] = $tabId;

            $relatedLists[] = array(
                "related_tabid" => $tabId,
                "module_name" => $moduleName,
                //"action" => $row["name"],
                "label" => $title, //getTranslatedString($moduleName, $moduleName),
            );
        }

        $sql = 'SELECT * FROM vtiger_relatedlists WHERE tabid = '.getTabId($this->getModuleName()).' AND name = "get_related_list"';
        $result = $adb->query($sql);


        while($row = $adb->fetchByAssoc($result)) {
            $moduleName = \Workflow\VtUtils::getModuleName($row['related_tabid']);

            $relatedLists[] = array(
                "related_tabid" => $row['related_tabid'],
                "module_name" => 'REL@'.$moduleName,
                //"action" => 'get_comments',
                "label" => getTranslatedString($moduleName, $moduleName),
            );

        }

        $viewer->assign('EntityModules', \Workflow\VtUtils::getEntityModules(true));

        $viewer->assign("related_modules", $relatedLists);
        $related_module = $this->get("related_module");

        if(!empty($_POST["task"]["related_module"])) {
            $related_module = $_POST["task"]["related_module"];
        }

        $viewer->assign("related_module", $related_module);

    }
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
