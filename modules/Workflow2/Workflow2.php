<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

// Demo mode deactivate every email, which would send
if(!defined('DEMO_MODE')) {
    define("DEMO_MODE", false);
}

if(!defined('E_EXPRESSION_ERROR')) {
    define("E_EXPRESSION_ERROR", 20000);
}
if(!defined('E_NONBREAK_ERROR')) {
    define("E_NONBREAK_ERROR", 20001);
}

require_once(realpath(dirname(__FILE__).'/autoload_wf.php'));

//@ini_set('memory_limit','128M');

if(!class_exists("Workflow2")) {
    class Workflow2 {
        const VERSION = "7.0101";

        public static $lastBlock = 0;

        /**
         * @var int
         */
        public static $currentBlock = 0;

        /**
         * @var \Workflow\Main
         *
         */
        public static $currentWorkflowObj = 0;

        /**
         * @var \Workflow\VTEntity
         *
         */
        public static $currentContext = null;

        /**
         * @var \Workflow\Task
         */
        public static $currentBlockObj = false;
        public static $formatCurrencies = false;

        public static $enableError = true;
        public static $isAjax = false;

        private $_workflowID;

        public function __construct($workflowID = 0) {
            $this->_workflowID = $workflowID;
        }
        public static function getNonAdminAccessControlQuery($module,$user,$scope) {
            return '';
        }
        public static function updateWorkflow($workflowId) {
            $adb = \PearDatabase::getInstance();
            $currentUser = \Users_Record_Model::getCurrentUserModel();
            $sql = 'UPDATE vtiger_wf_settings SET last_modify_by = ? WHERE id = ?';
            $adb->pquery($sql, array($currentUser->id, intval($workflowId)));
        }

        public static function getWorkflowsForModule($module_name, $only_active = 1, $trigger = "", $checkPermissions = false) {
            global $adb;
            $return = array();

            $sql = "SELECT
                        id, title, authmanagement, `trigger`, withoutrecord, collection_process, invisible
                    FROM
                        vtiger_wf_settings
                    WHERE
                        module_name = '".$module_name."'
                        ".($only_active?" AND `active` = 1":"")."
                        ".(!empty($trigger)?" AND `trigger` = '".$trigger."'":" AND `trigger` != 'WF2_FRONTENDTRIGGER'")."
                    ORDER BY
                        active DESC,
                        vtiger_wf_settings.title";
            $result = $adb->query($sql, 1);

            while($row = $adb->fetchByAssoc($result)) {
                if($checkPermissions === true) {
                    $objWorkflow = new \Workflow\Main($row["id"]);
                    if(($row["authmanagement"] == "0" || $objWorkflow->checkAuth("view"))) {
                        $return[] = $row;
                    }
                } else {
                    $return[] = $row;
                }
            }

            return $return;
        }

        public function countRunningInstances() {
            global $adb;

            $sql = "SELECT COUNT(*) as num FROM vtiger_wf_queue WHERE workflow_id = ".$this->_workflowID;
            $result = $adb->query($sql);

            return $adb->query_result($result, 0, "num");
        }
        public function countLastError() {
            global $adb;

            $sql = "SELECT COUNT(*) as num FROM vtiger_wf_errorlog WHERE workflow_id = ".$this->_workflowID;
            $result = $adb->query($sql);

            return $adb->query_result($result, 0, "num");
        }


        public function existTable($tableName) {
            return \Workflow\DbCheck::existTable($tableName);
        }

        /**
         * @param bool $full should all columns be checked or only new one
         */
        public function checkDB($full = false) {
            include dirname(__FILE__).'/dbCheck.php';
        }

        public function deleteTrigger($key) {
            $adb = \PearDatabase::getInstance();
            $sql = 'UPDATE vtiger_wf_trigger SET deleted = 1 WHERE `key` = ?';

            \Workflow\VtUtils::pquery($sql, array($key));
        }

        public function addTrigger($key, $label, $module, $description = '') {
            global $adb;

            $result = $adb->pquery("SELECT id FROM vtiger_wf_trigger WHERE `key` = ?", array($key));

            if($adb->num_rows($result) == 0) {
                //echo "[add] Trigger '".$key."' - '".getTranslatedString($label, $module)."'<br>";
                $adb->pquery("INSERT INTO vtiger_wf_trigger SET `key` = ?, `label` = ?, `module` = ?, description = ?", array($key, $label, $module, $description));
            } else {
                //echo "[edit] Trigger '".$key."' - '".getTranslatedString($label, $module)."'<br>";
                $adb->pquery("UPDATE vtiger_wf_trigger SET `label` = ?, `module` = ?, description = ? WHERE `key` = ?", array($label, $module, $description, $key));
            }
        }

        public static function log($crmid, $wfid, $blockid, $log) {
            if(constant("LOG_HANDLER") == "file") {
                error_log(date("[Y-m-d H:i:s]")." - ".str_pad($log, 20)." # WF: ".str_pad($wfid, 5)." # Block: ".str_pad($blockid, 6)." # CRMID: ".str_pad($crmid, 10)."\n", 3, constant("LOG_HANDLER_VALUE"));
            } elseif(constant("LOG_HANDLER") == "table") {
                global $adb;
                $sql = "INSERT INTO vtiger_wf_logtbl SET workflow = ?, crmid = ?, blockid = ?, log = ?";
                $adb->pquery($sql, array($wfid, $crmid, $blockid, trim($log)));
            }
        }

        public function resetDB() {
            global $adb;
            $sql = "TRUNCATE TABLE `vtiger_wf_types`;";
            $adb->query($sql);

            $sql = "TRUNCATE TABLE `vtiger_wf_types_seq`;";
            $adb->query($sql);

            $adb->query("INSERT INTO vtiger_wf_types_seq SET id = 1");
        }

        public function addDefaultTrigger() {

            $this->addTrigger("WF2_CREATION", "LBL_START_CREATION", "Workflow2", 'Executed after a record was created');
            $this->addTrigger("WF2_EVERY_SAVE", "LBL_START_EVERY", "Workflow2", 'Executed after a record was saved');
            $this->addTrigger("WF2_MANUELL", "LBL_START_MANUELL", "Workflow2");
            $this->addTrigger("WF2_MAILSEND", "LBL_START_MAIL_SEND", "Workflow2", 'Executed if you send an email to the record');
            $this->addTrigger("WF2_MODCOMMENT", "LBL_START_CREATE_COMMENT", "Workflow2", 'Executed if you create a new comment');
            $this->addTrigger("WF2_IMPORTER", "LBL_IMPORTER_TRIGGER", "Workflow2", 'Only used for file import processing');
            $this->addTrigger("WF_REFERENCE", "LBL_REFERENCE_TRIGGERED", "Workflow2", 'Executed directly after choosing a reference in editor');
            $this->addTrigger("WF2_BEFOREDELETE", "LBL_BEFOREDELETE_TRIGGER", "Workflow2", 'Executed before a record was deleted');

            $this->addTrigger("WF2_FRONTENDTRIGGER", "LBL_FRONTEND_TRIGGER", "Workflow2", 'Executed only in Create-/EditView');
            $this->addTrigger("WF2_ADDDOCUMENT", "LBL_ADD_DOCUMENT", "Workflow2", 'Executed if a Document was added to Record');

            $this->addTrigger("WF2_MAILSCANNER", "LBL_MAILSCANNER_TRIGGER", "Workflow2", 'Executed by Workflow Designer Mailscanner');

        }
        public function checkSettingsField() {
            global $adb;
            $sql = 'DELETE FROM vtiger_settings_field WHERE linkto = "index.php?module=Workflow2&action=admin&parenttab=Settings"';
            $adb->query($sql);

            $sql = "SELECT * FROM vtiger_settings_field WHERE linkto = 'index.php?module=Workflow2&view=Index&parent=Settings'";
            $result = $adb->query($sql);

            if($adb->num_rows($result) == 0) {
                $fieldid = $adb->getUniqueID('vtiger_settings_field');
                $blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
                $seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", array($blockid), true);
                if ($adb->num_rows($seq_res) > 0) {
                    $cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
                    if ($cur_seq != null)	$cur_seq = $cur_seq + 1;
                }
                $seq_res = $adb->pquery("SELECT max(fieldid) AS max_seq FROM vtiger_settings_field WHERE fieldid >= ?", array($fieldid), true);
                if ($adb->num_rows($seq_res) > 0) {
                    $tmp = $adb->query_result($seq_res, 0, 'max_seq');
                    if (!empty($tmp)) {
                        $fieldid = $tmp + 1;
                        $sql = 'UPDATE vtiger_settings_field_seq SET id = '.($fieldid);
                        $adb->query($sql);
                    }
                }
                if(empty($fieldid)) {
                    $fieldid = $adb->getUniqueID('vtiger_settings_field');
                }
                $adb->pquery('INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence)
                    VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'Workflow Designer', 'Smarty/templates/modules/Workflow2/settings.png', 'Design your Workflows', 'index.php?module=Workflow2&view=Index&parent=Settings', $cur_seq), true);
            }
        }

        public function checkCustomInventoryFields() {
            $additionalFields = \Workflow\VTInventoryEntity::getAdditionalProductFields();

            $cacheFile = vglobal('root_directory').'modules'.DIRECTORY_SEPARATOR.'Workflow2'.DIRECTORY_SEPARATOR.'extends'.DIRECTORY_SEPARATOR.'InventoryFields.inc.php';

            if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'extends'.DIRECTORY_SEPARATOR.''))
            $adb = \PearDatabase::getInstance();

            $defaulColumns = array_merge(array('id','productid', 'sequence_no', 'quantity', 'listprice' ,'discount_percent', 'discount_amount', 'comment', 'description', 'incrementondel', 'lineitem_id','purchase_cost'), array_keys($additionalFields));
            $result = $adb->query('DESCRIBE  `vtiger_inventoryproductrel`');
            $customColumns = array();
            $foundFields = array();

            while($row = $adb->fetchByAssoc($result)) {
                if(!in_array($row['field'], $defaulColumns)) {
                    if(!preg_match('/tax[0-9]+/', $row['field'])) {
                        $additionalFields[$row['field']] = array('inventoryField' => $row['field'],'label' => $row['field'], 'implemented' => false);
                        echo 'add inventory field `'.$row['field'].'`<br/>';
                    }
                }
                $foundFields[] = $row['field'];
            }
            foreach($additionalFields as $fieldName => $fieldData) {
                if(!in_array($fieldName, $foundFields)) {
                    unset($additionalFields[$fieldName]);
                    echo 'remove inventory field `'.$fieldName.'`<br/>';
                }
            }

            if(count($additionalFields) > 0) {
                file_put_contents($cacheFile, '<?php return '.var_export($additionalFields, true).';');
            }
        }

        public function installExtensions() {
            $rootUrl = vglobal('root_directory');

            $files = array(
                'PDFMakerWorkflow2.php' => $rootUrl . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'PDFMaker' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR,
                'base64image' => $rootUrl . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'jquery' . DIRECTORY_SEPARATOR . 'ckeditor' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR,
            );

            foreach($files as $file => $path) {
                if(!is_dir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $file)) {
                    if(file_exists($path) && is_writeable($path)) {
                        $this->copyr(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $file, $path  . DIRECTORY_SEPARATOR . $file);
                    }
                } else {
                    if(file_exists($path) && is_writeable($path)) {
                        $this->copyr(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $file, $path  . DIRECTORY_SEPARATOR);
                    }
                }
            }
        }

        private function copyr($source, $dest)
        {
            // recursive function to copy
            // all subdirectories and contents:
            if(is_dir($source)) {
                $dir_handle=opendir($source);
                $sourcefolder = basename($source);
                mkdir($dest."/".$sourcefolder);
                while($file=readdir($dir_handle)){
                    if($file!="." && $file!=".."){
                        if(is_dir($source."/".$file)){
                            $this->copyr($source."/".$file, $dest."/".$sourcefolder);
                        } else {
                            copy($source."/".$file, $dest."/".$file);
                        }
                    }
                }
                closedir($dir_handle);
            } else {
                // can also handle simple copy commands
                copy($source, $dest);
            }
        }

        public function AddGlobalEvents() {
            vimport('~~include/events/include.inc');
            $adb = \PearDatabase::getInstance();
            $adb->dieOnError = true;
            $em = new VTEventsManager($adb);

            $em->registerHandler('vtiger.entity.aftersave.final', 'modules/Workflow2/WfEventHandler.php', 'WfEventHandler');
            $em->registerHandler('vtiger.entity.aftersave', 'modules/Workflow2/WfEventHandler.php', 'WfEventHandler');
            $em->registerHandler('vtiger.entity.beforedelete', 'modules/Workflow2/WfEventHandler.php', 'WfEventHandler');

            $sql = 'UPDATE vtiger_eventhandlers SET is_active = 1 WHERE handler_path = "modules/Workflow2/WfEventHandler.php"';
            $adb->query($sql);
        }

        public function DelGlobalEvents() {
            vimport('~~include/events/include.inc');
            $em = new VTEventsManager(\PearDatabase::getInstance());
            $em->unregisterHandler('WfEventHandler');
        }

        public function installLanguages() {
            $adb = \PearDatabase::getInstance();

            $languages = array();
            $languages[] = vglobal('default_language');

            $sql = 'SELECT language FROM vtiger_users WHERE language != ? GROUP BY language ';
            $result = $adb->pquery($sql, array(vglobal('default_language')));

            while($row = $adb->fetchByAssoc($result)) {
                $languages[] = $row['language'];
            }

            $objLM = new \Workflow\SWExtension\LanguageManager('Workflow2');
            $objLM->installLanguages($languages);
        }

        public function checkRepository() {
            $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
            $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
            $GenKey = new $className("Workflow2", $moduleModel->version);
            $licenseHash = $GenKey->gb8d9a4f2e098e53aee15b6fd5f9456705f64f354();

            $adb = \PearDatabase::getInstance();
            $sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE ? AND deleted = 0';
            $result = $adb->pquery($sql, array('%.redoo-networks.%'));
            if($adb->num_rows($result) == 0) {
                $repoId = \Workflow\Repository::register('https://repo.redoo-networks.com', $licenseHash, 'Redoo Networks Repository', true, '', $licenseHash);
            } else {
                $repoId = $adb->query_result($result, 0, 'id');
            }

            $sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE ? AND deleted = 0';
            $result = $adb->pquery($sql, array('%repository.stefanwarnat.de%'));
            $oldRepoId = $adb->query_result($result, 0, 'id');

            $sql = 'UPDATE vtiger_wf_types SET repo_id = ? WHERE repo_id = ?';
            $adb->pquery($sql, array($repoId, $oldRepoId));

            $sql = 'DELETE FROM vtiger_wf_repository_types WHERE repos_id = ?';
            $adb->pquery($sql, array($oldRepoId));

            $sql = 'UPDATE vtiger_wf_repository SET deleted = 1 WHERE id = ?';
            $adb->pquery($sql, array($oldRepoId));

        }

        public function initialize_module() {
            ob_start();

            //file_put_contents(__DIR__.'/.HTTPLicense', 1);

            $this->installExtensions();
            $this->checkDB();
            $this->installLanguages();
            //$this->insertBaseTypes();
            $this->AddHeaderLink();
            $this->AddGlobalEvents();
            $this->removeLinks();
            $this->addDefaultTrigger();
            $this->checkSettingsField();
            $this->addLinks();
            $this->checkCron();
            $this->checkCustomInventoryFields();
            $this->checkRepository();

            $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
            $workflowObj->refreshFrontendJs();

            $repos = \Workflow\Repository::getAll();
            foreach($repos as $repo) {
                $repo->installAll(\Workflow\Repository::INSTALL_ALL);
            }

            ob_end_clean();
        }

        public function checkCron() {
            $adb = \PearDatabase::getInstance();

            $cron = array(
                'name' => 'Workflow2 Queue',
                'handler_file' => 'cron/modules/Workflow2/queue.service',
                'frequency' => '600',
                'module' => 'Workflow2',
                'desc' => 'Check every 10 minutes if Workflows should be continued',
            );

            $sql = 'SELECT * FROM vtiger_cron_task WHERE name = ?';
            $result = $adb->pquery($sql, array($cron['name']));
            if($adb->num_rows($result) > 0) {
                $sql = 'UPDATE vtiger_cron_task SET status = 1, handler_file = "'.$cron['handler_file'].'" WHERE id = '.$adb->query_result($result, 0, 'id');
                $adb->query($sql);
            } else {
                Vtiger_Cron::register($cron['name'], $cron['handler_file'],$cron['frequency'], $cron['module'], 1, Vtiger_Cron::nextSequence(), $cron['desc']);
            }
        }

        public function vtlib_handler($modulename, $event_type) {
            if(function_exists('opcache_reset')) opcache_reset();

            try {
                if ($event_type == \Vtiger_Module::EVENT_MODULE_POSTINSTALL) {
                    $this->initialize_module();
                } else if ($event_type == 'module.disabled') {

                    $this->disableModule();

                    // TODO Handle actions when this module is disabled.
                } else if ($event_type == 'module.enabled') {

                    $this->initialize_module();

                    $adb = \PearDatabase::getInstance();
                    $sql = 'SELECT * FROM vtiger_wf_settings WHERE active = 1 GROUP BY module_name';
                    $result = $adb->pquery($sql, array());
                    while($row = $adb->fetchByAssoc($result)) {
                        $request = new \Vtiger_Request(array());
                        $request->set('workflowModule', $row['module_name']);
                        $request->set('hidden', true);
                        $request->set('MODE', 'ADD');

                        $sidebar = new Settings_Workflow2_SidebarToggle_Action();
                        $sidebar->process($request);
                    };

                    // TODO Handle actions when this module is enabled.
                } else if ($event_type == 'module.preuninstall') {

                    $this->removeHeaderLink();
                    $this->removeLinks();

                    // TODO Handle actions when this module is about to be deleted.
                } else if ($event_type == \Vtiger_Module::EVENT_MODULE_PREUPDATE) {
                    // TODO Handle actions before this module is updated.

                } else if ($event_type == \Vtiger_Module::EVENT_MODULE_POSTUPDATE) {
                    $this->initialize_module();
                }
            } catch (\Exception $exp) {};

        }

        public function removeHeaderLink() {
            global $adb;

            $sql = "DELETE FROM vtiger_links WHERE linktype = 'HEADERSCRIPT' AND linklabel = 'Workflow2_JS'";
            $adb->query($sql);

        }
        public function disableModule() {
            $adb = \PearDatabase::getInstance();

            $this->removeHeaderLink();
            $this->removeLinks();

            $sql = 'DELETE FROM vtiger_eventhandlers WHERE handler_path = "modules/Workflow2/WfEventHandler.php"';
            $adb->query($sql);

            $sql = 'DELETE FROM vtiger_settings_field WHERE handler_path = "modules/Workflow2/WfEventHandler.php"';
            $adb->query($sql);

            $sql = 'DELETE FROM vtiger_links WHERE linkurl LIKE  "%Workflow2%"';
            $adb->query($sql);

            $sql = 'DELETE FROM vtiger_links WHERE linkurl LIKE  "%runListViewWorkflow%"';
            $adb->query($sql);

            $sql = 'DELETE FROM vtiger_cron_task WHERE name = "Workflow2 Queue"';
            $result = $adb->query($sql);

        }
        public function getVersion() {
            global $adb;
            $sql = 'SELECT version FROM vtiger_tab WHERE name = "Workflow2"';
            $result = $adb->query($sql);
            return $adb->query_result($result, 0, 'version');
        }
        public function AddHeaderLink() {
            global $adb;

            $sql = "DELETE FROM vtiger_links WHERE linktype = 'HEADERSCRIPT' AND (linklabel = 'Workflow2_JS' OR linklabel = 'Workflow2JS')";
            $result = $adb->query($sql);

            require_once('vtlib/Vtiger/Module.php');
            $link_module = Vtiger_Module::getInstance("Workflow2");
            $link_module->addLink('HEADERSCRIPT','Workflow2JS','modules/Workflow2/js/frontend.js?v='.$this->getVersion(), "", "1");
        }

        public function addLinks() {
            $obj = Vtiger_Module::getInstance('Home');
            $obj->addLink('DASHBOARDWIDGET', 'Workflow Permissions', 'index.php?module=Workflow2&view=ShowWidget&name=Permissions');
        }

        public function removeLinks() {
            global $adb;

            $sql = "DELETE
                    FROM
                        vtiger_links
                    WHERE
                        linktype = 'LISTVIEWBASIC' AND linkurl LIKE 'executeWorkflow%'";
            $adb->query($sql);
            $sql = "DELETE
                    FROM
                        vtiger_links
                    WHERE
                        linktype = 'DASHBOARDWIDGET' AND linkurl LIKE '%=Workflow2%'";
            $adb->query($sql);
        }

        public function addType($type, $handlerClass, $file, $module, $output, $persons, $text, $category, $input = "1", $styleClass = "", $background = "", $singleModule = "", $helpUrl = "") {
            global $adb;
            //Zend_Json::$useBuiltinEncoderDecoder = true;

            $sql = "SELECT `id` FROM vtiger_wf_types WHERE `type` = ?";
            $result = $adb->pquery($sql, array($type));

            if(is_array($output)) {
                $output = \Workflow\VtUtils::json_encode($output);
            }
            if(is_array($persons)) {
                $persons = \Workflow\VtUtils::json_encode($persons);
            }
            if(is_array($singleModule)) {
                $singleModule = \Workflow\VtUtils::json_encode($singleModule);
            }

            if($adb->num_rows($result) == 0) {
                $nextID = $adb->getUniqueID("vtiger_wf_types");

                echo "Type '".$type."' creation ...";

                $values = array($nextID, $type, $handlerClass, $file, $module, $output, $persons, $text, $input, $styleClass, $background, $category, $singleModule, $helpUrl);
                $sql = "INSERT INTO vtiger_wf_types (`id`, `type`, `handlerclass`, `file`, `module`, `output`, `persons`, `text`, `input`, `styleclass`, `background`, `category`, `singlemodule`, `helpurl`) VALUES (".generateQuestionMarks($values).") ";
                $adb->pquery($sql, $values);

                echo " ok<br>";
            } else {
                echo "Type '".$type."' upgrade ...";
                $taskID = $adb->query_result($result, 0, "id");

                $values = array($taskID, $type, $handlerClass, $file, $module, $output, $persons, $text, $input, $styleClass, $background, $category, $singleModule, $helpUrl);
                $sql = "REPLACE INTO vtiger_wf_types (`id`, `type`, `handlerclass`, `file`, `module`, `output`, `persons`, `text`, `input`, `styleclass`, `background`, `category`, `singlemodule`, `helpurl`) VALUES (".generateQuestionMarks($values).") ";
                $adb->pquery($sql, $values);

                echo " ok<br>";
            }

        }

        public static function send_error($errstr, $errfile, $errline) {
            global $current_user, $adb;
            /**
             * @var $adb PearDatabase
             */
            $html = "Workflow2 Error notice</h2>
    Date: ".date("Y-m-d H:i:s")."
    LOCATION: ".$errfile." [".$errline."]
    Last Block: ".Workflow2::$lastBlock."
    Current Block: ".Workflow2::$currentBlock."

            $errstr
    ";
            set_include_path(dirname(__FILE__).'/../../'.PATH_SEPARATOR.get_include_path());

            $adminUser = Users::getActiveAdminUser();

            require_once('modules/Emails/mail.php');
            $headers = "From: " . $current_user->column_fields["email1"] . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";

            mail($adminUser->column_fields["email1"], "Workflow2 Error notice", $html, $headers);

            $sql = "SELECT stuffid FROM vtiger_homestuff WHERE stufftitle = 'Workflow2 ERROR'";
            $result = $adb->query($sql);
            if($adb->num_rows($result) == 0) {
                $stuffid = $adb->getUniqueID("vtiger_homestuff");

                $maxSequence = $adb->query_result($adb->query("(SELECT MAX(stuffsequence) as max FROM vtiger_homestuff WHERE userid = 1)"), 0, "max");

                $sql = "INSERT INTO vtiger_homestuff SET stuffsequence = ".$maxSequence.", stuffid = ".$stuffid.", stufftype = 'Notebook', userid = 1, visible = 0, stufftitle = 'Workflow2 ERROR'";
                $adb->query($sql, true);

                $sql = "INSERT INTO vtiger_notebook_contents SET userid = 1, notebookid = ".$stuffid;
                $adb->query($sql);
            } else {
                $stuffid = $adb->query_result($result, 0, "stuffid");
            }

            $oldContent = $adb->query_result($adb->query("SELECT contents FROM vtiger_notebook_contents WHERE notebookid = ".$stuffid), 0, "contents");

            $sql = "UPDATE vtiger_notebook_contents SET contents = ? WHERE notebookid = ".$stuffid;
            $adb->pquery($sql, array(html_entity_decode($oldContent)."\n\n".($html)), true);
        }
        public static function error_handler($errno = "", $errstr = "", $errfile = "", $errline = "") {

            global $current_user;

            if(error_reporting() == 0) {
                #return;
            }
            if($errno === 8) {
                return;
            }

            if ((is_object($errno))) {
                $errline = $errno->getLine();
                $errfile = $errno->getFile();
                $errstr = $errno->getMessage();
                $trace = $errno->getTrace();
                $errno = E_ERROR;
            }

            if(Workflow2::$enableError == false)
                return false;

            switch ($errno){
                case "REFERENCE_INVALID":
                case "MANDATORY_FIELDS_MISSING":
                case "ACCESS_DENIED":
                    $typestr = $errno; break;
                case E_ERROR: // 1 //
                    $typestr = 'E_ERROR'; break;
                case E_PARSE: // 4 //
                    $typestr = 'E_PARSE'; break;
                case E_CORE_ERROR: // 16 //
                    $typestr = 'E_CORE_ERROR'; break;
                case E_CORE_WARNING: // 32 //
                    $typestr = 'E_CORE_WARNING'; break;
                case E_COMPILE_ERROR: // 64 //
                    $typestr = 'E_COMPILE_ERROR'; break;
                case E_CORE_WARNING: // 128 //
                    $typestr = 'E_COMPILE_WARNING'; break;
                case E_USER_ERROR: // 256 //
                    $typestr = 'E_USER_ERROR'; break;
                case E_USER_WARNING: // 512 //
                    $typestr = 'E_USER_WARNING'; break;
                case E_RECOVERABLE_ERROR: // 4096 //
                    $typestr = 'E_RECOVERABLE_ERROR'; break;
                case E_EXPRESSION_ERROR:
                    $typestr = 'E_EXPRESSION_ERROR'; break;
                case E_NONBREAK_ERROR:
                    $typestr = 'E_NONBREAK_ERROR';
                    break;
                default:
                    return true;
            }

            global $adb;
            $databaseError = false;
            if(!empty($adb->database->_connectionID->error)) {
                $errstr .= '<br/><br/>'.$adb->database->_connectionID->error;
                $errstr .= serialize($adb);
                $databaseError = true;
            }

            $html = "<html>";
            $html .= "<body style='font-family:Arial;'>";
            $html .= "<h2>Workflow2 Error occurred [".self::getVersion()."]</h2>";
            $html .= "<table style='font-size:14px;font-family:Courier;'>";
            $html .= "<tr><td width=100>ERROR:</td><td><strong>".$typestr."</strong></td></tr>";
            $html .= "<tr><td>LOCATION:</td><td><em>".$errfile." [".$errline."]</td></tr>";
            $html .= "<tr><td>Last Block:</td><td><em>".Workflow2::$lastBlock."</td></tr>";

            if(is_object(Workflow2::$currentBlockObj)) {
                $wfId = Workflow2::$currentBlockObj->getWorkflowId();
                $html .= "<tr><td>Current Block:</td><td><a href='".vglobal('site_URL')."/index.php?module=Workflow2&view=Config&parent=Settings&workflow=".$wfId."'>WF ".Workflow2::$currentBlockObj->getWorkflowId()."</a> - <a href='".vglobal('site_URL')."/index.php?module=Workflow2&parent=Settings&view=TaskConfig&taskid=".Workflow2::$currentBlock."'><em>Block ".Workflow2::$currentBlock."</a></td></tr>";
            } else {
                $html .= "<tr><td>Current Block:</td><td><a href='".vglobal('site_URL')."/index.php?module=Workflow2&parent=Settings&view=TaskConfig&taskid=".Workflow2::$currentBlock."'><em>Block ".Workflow2::$currentBlock."</a></td></tr>";
            }


            $html .= "</table>";
            $html .= "<br>";
            $html .= $errstr;
            if($current_user->is_admin == "on") {
                $html .= "<br><br><pre>".substr(print_r(isset($trace)?$trace:debug_backtrace(), true), 0, 10000)."</pre>";
            }
            $html .= "</body>";
            $html .= "</html>";
            if($errno != E_NONBREAK_ERROR) {
                if(self::$isAjax == false || $current_user->is_admin == "on") {
                    echo "<br><br><strong>The Systemadministrator has been notified!</strong>";

                    if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
                        echo strip_tags(str_replace("</tr>", "\n", $html));
                    } else {
                        echo $html;
                    }
                } else {
                    echo \Workflow\VtUtils::json_encode(array("result" => 'error', 'errorcode' => $databaseError?$typestr:$errstr, 'message' => '<strong>'.vtranslate('An error occurred during the Process.', 'Settings:Workflow2').'</strong><br/><br/>'.vtranslate('Your Systemadministrator was notified! Please contact them for a fast solution.', 'Settings:Workflow2').'<br/><br/>'.vtranslate('Click on background to close popup', 'Settings:Workflow2').''));
                }
            }

            if(is_object(self::$currentBlockObj)) {
                $sql = "INSERT INTO vtiger_wf_errorlog SET block_id = ".intval(Workflow2::$currentBlock).", text = ?, workflow_id = ?";
                global $adb; $adb->pquery($sql, array($typestr." # ".$errstr, self::$currentBlockObj->getWorkflowId()));

                \Workflow\ExecutionLogger::getCurrentInstance()->log('Exception occured: '.$typestr, true);
            }

            //$html .= "<br><br><pre>".print_r(debug_backtrace(), true)."</pre>";

            set_include_path(dirname(__FILE__).'/../../'.PATH_SEPARATOR.get_include_path());

            if(defined("ERROR_HANDLER")) {
                if(constant("ERROR_HANDLER") == "email") {
                    $errorMail = constant("ERROR_HANDLER_VALUE");
                    if(empty($errorMail)) {
                        $adminUser = \Users::getActiveAdminUser();
                        $errorMail = $adminUser->column_fields["email1"];
                    }
                } elseif(constant("ERROR_HANDLER") == "file") {
                    error_log($errstr." # - # Block: ".Workflow2::$currentBlock." # -\n", 3, constant("ERROR_HANDLER_VALUE"));
                }
            }

            if((!defined("WFD-NO-ERRORMAIL") || constant("WFD-NO-ERRORMAIL") != true) && (!defined("WF_DEMO_MODE") || constant("WF_DEMO_MODE") != true)) {
                require_once('modules/Emails/mail.php');

                if(!class_exists("Workflow_PHPMailer")) {
                    require_once("modules/Workflow2/phpmailer/class.phpmailer.php");
                }

                $to_email = trim($errorMail, ",");

                $to = array();
                if(strpos($to_email, ';') !== false) {
                    $mails = explode(';', $to_email);
                    foreach($mails as $address) {
                        $to[] = $address;
                    }
                } else {
                    $to[] = $to_email;
                }

                send_mail('Workflow2', $to_email, "Workflow Designer", $errorMail, "Workflow2 Error occurred", $html);
                /* $mail = new Workflow_PHPMailer();
                $mail->CharSet = 'utf-8';
                $mail->IsSMTP();
                $mail->SMTPDebug = 2;
                setMailServerProperties($mail);

                ;
                #setMailerProperties($mail,$subject, $content, $from_email, $from_name, trim($to_email,","), "all", $emailID);

                $mail->Timeout = 60;

                $mail->FromName = "Workflow Manager";
                $mail->From = ;

                $mail->Subject =  ;
                $mail->MsgHTML();



                try {
                    $return = MailSend($mail);
                } catch(Workflow_phpmailerException $exp) {
                    var_dump($exp); /* debug only */
  //              }

                #send_mail("Users", $errorMail, "Workflow Manager", $errorMail,, );
            }

            if(wfIsCli()) {
                return true;
            }

            if($errno != E_NONBREAK_ERROR)
                exit();

            return true;
        }

        public static function shutdown_handler() {
            if ($error = error_get_last()) {
                if($error["message"] !== "") {
                    self::error_handler($error['type'], $error['message'], $error['file'], $error['line']);
                }
            }

            if(wfIsCli() && defined('WFD-CRON-DEBUG') && constant('WFD-CRON-DEBUG') === true) {
                $content = ob_get_clean();
                $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
                @mkdir($path . 'Workflow2Cron' . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR, 0755, true);

                file_put_contents($path . 'Workflow2Cron' . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR . date('H_i_s') . '.txt', $content);
                echo $content;
            }
        }

        public static function updateCheck($lastCheckDate = false) {
            global $adb,$vtiger_current_version;
            $moduleName = "Workflow2";
/*
            $data = $adb->fetch_array(\Workflow\VtUtils::query("SELECT last_check, available_update, version FROM vtiger_wf_config LIMIT 1"));
            $installed_version = $data["version"];
            $lastCheckDate = $data["last_check"];

            $lastCheckDate = strtotime($lastCheckDate);

            if(!empty($data["available_update"]) && (float)$data["available_update"] > $installed_version && $lastCheckDate > time() - 86400) {
                echo "<br /><div class='updateHint'>".sprintf(getTranslatedString("LBL_NEW_VERSION_AVAILABLE", "Workflow2"), (float)$data["available_update"])."</div>";
                return;
            }

            if($lastCheckDate < time() - 86400) {
                $result = \Workflow\VtUtils::query("SELECT MD5(license) as license_hash, update_channel FROM vtiger_wf_config");
                $data = $adb->fetch_array($result);

                require_once("GetCurrentVersion.php");
                $result = GetCurrentVersion($moduleName, $data["lic"."ense_h"."ash"], $data["update_channel"]);
                ini_set('default_socket_timeout', 4);

                if(!empty($result)) {

                    if($result["result"] == "ok") {

                        if($installed_version < (float)$result["version"]) {
                            echo "<br /><div class='updateHint'>".sprintf(getTranslatedString("LBL_NEW_VERSION_AVAILABLE", "Workflow2"), (float)$result["version"])."</div>";

                            $adb->query("UPDATE vtiger_wf_config SET available_update = '".(float)$result["version"]."'");
                        } else {
                            $adb->query("UPDATE vtiger_wf_config SET available_update = ''");
                        }
                    }
                }

                $adb->query("UPDATE vtiger_wf_config SET last_check = NOW();");
            }
*/
        }

        public static function purgeErrorlog() {
            $days = 14;
            global $adb;

            $sql = "DELETE FROM vtiger_wf_errorlog WHERE datum_eintrag < '".date("Y-m-d", time() - ($days * 86400))."'";
            $adb->query($sql);

        }
        public function repoUpdateCheck() {
            global $adb;

            $sql = 'SELECT * FROM vtiger_wf_repository WHERE last_update < "'.date('Y-m-d', time() - (86400 * 2)).' 04:00:00" LIMIT 1';
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $objRepo = new \Workflow\Repository($row['id']);
                $objRepo->update();
            }

        }
        public static function cleanQueue() {
            $adb = \PearDatabase::getInstance();

            $sql = 'DELETE FROM vtiger_wf_queue WHERE crmid = 0 AND locked = 1 AND `timestamp` < "'.date('Y-m-d H:i', time() - 3600 * 6).'"';
            $adb->query($sql);
        }
        public static function purgeQueue() {
            global $adb;

            $sql = "SELECT vtiger_wf_queue.id FROM vtiger_wf_queue LEFT JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_queue.block_id) WHERE vtiger_wfp_blocks.id IS NULL";
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $sql = "DELETE FROM vtiger_wf_queue WHERE id = ?";
                $adb->pquery($sql, array($row["id"]));
            }

            $sql = "SELECT vtiger_wf_queue.id FROM vtiger_wf_queue INNER JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_queue.crmid) WHERE vtiger_crmentity.deleted = 1 AND vtiger_wf_queue.nextStepTime < '".date("Y-m-d", time() - (86400 * 14))."'";
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $sql = "DELETE FROM vtiger_wf_queue WHERE id = ?";
                $adb->pquery($sql, array($row["id"]));
            }
        }
        public static function purgeLogs() {
            global $adb;

            $sql = "SELECT minify_logs_after, remove_logs_after FROM  vtiger_wf_config LIMIT 1";
            $result = $adb->query($sql, false);
            if($adb->num_rows($result) > 0) {
                $row = $adb->fetchByAssoc($result);

                $sql = "UPDATE vtiger_wf_log SET data = '', timestamp = timestamp WHERE data != '' AND timestamp < '".date("Y-m-d H:i:s", time() - $row["minify_logs_after"] * 86400)."'  LIMIT 10000";
                $logRst = $adb->query($sql);

                if($adb->getAffectedRowCount($logRst) > 0) {
                    //$adb->query("OPTIMIZE TABLE  `vtiger_wf_log`;");
                }

                $sql = "DELETE FROM vtiger_wf_log WHERE timestamp < '".date("Y-m-d H:i:s", time() - $row["remove_logs_after"] * 86400)."' LIMIT 10000";
                $result = $adb->query($sql);

                if($adb->getAffectedRowCount($result) > 0) {
                    //$adb->query("OPTIMIZE TABLE  `vtiger_wf_log`;");
                }
            }


        }
        public static function addBlocktype($type, $handlerClass,  $file, $module, $output, $persons, $text, $input, $styleClass, $background, $category) {
            global $adb;
            $seq_res = $adb->query_result($adb->query("SELECT MAX(id) AS max_seq FROM vtiger_wf_types"), 0, "max_seq");

            $adb->pquery("INSERT INTO `vtiger_wf_types`
                (`id`, `type`, `handlerclass`, `file`, `module`, `output`, `persons`, `text`, `input`, `styleclass`, `background`, `category`)
                    VALUES
                (".($seq_res + 1).", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array(
                    $type,
                    $handlerClass,
                    $file,
                    $module,
                    $output,
                    $persons,
                    $text,
                    $input,
                    $styleClass,
                    $background,
                    $category
                )
            );
        }

        /* List View Functions*/
        private $sortby_fields = array();
        function initSortByField($module) {
            $this->sortby_fields[] = "id";
        }
        /**
         * Function to Listview buttons
         * return array  $list_buttons - for module (eg: 'Accounts')
         */
        function getListButtons($app_strings) {
            $list_buttons = Array();

    /*   		if (isPermitted($currentModule, 'Delete', '') == 'yes')
                $list_buttons['del'] = $app_strings[LBL_MASS_DELETE];
            if (isPermitted($currentModule, 'EditView', '') == 'yes') {
                $list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
                // Mass Edit could be used to change the owner as well!
                //$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
            }*/
            return $list_buttons;
        }

        function getSortOrder() {
            global $log,$currentModule;
            $log->debug("Entering getSortOrder() method ...");
            if (isset($_REQUEST['sorder']))
                $sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
            else
                $sorder = "id"; // (($_SESSION[$currentModule . '_Sort_Order'] != '') ? ($_SESSION[$currentModule . '_Sort_Order']) : ($this->default_sort_order));
            $log->debug("Exiting getSortOrder() method ...");
            return $sorder;
        }

        function getOrderBy() {
    /*   		global $log, $currentModule;
            $log->debug("Entering getOrderBy() method ...");

            $use_default_order_by = '';
            if (PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
                $use_default_order_by = $this->default_order_by;
            }

            if (isset($_REQUEST['order_by']))
                $order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
            else
                $order_by = (($_SESSION[$currentModule.'_Order_By'] != '') ? ($_SESSION[$currentModule.'_Order_By']) : ($use_default_order_by));
            $log->debug("Exiting getOrderBy method ..."); */
            return "id";
        }
    }
    if(!defined("WORKFLOW2_VERSION")) {
        define("WORKFLOW2_VERSION", Workflow2::VERSION);
    }
}

