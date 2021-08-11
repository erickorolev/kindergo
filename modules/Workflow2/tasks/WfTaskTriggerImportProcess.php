<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskTriggerImportProcess extends \Workflow\Task
{
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
        $filestoreid = $this->get('filestoreid', $context);
        $workflow = intval($this->get('workflow'));
        $delimiter = $this->get('delimiter', $context);
        $format = $this->get('format');
        $skipfirstrow = $this->get('skipfirstrow') == '1';

        $envId = '_block_importhash_'.$this->getBlockId();
        $envStepId = '_block_importstep_'.$this->getBlockId();
        $envExecId = '_block_importexecid_'.$this->getBlockId();

        if(!$this->isContinued()) {
            $context->setEnvironment($envId, md5(microtime(false).rand(10000,99999)));
            $context->setEnvironment($envStepId, 1);

            return array("delay" => time() + 10, "checkmode" => "static");
        }

        vglobal('_ImportTriggered', true);

        if($context->getEnvironment($envStepId) == 1) {
            $file = $context->getTempFiles($filestoreid);

            if($file == null) {
                throw new \Workflow\NonBreakException('FilestoreID you want to import could not be found.');
                return 'yes';
            }

            if(!file_exists($file['path'])) {
                return 'yes';
            }

            $encoding = 'UTF-8';
            if($this->notEmpty('encoding')) {
                $encoding = $this->get('encoding');
            }

            $importParams = array(
                'delimiter' => $delimiter,
                'mode' => $format,
                'skipfirst' => $skipfirstrow,
                'encoding' => $encoding
            );

            //$_SESSION['_import_data'][$hash] = ;

            ob_start();

            $objImport = \Workflow\Importer::create();

            $objImport->setFile($file['path']);
            $objImport->set('mode', $format);
            $objImport->set('importParams', $importParams);

            $hash = $objImport->getHash();

            $objWorkflow = new \Workflow\Main($workflow, false, $context->getUser());

            $context->setEnvironment('_import_hash', $hash);
            $context->setEnvironment('_importParams', $_SESSION['_import_data'][$hash]);
            $context->setEnvironment($envStepId, 2);

            $objWorkflow->setContext($context);

            $objWorkflow->start();

            $ready = $objImport->get('ready');
            ob_end_clean();

            if($ready == true) {
                return 'yes';
            } else {
                $context->setEnvironment('_importParams', $_SESSION['_import_data'][$hash]);
                $context->setEnvironment($envExecId, $objWorkflow->getLastExecID());
                return array("delay" => time() + 10, "checkmode" => "static");
            }
        } elseif($context->getEnvironment($envStepId) == 2) {
            $hash = $context->getEnvironment('_import_hash');
            $data = $context->getEnvironment('_importParams');
            $_SESSION['_import_data'][$hash] = $data;
            ob_start();

            $objImporter = \Workflow\Importer::getInstance($hash);

            $adb = \PearDatabase::getInstance();
            $execId = $context->getEnvironment($envExecId);

            $task = \Workflow\Queue::getQueueEntryByExecId($execId);
            if($task === false) {
                return 'yes';
            }
            //error_log("run Queue:".$task["queue_id"]);
            $sql = "DELETE FROM vtiger_wf_queue WHERE id = ".$task["queue_id"]."";
            $adb->query($sql);

            \Workflow\Queue::runEntry($task);

            $ready = $objImporter->get('ready');

            ob_end_clean();
            if($ready == true) {
                return 'yes';
            } else {

                $context->setEnvironment('_importParams', $_SESSION['_import_data'][$hash]);
                return array("delay" => time() + 10, "checkmode" => "static");
            }

        }
    }
	
    public function beforeGetTaskform($viewer) {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_settings WHERE `trigger` = "WF2_IMPORTER" AND active = 1';
        $result = $adb->query($sql);

        while($row = $adb->fetchByAssoc($result)) {
            $workflows[$row['id']] = '['.vtranslate($row['module_name'], $row['module_name']).'] '.$row['title'];
        }

        $viewer->assign('workflows', $workflows);

        if($this->get('delimiter') == -1 || $this->get('delimiter') == '') {
            $this->set('delimiter', ',');
        }

        $encodings = mb_list_encodings();
        $viewer->assign('encodings', $encodings);


    }
    public function beforeSave(&$values) {
        if(empty($values['delimiter'])) $values['delimiter'] = ',';

		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
