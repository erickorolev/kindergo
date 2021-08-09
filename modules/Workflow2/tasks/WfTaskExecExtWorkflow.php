<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

/* vt6 ready 2014/04/12 */
class WfTaskExecExtWorkflow extends \Workflow\Task {

    protected $_envSettings = array("result_environments");

    public function init() {
        if(-1 != $this->get("search_module") || !empty($_POST["task"]["search_module"])) {
            $module = !empty($_POST["task"]["search_module"]) ? $_POST["task"]["search_module"] : $this->get("search_module");
            $parts = explode('#~#', $module);
            $this->addPreset("Condition", "condition", array(
                'toModule' => VtUtils::getModuleName($parts[1]),
                'mode' => 'mysql',
            ));
            /*$this->addPreset("RecordSources", "recordsource", array(
                'module' => VtUtils::getModuleName($parts[1]),
                'default' => 'condition',
            ));*/
        }

    }

    /**
     * @param $context \Workflow\VTEntity
     * @return string
     */
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

        $recordids = array();
        if($recordsource == 'condition') {
            require_once('modules/Workflow2/VTConditionMySql.php');

            $logger = \Workflow\ExecutionLogger::getCurrentInstance();

            $objMySQL = new \Workflow\ConditionMysql($related_module, $context);
            $objMySQL->setLogger($logger);

            $main_module = CRMEntity::getInstance($related_module);
            #$sqlTables = $main_module->generateReportsQuery($related_module);

            $sqlCondition = $objMySQL->parse($this->get("condition"));

            $sqlTables = $objMySQL->generateTables();

            if(strlen($sqlCondition) > 3) {
                $sqlCondition .= " AND vtiger_crmentity.deleted = 0";
            } else {
                $sqlCondition .= " vtiger_crmentity.deleted = 0";
            }

            $idColumn = $main_module->table_name.".".$main_module->table_index;
            $sqlQuery = "SELECT $idColumn as `idCol` ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"");
            $sortField = $this->get("sort_field");
            $sqlQuery .= ' GROUP BY vtiger_crmentity.crmid ';

            if(!empty($sortField) && $sortField != -1) {
                $sortDirection = $this->get("sortDirection");
                $sortField = VtUtils::getColumnName($sortField);
                $sqlQuery .= " ORDER BY ".$sortField." ".$sortDirection;
            }
            $numRows = $this->get("found_rows");
            if(!empty($numRows) && $numRows != -1) {
                $sqlQuery .= " LIMIT ".$found_rows;
            }

            $this->addStat("MySQL Query: ".$sqlQuery);

            $result = $adb->query($sqlQuery, true);

            $this->addStat("num Rows: ".$adb->num_rows($result));

            while($row = $adb->fetchByAssoc($result)) {
                $recordids[] = $row["idcol"];
            }
        }
        if($recordsource == 'customview') {
            $queryGenerator = new \QueryGenerator($related_module, \Users::getActiveAdminUser());
            $queryGenerator->initForCustomViewById($this->get('customviewsource'));
            $query = $queryGenerator->getQuery();
            $parts = preg_split('/FROM/i', $query);
            $sqlQuery = 'SELECT vtiger_crmentity.crmid as id_col FROM '.$parts[1];
            $result = $adb->query($sqlQuery, true);
            $this->addStat("num Rows: ".$adb->num_rows($result));

            while($row = $adb->fetchByAssoc($result)) {
                $recordids[] = $row["id_col"];
            }
        }

        $filterbyproduct = $this->get('filterbyproduct');

        if($filterbyproduct === 'yes') {
            $products = $this->get('products');
            if(!empty($products)) {
                $sql = 'SELECT id FROM vtiger_inventoryproductrel WHERE id IN ('.generateQuestionMarks($recordids).') AND productid = ? GROUP BY id';
                $recordids[] = $products;
                $result = $adb->pquery($sql, $recordids);

                $recordids = array();
                while($row = $adb->fetchByAssoc($result)) {
                    $recordids[] = $row['id'];
                }
            }

        }

        $workflow_id = $this->get("workflow_id");
        if(!empty($workflow_id)) {
            foreach($recordids as $recordId) {
                $tmpContext = \Workflow\VTEntity::getForId($recordId, $related_module);
                $preserveEnvironment = $tmpContext->getEnvironment();
                $tmpContext->clearEnvironment();
                $tmpContext->loadEnvironment($context->getEnvironment());
                $obj = new \Workflow\Main($workflow_id, false, $context->getUser());
                $obj->setExecutionTrigger($this->getWorkflow()->getExecutionTrigger());
                $obj->setContext($tmpContext);
                $obj->isSubWorkflow(true);

                $obj->start();
                if($obj->getSuccessRedirection() != false) {
                    $this->getWorkflow()->setSuccessRedirection($obj->getSuccessRedirection(), $obj->getSuccessRedirectionTarget());
                }

                $env = $this->get("env");
                if($env !== -1 && !empty($env["result_environments"])) {
                    $oldEnv = $context->getEnvironment($env["result_environments"]);
                    if(!is_array($oldEnv)) {
                        $oldEnv = array();
                    }
                    $oldEnv[] = $tmpContext->getEnvironment();
                    $context->setEnvironment("result_environments", $oldEnv, $this);
                }

                if(!empty($preserveEnvironment)) {
                    $tmpContext->loadEnvironment($preserveEnvironment);
                }
            }
        }

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
            $viewer->assign("related_tabid", $parts[1]);

            $search_module_name = VtUtils::getModuleName($parts[1]);
            #$workflowSettings = $this->getWorkflow()->getSettings();

            $workflows = $workflows = Workflow2::getWorkflowsForModule($search_module_name, 1);
            $viewer->assign("workflows", $workflows);

            $fields = VtUtils::getFieldsWithBlocksForModule($search_module_name);
            $viewer->assign("sort_fields", $fields);

            $moduleObj = \Vtiger_Module_Model::getInstance($search_module_name);

            $viewer->assign('productCache', array());

            if($moduleObj instanceof \Inventory_Module_Model) {
                $viewer->assign('searchByProduct', true);

                $product = $this->get('products');
                if(!empty($product)) {
                    //$dataObj = \Vtiger_Record_Model::getInstanceById($product);

                    $productCache[$product] = array(
                        'label' => \Vtiger_Functions::getCRMRecordLabel($product),
                    );
                    $viewer->assign('productCache', $productCache);
                }

            }

            $views = array();
            $allviews = \CustomView_Record_Model::getAll($search_module_name);
            foreach($allviews as $view) {
                $views[$view->get('cvid')] = $view->get('viewname');
            }

            $viewer->assign('customviews', $views);
        }

    }

    public function beforeSave(&$data) {
        $data["found_rows"] = preg_replace("/[^0-9]/", "", $data["found_rows"]);
    }

}