<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskExterncalcvalues extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    public function init() {
        if(-1 != $this->get("search_module") || !empty($_POST["task"]["search_module"])) {
            if(!empty($_POST["task"]["search_module"]) && $this->get('search_module') != $_POST["task"]["search_module"]) {
                $this->set('fields', array());
            }

            $module = !empty($_POST["task"]["search_module"]) ? $_POST["task"]["search_module"] : $this->get("search_module");
            $parts = explode('#~#', $module);
            /*$this->addPreset("Condition", "condition", array(
                'toModule' => VtUtils::getModuleName($parts[1]),
                'mode' => 'mysql',
            ));*/

            $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
                'module' => VtUtils::getModuleName($parts[1]),
                'default' => 'condition',
            ));
        }

    }

    public function upgrade($moduleName) {

        if($this->notEmpty('calcfields') && $this->notEmpty('fields') == false) {
            $this->set('recordsource', array('sourceid' => 'condition'));
            $this->set('recordsourcecondition', $this->get('condition'));
            $calcFields = $this->get('calcfields');

            $fields = array();
            foreach($calcFields as $field) {
                $fieldinfo = \Workflow\VtUtils::getFieldInfo($field, getTabId($moduleName));
                $fields[] = array(
                    'fieldname' => $field,
                    'fieldlabel' => $fieldinfo['fieldlabel'],
                    'operation' => 'SUM',
                    'envvar' => $field
                );
            }

            $this->set('fields', $fields);
        }
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

        $recordsource = $this->get('recordsource');
        if(empty($recordsource) || $recordsource == -1) {
            $recordsource = 'condition';
        }

        $parts = explode("#~#", $this->get("search_module"));
        $functionName = $parts[0];
        $related_module = VtUtils::getModuleName($parts[1]);

        $this->upgrade($related_module);

        if($this->notEmpty('fields') === false) {
             return 'yes';
        }

        $calcFields = $this->get('fields');
        $sqlSelectInner = array();

        $query = $this->RecordSources->getQuery(
            $context,
            $this->notEmpty("sort_field")?$this->get('sort_field'):null,
            $this->notEmpty("found_rows")?$this->get('found_rows'):null,
            true
        );

        $calculated = array();
        foreach($calcFields as $field) {
            $sqlSelect = array();

            $field['envvar'] = preg_replace('/[^a-z0-9_]/', '', strtolower($field['envvar']));
            $fieldInfo = \Workflow\VtUtils::getFieldInfo($field['fieldname'], getTabid($related_module));
            $column = $fieldInfo['columnname'];

            switch ($field['operation']) {
                case 'SUMCURR':
                    $sqlSelect[] = 'SUM(ROUND(`' . $column . '`, 2)) as `'.$field['envvar'].'`';
                    break;
                case 'SUM':
                case 'AVG':
                case 'MAX':
                case 'MIN':
                    $sqlSelect[] = $field['operation'] . '(`' . $column . '`) as `' . $field['envvar'] . '`';
                    break;
                case 'Count':
                    $sqlSelect[] = 'COUNT(`' . $column . '`) as `' . $field['envvar'] . '`';
                    break;
                case 'CountDistinct':
                    $sqlSelect[] = 'COUNT(DISTINCT `' . $column . '`) as `' . $field['envvar'] . '`';
                    break;
            }

            $innerSQL = 'IFNULL(`' . $column . '`,0) as `' . $column . '`';
            /*
            if(!in_array($innerSQL, $sqlSelectInner)) {
                $sqlSelectInner[] = $innerSQL;
            }
            */

            $sqlSelect[] = 'COUNT(*) as __totalrows';

            $fieldquery = str_replace('/* Insert Fields */', ' as __dmy, ' . $innerSQL . '', $query);

            if($fieldInfo['tablename'] == 'vtiger_inventoryproductrel') {
                $fieldquery = preg_replace('/GROUP BY vtiger_crmentity\.crmid/', 'GROUP BY vtiger_inventoryproductrel.lineitem_id', $fieldquery);
            }

            $sqlQuery = "SELECT ".implode(',', $sqlSelect).' FROM ('.$fieldquery.') t1';
//            $this->addStat("MySQL Query: ".$sqlQuery);

            $result = $adb->query($sqlQuery, true);

            $fieldValue = $adb->fetchByAssoc($result);

            $calculated[$field['envvar']] = $fieldValue[$field['envvar']];

            $this->addStat('Total rows to calculate: ' . $fieldValue['__totalrows']);

        }

        if(empty($calculated)) return 'yes';

        $envVar = $this->get('envvar');
        if(empty($envVar)) {
            foreach($calculated as $fieldName => $fieldValue) {
                $context->setEnvironment($fieldName, $fieldValue);
            }
        } else {
            $context->setEnvironment($envVar, $calculated);
        }
        $this->addStat($calculated);

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb, $current_language, $mod_strings;

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
            $search_module_name = VtUtils::getModuleName($parts[1]);
            $this->upgrade($search_module_name);

            $viewer->assign("related_tabid", $parts[1]);

            #$workflowSettings = $this->getWorkflow()->getSettings();

            $workflows = $workflows = Workflow2::getWorkflowsForModule($search_module_name, 1);
            $viewer->assign("workflows", $workflows);

            $fields = VtUtils::getFieldsWithBlocksForModule($search_module_name);
            $viewer->assign("sort_fields", $fields);

            $moduleObj = \Vtiger_Module_Model::getInstance($search_module_name);

            $viewer->assign('moduleFields', $fields);
            $viewer->assign('productCache', array());

        }

    }

    public function beforeSave(&$data) {
        $data["found_rows"] = preg_replace("/[^0-9]/", "", $data["found_rows"]);


    }

    public function getEnvironmentVariables() {
        $variables = array();

        $parts = explode("#~#", $this->get("search_module"));
        $related_module = VtUtils::getModuleName($parts[1]);

        $calcFields = $this->get('fields');

        if($this->notEmpty('envvar')) {
            $envVar = $this->get('envvar');
            $prefix = "['".$envVar."']";
        } else {
            $prefix = '';
        }
        foreach($calcFields as $field) {
            $variables[] = $prefix."['".strtolower($field['envvar'])."'";
        }

        return $variables;
    }

}
