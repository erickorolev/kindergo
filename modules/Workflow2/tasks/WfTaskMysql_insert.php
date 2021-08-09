<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMysql_insert extends \Workflow\Task
{
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        $dbserver = $this->get('dbserver');
        $dbname = $this->get('dbname');
        $dbuser = $this->get('dbuser');
        $dbpass = $this->get('dbpass');
        $table = $this->get('table');

        global $dbconfig;

        $values = $this->get("cols");
        $colsWhere = $this->get("colsWhere");

        $mode = 'INSERT';

        if($colsWhere != -1) {
            $sqlWhereColumns = array();
            $sqlWhereParams = array();

            foreach($colsWhere["key"] as $index => $value) {
                $keyValue = $colsWhere["value"][$index];

                if(strpos($keyValue, '$') !== false || strpos($keyValue, '?') !== false) {
                    $objTemplate = new \Workflow\VTTemplate($context);
                    $keyValue = $objTemplate->render($keyValue);
                }

                $sqlWhereColumns[] = '`'.$value.'` = ?';
                $sqlWhereParams[] = $keyValue;
            }

            $connection = \Workflow\ConnectionProvider::getConnection($this->get('provider'));
            $connection->database($this->get('dbname'));
            $db = $connection->getMySQLConnection();

            try {
                $sql = 'SELECT * FROM ' . $table . ' WHERE '.implode(' AND ', $sqlWhereColumns).' LIMIT 1';

                $stmt = $db->prepare($sql);
                $stmt->execute($sqlWhereParams);

                if($stmt->rowCount() > 0) {
                    $mode = 'UPDATE';
                }

            } catch (\Exception $exp) {
                \Workflow2::error_handler($exp);
                //throw new \Exception('MySQL Error: '.$exp->getMessage());
            }

        }

        $sqlColumns = array();
        $sqlParams = array();
        foreach($values["key"] as $index => $value) {
            if($mode == 'UPDATE' && $values['update'][$index] != '1') {
                continue;
            }

            $keyValue = $values["value"][$index];

            if(strpos($keyValue, '$') !== false || strpos($keyValue, '?') !== false) {
                $objTemplate = new \Workflow\VTTemplate($context);
                $keyValue = $objTemplate->render($keyValue);
            }

            $sqlColumns[] = '`'.$value.'` = ?';
            $sqlParams[] = $keyValue;
        }

        if(count($sqlColumns) > 0) {
            try {
                $connection = \Workflow\ConnectionProvider::getConnection($this->get('provider'));
                $connection->database($this->get('dbname'));
                $db = $connection->getMySQLConnection();

                if($mode == 'INSERT') {
                    $stmt = $db->prepare("INSERT INTO " . $table . " SET " . implode(',', $sqlColumns));
                    $stmt->execute($sqlParams);
                } else {
                    $stmt = $db->prepare("UPDATE " . $table . " SET " . implode(',', $sqlColumns).' WHERE '.implode(' AND ', $sqlWhereColumns));
                    $stmt->execute(array_merge($sqlParams, $sqlWhereParams));
                }
            } catch (\Exception $exp) {
                \Workflow2::error_handler($exp);
                //throw new \Exception('MySQL Error: '.$exp->getMessage());
            }
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $pause_rows = $this->get("pause_rows");
        if($pause_rows == -1) {
            $this->set("pause_rows", 50);
        }

        $cols = $this->get("cols");

        if($cols == -1) {
            $cols = array('key' => array(), 'value' => array());
        }

        foreach($cols["key"] as $index => $col) {
            if(empty($col)) {
                unset($cols["key"][$index]);
                unset($cols["value"][$index]);
                unset($cols["update"][$index]);
            }
        }

        $colsWhere = $this->get("colsWhere");
        if($colsWhere == -1) {
            $colsWhere = array('key' => array(), 'value' => array());
        }

        foreach($colsWhere["key"] as $index => $col) {
            if(empty($col)) {
                unset($colsWhere["key"][$index]);
                unset($colsWhere["value"][$index]);
            }
        }

        $viewer->assign("cols", $cols);
        $viewer->assign("colsWhere", $colsWhere);

        $references = \Workflow\VtUtils::getReferenceFieldsForModule($this->getModuleName());
        $viewer->assign("reference", $references);

        $provider = \Workflow\ConnectionProvider::getAvailableConfigurations('mysql');

        $viewer->assign('available_providers', $provider);

    }	
    public function beforeSave(&$values) {
        if($_POST['loadStructure'] == '1') {
            /**
             * @var $connection \Workflow\Plugins\ConnectionProvider\MySQL
             */
            $connection = \Workflow\ConnectionProvider::getConnection($values['provider']);
            try {
                $connection->database($values['dbname']);

                $columns = $connection->getColumns($values['table']);
            } catch (\Exception $exp) {
                $this->addConfigHint($exp->getMessage(), true);
                return;
            }

            $values['colsWhere']['key'] = $values['colsWhere']['key'] = $values['cols']['key'] = $values['cols']['value'] = array();
            foreach($columns as $colname => $column) {
                $values['cols']['key'][] = $colname;
                $values['cols']['value'][] = '';
                //$values['cols']['descr'][] = $column['Type'];
            }

        }
		/* Insert here source code to modify the values the user submit on configuration */
    }
}
