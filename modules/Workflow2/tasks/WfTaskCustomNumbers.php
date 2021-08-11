<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskCustomNumbers extends \Workflow\Task
{
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$adb = PearDatabase::getInstance();
        $serie = $this->get('serie', $context);

        $adb->query('LOCK TABLES vtiger_wf_customnumbering WRITE', false);

        $sql = 'SELECT * FROM vtiger_wf_customnumbering WHERE serie = ?';
        $result = $adb->pquery($sql, array($serie), false);

        if($adb->num_rows($result) == 0) {
            $sql = 'INSERT INTO vtiger_wf_customnumbering SET `serie` = ?, `prefix` = ?, `current` = ?, `length` = ?';

            $adb->pquery($sql, array($serie, $this->get('serie_prefix', $context), intval($this->get('serie_start', $context)) + 1, $this->get('serie_length', $context)));
            $nextId = $this->get('serie_start', $context);
        } else {
            $nextId = $adb->query_result($result, 0, 'current');

            $adb->pquery('UPDATE vtiger_wf_customnumbering SET current = current + 1 WHERE serie = ?', array($serie));
        }

        $adb->query('UNLOCK TABLES', false);

        $IDString = $this->get('serie_prefix', $context).str_pad($nextId, $this->get('serie_length', $context), '0', STR_PAD_LEFT);

        $fieldInfo = \Workflow\VtUtils::getFieldInfo($this->get('field'), getTabid($this->getModuleName()));

        $context->set($this->get('field'), $IDString);
        $sql = 'UPDATE '.$fieldInfo['tablename'].' SET `' . $fieldInfo['columnname']. '` = ? WHERE `' . $this->get('crmidCol'). '` = ?';
        $adb->pquery($sql, array($IDString, $context->getId()));

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        global $adb;

        if(!\Workflow\VtUtils::existTable("vtiger_wf_customnumbering")) {
            echo "Create table vtiger_wf_confirmation_user ... ok<br>";
            $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_customnumbering` (
              `serie` varchar(24) NOT NULL,
              `prefix` varchar(16) NOT NULL,
              `current` int(10) unsigned NOT NULL,
              `length` tinyint(4) NOT NULL,
              PRIMARY KEY (`serie`)
            ) ENGINE=InnoDB;");
        }

        $crmidColObj = CRMEntity::getInstance($this->getModuleName());
        $viewer->assign('crmidCol', $crmidColObj->table_index);

        $moduleName = $this->getModuleName();
        $fields = VtUtils::getFieldsWithBlocksForModule($moduleName, false);

        $selectedId = $this->get('field');
        if($selectedId === -1 || empty($selectedId)) {
            $sql = 'SELECT * FROM vtiger_field WHERE uitype = 4 AND tabid = '.getTabid($moduleName);

            $result = $adb->query($sql);
            $selectedId = $adb->query_result($result, 0, 'fieldname');
            $this->set('field', $selectedId);
        }

        $sql = 'SELECT * FROM vtiger_wf_customnumbering';
        $result = $adb->query($sql);
        $series = array();
        while($row = $adb->fetchByAssoc($result)) {
            $series[$row['serie']] = $row;
        }

        if(isset($series[$this->get('serie')]) && strpos($this->get('serie_prefix'), '$') === false) {
            $viewer->assign('lockFields', true);
        }
        $viewer->assign('series', $series);
        $viewer->assign('fields', $fields);
		/* Insert here source code to create custom configurations pages */
    }

    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
        $adb = \PearDatabase::getInstance();

        if(strpos($values['serie'], '$') === false) {
            $adb->pquery('UPDATE vtiger_wf_customnumbering SET current = ? WHERE serie = ?', array($values['serie_start'], $values['serie']));
        } else {
            $adb->pquery('UPDATE vtiger_wf_customnumbering SET current = ?, `prefix` = ? WHERE serie = ?', array($values['serie_start'], $values['serie_prefix'], $values['serie']));
        }
    }	
}
