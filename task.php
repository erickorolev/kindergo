<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskExportRecords extends \Workflow\Task
{
    protected $_javascriptFile = "WfTaskExportrecords.js";

    public function init() {
        $this->addPreset("FileActions", "resultaction", array());

        if(!empty($_POST['changeModule'])) {
            $this->set('fields', array());
        }

    }

    public function getRow($record, $moduleName) {
        $fields = $this->get('fields');

        if($moduleName == 'Custom') {
            $customMode = true;

            $recordObj  = \Workflow\VTEntity::getDummy();
        } else {
            $customMode = false;

            $recordObj = \Workflow\VTEntity::getForId($record, $moduleName);
        }

        $tmp = array();
        foreach($fields as $field) {
            switch($field['mode']) {
                case 'column':
                    if(isset($record[$field['value']])) {
                        $tmp[] = $record[$field['value']];
                    } else {
                        $tmp[] = 'wrong Index';
                    }

                    break;
                case 'function':
                    $parser = new \Workflow\ExpressionParser($field['value'], $recordObj, false); # Last Parameter = DEBUG

                    try {
                        $parser->run();
                    } catch(\Workflow\ExpressionException $exp) {
                        Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                    }

                    $tmp[] = $parser->getReturn();
                    break;
                default:
                    $tmp[] = \Workflow\VTTemplate::parse($field['value'], $recordObj);
                    break;
            }
        }

        return $tmp;

    }

    public function handleTask(&$context) {
        $adb = \PearDatabase::getInstance();

		/* Insert here source code to execute the task */

        $format = $this->get('fileformat');
        $fields = $this->get('fields');

        $tmpfile = tempnam(sys_get_temp_dir(), 'CSV');
        @unlink($tmpfile);

        $source = $this->get('source');
        $customMode = false;
        if($this->get('search_module') == 'Custom') {
            $customMode = true;
            $env = $context->getEnvironment($this->get('recordlistid'));
        } else {
            if ($source == -1 || $source == 'envid') {
                $env = $context->getEnvironment($this->get('recordlistid'));
            } elseif ($source == 'customview') {
                $search_module = $this->get("search_module");
                $parts = explode("#~#", $search_module);
                $searchModuleName = $parts[0];

                $queryGenerator = new \QueryGenerator($searchModuleName, \Users::getActiveAdminUser());
                $queryGenerator->initForCustomViewById($this->get('customviewsource'));
                $query = $queryGenerator->getQuery();
                $parts = preg_split('/FROM/i', $query);
                $sqlQuery = 'SELECT vtiger_crmentity.crmid as id_col FROM ' . $parts[1];
                $result = $adb->query($sqlQuery, true);
                $this->addStat("num Rows: " . $adb->num_rows($result));

                while ($row = $adb->fetchByAssoc($result)) {
                    $recordids[] = $row["id_col"];
                }


                $env = array(
                    'moduleName' => $searchModuleName,
                    'ids' => $recordids
                );
            }
        }

        if($customMode === false) {
            $moduleName = $env['moduleName'];
            $ids = $env['ids'];
        } else {
            $moduleName = 'Custom';
            $ids = $env;
        }

        $filename = $this->get('filename', $context);

        switch($format) {
            case 'csv':
                $file = fopen($tmpfile, 'w');
                $headline = $this->get('insertheadline');

                if($headline == '1') {
                    $headline = array();
                    foreach($fields as $field) {
                        $headline[] = $field['label'];
                    }
                    fputcsv($file, $headline, ';');
                }

                foreach($ids as $id) {

                    $tmp = $this->getRow($id, $moduleName);

                    fputcsv($file, $tmp, ';');
                }
                fclose($file);

                break;
            case 'excel':
                require_once $this->getAdditionalPath('phpexcel').'PHPExcel.php';
                // Create new PHPExcel object
                $objPHPExcel = new PHPExcel();

                // Set document properties
                $objPHPExcel->getProperties()->setCreator("Workflow Designer")
                                             ->setLastModifiedBy("Workflow Designer")
                                             ->setTitle("Workflow Designer Export")
                                             ->setSubject("Workflow Designer Export");

                $headline = $this->get('insertheadline');

                $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

                $currentROW = 1;

                if($headline == '1') {
                    $headline = array();
                    foreach($fields as $field) {
                        $headline[] = $field['label'];
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->fromArray(array($headline), NULL, 'A1');

                    $currentROW++;
                }

                foreach($ids as $id) {
                    $tmp = $this->getRow($id, $moduleName);

                    $objPHPExcel->getActiveSheet()->fromArray($tmp, '', 'A' . $currentROW);
                    $currentROW++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save($tmpfile);


                break;
        }

        \Workflow\FileAction::doActions($this->get('resultaction'), $tmpfile, $filename, $context, $context->getId(), $this->getWorkflow());
        @unlink($tmpfile);

		return "yes";
    }

    public function getFromFields() {
        if($this->_fromFields === null) {
            $search_module = $this->get("search_module");

            if(!empty($search_module)) {
                if($search_module != -1) {
                    $parts = explode("#~#", $search_module);
                }
            } else {
                return;
            }


            $this->_fromFields = VtUtils::getFieldsWithBlocksForModule($parts[0], true);
        }

        return $this->_fromFields;
    }

    public function beforeGetTaskform($viewer) {
        $fields = $this->get('fields');
        if(empty($fields) || $fields == -1) {
            $fields = array();
        }

        $viewer->assign("StaticFieldsField", 'fields');
        $viewer->assign("fields", $fields);
        $viewer->assign("fromFields", $this->getFromFields());

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

        $views = array();
        $allviews = \CustomView_Record_Model::getAll($parts[0]);
        foreach($allviews as $view) {
            $views[$view->get('cvid')] = $view->get('viewname');
        }

        $viewer->assign('customviews', $views);

        if(!empty($parts)) {
            $viewer->assign("related_tabid", $parts[1]);
            $viewer->assign("target_module_name", $parts[0]);
        }

    }
    public function beforeSave(&$values) {
        unset($values['fields']['##SETID##']);
        return $values;
		/* Insert here source code to modify the values the user submit on configuration */
    }	}
