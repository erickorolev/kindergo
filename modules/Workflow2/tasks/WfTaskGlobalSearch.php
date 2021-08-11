<?php
/**
This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

It belongs to the Workflow Designer and must not be distributed without complete extension
 **/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

/* vt6 ready 2014/04/28 */
class WfTaskGlobalSearch extends \Workflow\Task {

    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    protected $_envSettings = array(
        "count_results" => 'Number of found results'
    );

    public function upgrade() {
        if($this->notEmpty('recordsource') == false && $this->notEmpty('condition')) {
            $this->set('recordsource', array('sourceid' => 'condition'));
            $this->set('recordsourcecondition', $this->get('condition'));
        }
    }

    public function init() {
        $search_module = $this->get("search_module");

        if(!empty($_POST["task"]["search_module"])) {
            $toModule = $_POST["task"]["search_module"];
        } elseif(!empty($search_module) && $search_module != -1) {
            $toModule = $search_module;
        }

        if(isset($toModule)) {
            $parts = explode("#~#", $toModule);

            $this->addPreset("Condition", "condition", array(
                'fromModule' => $this->getModuleName(),
                'toModule' => $parts[0],
                'mode' => 'mysql',
            ));

            $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
                'module' => $parts[0],
                'default' => 'condition',
            ));

        }

        $this->upgrade();
    }

    public function handleTask(&$context) {
        global $adb;

        if($this->get("search_module") == -1) {
            return "no";
        }

        $found_rows = $this->get("found_rows");
        if(empty($found_rows) || $found_rows == -1) {
            $found_rows = 1;
        }

        $parts = explode("#~#", $this->get("search_module"));
        $functionName = $parts[0];
        $related_module = VtUtils::getModuleName($parts[1]);

        $sortBy = null;
        if($this->notEmpty('sort_field')) {
            $sortBy = array($this->get('sort_field'), $this->get('sortDirection'));
        }

        $sqlQuery = $this->RecordSources->getQuery($context, $sortBy);
        //var_dump($sqlQuery);exit();
        /*
        require_once('modules/Workflow2/VTConditionMySql.php');


        $objMySQL = new \Workflow\ConditionMysql($related_module, $context);

        $main_module = CRMEntity::getInstance($related_module);
        #$sqlTables = $main_module->generateReportsQuery($related_module);

        $sqlCondition = $objMySQL->parse($this->get("condition"));

        if(strlen($sqlCondition) > 3) {
            $sqlCondition .= "AND vtiger_crmentity.deleted = 0";
        } else {
            $sqlCondition .= "vtiger_crmentity.deleted = 0";
        }

        $sqlTables = $objMySQL->generateTables();

        $idColumn = $main_module->table_name.".".$main_module->table_index;
        $sqlQuery = "SELECT $idColumn as idCol ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"").' GROUP BY vtiger_crmentity.crmid';
*/
        $this->addStat("MySQL Query: ".$sqlQuery);

        $result = $adb->query($sqlQuery, true);

        if($adb->database->ErrorMsg() != "") {
            $this->addStat($adb->database->ErrorMsg());
        }

        $context->setEnvironment('count_results', $adb->num_rows($result), $this);

        $this->addStat("num Rows: ".$adb->num_rows($result));
        $this->addStat("have to at least x rows: ".$found_rows);

        $resultEnv = $this->get('resultEnv');
        if(!empty($resultEnv) && $resultEnv != -1) {
            $ids = array();
            while($row = $adb->fetchByAssoc($result)) {
                $ids[] = $row['crmid'];
            }

            $context->setEnvironment($resultEnv, array('ids' => $ids, 'moduleName' => $related_module));
        }

        if($adb->num_rows($result) >= $found_rows) {
            $return = "yes";
        } else {
            return "no";
        }

        return $return;

    }

    public function beforeGetTaskform($viewer) {
        global $current_language, $mod_strings;

        $viewer->assign("related_modules", VtUtils::getEntityModules(true));
        $search_module = $this->get("search_module");

        if(!empty($_POST["task"]["search_module"])) {
            $parts = explode("#~#", $_POST["task"]["search_module"]);
        } elseif(!empty($search_module)) {
            if($search_module != -1) {
                $parts = explode("#~#", $search_module);
            }
        } else {
            return;
        }

        if(!empty($parts)) {
            $viewer->assign("related_tabid", $parts[1]);

            $fields = VtUtils::getFieldsWithBlocksForModule($parts[0]);
            $viewer->assign("sort_fields", $fields);
        }
    }

    public function beforeSave(&$data) {
        $data["found_rows"] = preg_replace("/[^0-9]/", "", $data["found_rows"]);
    }

}