if(!function_exists("wfIsCli")) {
    function wfIsCli() {

         if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
              return true;
         } else {
              return false;
         }
    }
}

if(wfIsCli() || (!empty($_REQUEST["module"]) && $_REQUEST["module"] != 'ModuleManager')) {
    if(defined('WFD-CRON-DEBUG') && constant('WFD-CRON-DEBUG') === true) {
        ob_start();
    }

    global $adb;
    $sql = "SELECT error_handler, error_handler_value, log_handler, log_handler_value FROM  vtiger_wf_config LIMIT 1";
    $result = $adb->query($sql, false);
    if($adb->num_rows($result) > 0) {
        $row = $adb->fetchByAssoc($result);
        define("ERROR_HANDLER", empty($row["error_handler"])?"email":$row["error_handler"]);
        define("ERROR_HANDLER_VALUE", $row["error_handler_value"]);
        define("LOG_HANDLER", empty($row["log_handler"])?"none":$row["log_handler"]);
        define("LOG_HANDLER_VALUE", $row["log_handler_value"]);
    }
} else {
    define("ERROR_HANDLER", "email");
    define("ERROR_HANDLER_VALUE", "");
    define("LOG_HANDLER", "none");
    define("LOG_HANDLER_VALUE", "");
}
set_error_handler(array("Workflow2", "error_handler"));
set_exception_handler(array("Workflow2", "error_handler"));

register_shutdown_function(array("Workflow2", "shutdown_handler"));

