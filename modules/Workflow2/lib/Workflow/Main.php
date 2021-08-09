<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/
namespace Workflow;
global $wf2Context;

use \Workflow2;

    class Main
    {
        protected $_workflowID;
        /**
         * @var VTEntity
         */
        protected $_context;
        protected $_origContextID = null;

        protected $_user;
        protected $_runCounter = 0;
        protected $_environment = array();
        protected $_successRedirection = false;
        protected $_successRedirectionTarget = false;
        protected $_execID = false;

        protected $executionStack = array();
        protected $_executionTrigger = "";

        protected $_isSubWorkflow = false;

        private $_tmpRedirectionConfiguration = null;
        private $_options = null;

        private static $REDIRECTION = null;
        private static $DOWNLOADS = array();

        private $_PrevLogger = null;

        private static $_ReloadAfterFinish = true;
        /**
         * @var ExecutionLogger
         */
        private $_logger = null;

        const ON_EVERY_SAVE = "WF2_EVERY_SAVE";
        const ON_FIRST_SAVE = "WF2_CREATION";
        const MANUAL_START = "WF2_MANUELL";
        const FRONTEND_WORKFLOW = "WF2_FRONTENDTRIGGER";
        const SENDMAIL_START = "WF2_MAILSEND";
        const ADD_DOCUMENT = "WF2_ADDDOCUMENT";
        const COMMENT_START = "WF2_MODCOMMENT";
        const BEFOREDELETE_START = "WF2_BEFOREDELETE";

        public static $INSTANCE = null;

        public static $counter = 0;


        /**
         * @param $workflowID
         * @param \Workflow\VTEntity $context
         * @param bool $user
         */
        public function __construct($workflowID, $context = false, $user = false) {
            $this->_workflowID = intval($workflowID);
            $this->_context = $context;
            $this->initLogger();
            $this->_PrevLogger = ExecutionLogger::getCurrentInstance();
            if($context !== false) {
                $this->logger->setCRMID($context->getId());
            }

            global $wf2Context;
            if($context !== false) {
                $wf2Context = $this->_context;
            }

            if($context instanceof \Workflow\VTEntity) {
                $this->_origContextID = $context->getId();
            }

            if($user !== false && !is_a($user, "Users")) {
                throw new \BadFunctionCallException("Workflow Permission denied for this user");
            }

            // The ExecId of this new Workflow is generated
            $this->_execID = md5(microtime(true)."#start".$workflowID.mt_rand(1000,9999));
            $this->logger->setExecId($this->_execID);

            $this->_user = $user;

            self::$INSTANCE = $this;
        }

        public function preventReload() {
            self::$_ReloadAfterFinish = false;
        }
        public static function shouldReloadAfterFinish() {
            return self::$_ReloadAfterFinish;
        }

        public function clearRedirection() {
            if($this->getExecutionTrigger() !=  self::MANUAL_START) {
                $objFrontendActions = new FrontendActions();
                $objFrontendActions->removeActionType($this->_context->getID(), 'edit', 'redirect');

                return;
            }

            self::$REDIRECTION = null;
        }

        public function setSuccessRedirection($value, $target = null) {
            if($target === null) {
                $this->_tmpRedirectionConfiguration = array('url' => $value);
                return;
            }

            if($this->getExecutionTrigger() != self::MANUAL_START) {
                $userAction = 'edit';
                if(!Workflow2::$isAjax) { $userAction = 'init'; };

                $objFrontendActions = new FrontendActions($this->_context->getModuleName());
                $objFrontendActions->push(0, 'redirect', array('url' => $value, 'target' => $target), $userAction);
                return;
            }

            if(!is_array(self::$REDIRECTION)) {
                self::$REDIRECTION = array();
            }

            self::$REDIRECTION = array('url' => $value, 'target' => $target);
        }

        public static function getFinalDownloads() {
            return self::$DOWNLOADS;
        }
        public function addFinalDownload($url, $filename) {
            self::$DOWNLOADS[] = array(
                'url' => $url,
                'filename' => $filename
            );
        }
        public function setSuccessRedirectionTarget($value) {
            $this->setSuccessRedirection($this->_tmpRedirectionConfiguration['url'], $value);
        }

        public static function getRedirection() {
            return self::$REDIRECTION;
        }

        // Deprecated Function
        public function getSuccessRedirection() {
            if(empty(self::$REDIRECTION)) return false;

            return self::$REDIRECTION['url'];
        }
        public function getSuccessRedirectionTarget() {
            if(empty(self::$REDIRECTION)) return 'same';

            return self::$REDIRECTION['target'];
        }
        public function getId() {
            return $this->_workflowID;
        }
        public function getContext() {
            return $this->_context;
        }
        public function isSubWorkflow($value = null) {
            if($value !== null) {
                $this->_isSubWorkflow = $value;
            }

            return $this->_isSubWorkflow;
        }

        public function setContext($context) {
            if($context instanceof \Workflow\VTEntity) {
                $this->_origContextID = $context->getId();
            }

            $this->_context = $context;

            global $wf2Context;
            $wf2Context = $this->_context;
        }
        public function getOptions() {
            if($this->_options !== null) {
                return $this->_options;
            }

            $this->_options = array();
            $settings = $this->getSettings();

            if(strlen($settings["options"]) > 4) {
                //\Zend_Json::$useBuiltinEncoderDecoder = true;
                $this->_options = \Workflow\VtUtils::json_decode(html_entity_decode($settings["options"]));
            }
            return $this->_options;
        }
        public function getEnvironment() {
            return $this->_context->getEnvironment();
        }
        public static function setOption($workflowId, $key, $value) {
            $adb = \PearDatabase::getInstance();
            $sql = 'SELECT options FROM vtiger_wf_settings WHERE id = ?';
            $result = $adb->pquery($sql, array(intval($workflowId)), true);

            $data = $adb->fetchByAssoc($result);
            $options = html_entity_decode($data['options']);

            if(strlen($options) > 4) {
                $options = \Workflow\VtUtils::json_decode($options);
            } else {
                $options = array();
            }
            $options[$key] = $value;

            $sql = 'UPDATE vtiger_wf_settings SET options = ? WHERE id = ?';
            $adb->pquery($sql, array(\Workflow\VtUtils::json_encode($options), $workflowId), true);
        }
        public function getEnvironmentVariables() {
            $adb = \PearDatabase::getInstance();

            $sql = "SELECT * FROM vtiger_wfp_blocks WHERE workflow_id = ".$this->_workflowID." AND env_vars != ''";
            $result = $adb->query($sql);

            $envVars = array();
            if($adb->num_rows($result) > 0) {
                while($row = $adb->fetchByAssoc($result)) {
                    $entity = explode("#~~#", $row["env_vars"]);
                    foreach($entity as $ent) {
                        if(!in_array("env".$ent."]", $envVars)) {
                            $envVars[] = "env".$ent."]";
                        }
                    }
                }
            }
            return $envVars;
        }
        public static function import($name, $content, $module_name = false) {
            global $adb;

            $moduleModel = \Vtiger_Module_Model::getInstance("Workflow2");
            if($content["main"]["workflow_version"] > $moduleModel->version && empty($_COOKIE['importall'])) {
                throw new \Exception('Exported with Workflow Designer Version '.$content["main"]["workflow_version"].'. You need at least this version to import!');
                return;
            }
            if($content["main"]["workflow_version"] < $moduleModel->version) {
                switch($content["main"]["workflow_version"]) {
                    case 0.2:
                        // Do something new in Version 0.2 DON'T BREAK!
                    case 1:
                        // Do something new in Version 1.0 DON'T BREAK!
                }
            }

            // vtiger_wf_settings
            $parameters = array();
            $sqlFields = array();

            unset($content["settings"]["id"]);
            $content["settings"]["active"] = "0";

            if(!empty($name)) {
                $content["settings"]["title"] = $name;
            }

            foreach($content["settings"] as $key => $value) {
                if($key == "module_name" && $module_name !== false) {
                    $value = $module_name;
                }

                if($key == "optionns")
                    continue;

                $sqlFields[] = "`".$key."` = ?";
                $parameters[] = $value;
            }

            $sql = "INSERT INTO vtiger_wf_settings SET ".implode(", ", $sqlFields);
            $adb->pquery($sql, $parameters, true);

            $workflowID = \Workflow\VtUtils::LastDBInsertID();
            // vtiger_wf_settings
            $objWorkflow = new Main($workflowID);

            $blockAliasList = array();
            // vtiger_wfp_blocks
            foreach($content["blocks"] as $key => $block) {
                $parameters = array();
                $sqlFields = array();

                $blockData = $block["blockData"];
                unset($block["blockData"]);

                $block["workflow_id"] = $workflowID;
                $blockAlias = $key;
                unset($block["id"]);

                foreach($block as $key => $value) {
                    $sqlFields[] = "`".$key."` = ?";
                    $parameters[] = html_entity_decode($value);
                }

                $sql = "INSERT INTO vtiger_wfp_blocks SET ".implode(", ", $sqlFields);
                $adb->pquery($sql, $parameters);

                $blockAliasList[$blockAlias] = \Workflow\VtUtils::LastDBInsertID();

                $objHandler = \Workflow\Manager::getTaskHandler($block["type"], $blockAliasList[$blockAlias], $objWorkflow);
                $objHandler->import($blockData);
            }
            // vtiger_wfp_blocks

            // vtiger_wfp_objects
            foreach($content["objects"] as $key => $block) {
                $parameters = array();
                $sqlFields = array();

                $block["workflow_id"] = $workflowID;
                $blockAlias = $key;
                unset($block["id"]);

                foreach($block as $key => $value) {
                    $sqlFields[] = "`".$key."` = ?";
                    $parameters[] = $value;
                }

                $sql = "INSERT INTO vtiger_wfp_objects SET ".implode(", ", $sqlFields);
                $adb->pquery($sql, $parameters);

                $blockAliasList[$blockAlias] = \Workflow\VtUtils::LastDBInsertID();
            }
            // vtiger_wfp_objects

            // vtiger_wfp_connections
            foreach($content["connections"] as $block) {
                $parameters = array();
                $sqlFields = array();

                $block["workflow_id"] = $workflowID;
                $blockAlias = $block["id"];
                unset($block["id"]);

                $block["source_id"] = $blockAliasList[$block["source_id"]];
                $block["destination_id"] = $blockAliasList[$block["destination_id"]];

                foreach($block as $key => $value) {
                    $sqlFields[] = "`".$key."` = ?";
                    $parameters[] = $value;
                }

                $sql = "INSERT INTO vtiger_wfp_connections SET ".implode(", ", $sqlFields);
                $adb->pquery($sql, $parameters);
            }
            // vtiger_wfp_connections

        }

        public function export() {
            global $adb;
//$adb->dieOnError = 1;
            $blockAliasList = array();

            $data = array(
                "blocks" => array(),
                "connections" => array(),
                "objects" => array()
            );

            $sql = "SELECT * FROM vtiger_wfp_blocks WHERE workflow_id = ".$this->_workflowID;
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $objTask = \Workflow\Manager::getTaskHandler($row["type"], $row["id"], $this);
                $blockAlias = $row["id"]."_".md5($row["id"]);

                $blockAliasList[$row["id"]] = $blockAlias;

                $row["blockData"] = $objTask->export();

                unset($row["id"]);
                $data["blocks"][$blockAlias] = $row;
            }

            $sql = "SELECT * FROM vtiger_wfp_objects WHERE workflow_id = ".$this->_workflowID;
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $blockAlias = $row["id"]."_".md5($row["id"]);

                $blockAliasList[$row["id"]] = $blockAlias;

                unset($row["id"]);
                $data["objects"][$blockAlias] = $row;
            }

            $sql = "SELECT * FROM vtiger_wfp_connections WHERE workflow_id = ".$this->_workflowID." AND deleted = 0";
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $row["source_id"] = $blockAliasList[$row["source_id"]];
                $row["destination_id"] = $blockAliasList[$row["destination_id"]];

                unset($row["id"]);
                $data["connections"][] = $row;
            }
            $sql = "SELECT * FROM vtiger_wf_settings WHERE id = ".$this->_workflowID."";
            $result = $adb->query($sql);

            while($row = $adb->fetchByAssoc($result)) {
                $data["settings"] = $row;
            }

            return $data;

        }
        public function countRunningInstances() {
            global $adb;

            $sql = "SELECT COUNT(*) as num FROM vtiger_wf_queue WHERE workflow_id = ".$this->_workflowID;
            $result = $adb->query($sql);

            return $adb->query_result($result, 0, "num");
        }
        public function countLastError() {
            global $adb;

            $sql = "SELECT COUNT(*) as num FROM vtiger_wf_errorlog WHERE workflow_id = ".$this->_workflowID.' AND datum_eintrag >= "'.date('Y-m-d', time() - (14 * 86400)).'"';
            $result = $adb->query($sql);

            return $adb->query_result($result, 0, "num");
        }

        private $_settings = null;
        public function getSettings() {
            global $adb;

            if($this->_settings !== null) {
                return $this->_settings;
            }

            $sql = "SELECT * FROM vtiger_wf_settings WHERE id = ".$this->_workflowID;
            $result = \Workflow\VtUtils::query($sql);

            $this->_settings = $adb->fetch_array($result);

            return $this->_settings;
        }

        public function hasRequestValues($key) {
            $reqValues = $this->_context->getEnvironment('_reqValues');
            if(isset($reqValues[$key])) {
                return true;
            }

            return false;
        }

        public function isFrontendWorkflow() {
            $settings = $this->getSettings();

            return $settings['trigger'] == self::FRONTEND_WORKFLOW;
        }

        public function resetRequestValueKey($key) {
            $reqValues = $this->_context->getEnvironment('_reqValues');
            unset($reqValues[$key]);
            $this->_context->setEnvironment('_reqValues', $reqValues);
        }

        public function requestValues($key, $FormGeneratorExport, \Workflow\Task $task, $message, \Workflow\VTEntity $context, $stoppable = false, $pausable = true, $options = array()) {
            $queue_id = \Workflow\Queue::addEntry($task, $context->getUser(), $context, 'static', false, 1, false);

            if(empty($options['successText'])) $options['successText'] = 'Execute Workflow';

            ExecutionLogger::getCurrentInstance()->log('Start Request values: '.$message);

            $userQueueId = \Workflow\Userqueue::add(
                'requestValue',
                $queue_id,
                $message,
                $task->getExecId(),
                array(
                    "result" => "reqvalues",
                    'request_message' => $message,
                    'stoppable' => $stoppable,
                    'crmId' => $context->getId(),
                    'blockId' => $task->getBlockId(),
                    'fields_key' => $key,
                    'execId' => $task->getExecId().'##'.$task->getBlockId(),
                    'handler' => '\Workflow\Preset\FormGenerator',
                    'handlerConfig' => $FormGeneratorExport,
                    'pausable' => $pausable,
                    'options' => $options
                )
            );

            if($this->getExecutionTrigger() != self::MANUAL_START) {
                $objFrontendAction = new \Workflow\FrontendActions($context->getModuleName());
                $objFrontendAction->push($context->getId(), 'requestValues', array(
                    'crmid' => $context->getId(),
                    'execid' => $task->getExecId(),
                    'blockid' => $task->getBlockId(),
                ), 'edit');
            }
        }

        /**
         * @param $auth
         * @param bool|Users $user
         * @return bool
         */
        public function checkAuth($auth, $user = false) {
            global $adb, $current_user;

            $settings = $this->getSettings();
            if($settings["authmanagement"] == "0") {
                return true;
            }
            if($current_user->is_admin == 'on') {
                return true;
            }

            if($user === false) {
                $user = $current_user;
            }

            switch($auth) {
                case "edit":
                    $value = 3;
                break;
                case "view":
                    $value = 2;
                    break;
                case "exec":
                    $value = 1;
                    break;
            }
            if(empty($value)) {
                return false;
            }

            $userID = $user->id;
            $sql = "SELECT auth_value FROM vtiger_wf_auth WHERE workflow_id = ".$this->_workflowID." AND key_id = 'user".$userID."'";
            $result = $adb->query($sql);

            if($adb->num_rows($result) > 0) {
                if($adb->query_result($result, 0, "auth_value") < $value) {
                    return false;
                } else {
                    return true;
                }
            }

            $sql = "SELECT auth_value FROM vtiger_wf_auth WHERE workflow_id = ".$this->_workflowID." AND key_id = ?";
            $result = $adb->pquery($sql, array('role'.$user->column_fields["roleid"]));
            if($adb->num_rows($result) > 0) {
                if($adb->query_result($result, 0, "auth_value") < $value) {
                    return false;
                } else {
                    return true;
                }
            }

            return true;
        }
        public function getAuthDataAll() {
            global $adb;

            $sql = "SELECT * FROM vtiger_wf_auth WHERE `workflow_id` = ?";
            $result = $adb->pquery($sql, array($this->_workflowID));

            while($row = $adb->fetchByAssoc($result)) {
                $auth[$row["key_id"]] = $row["auth_value"];
            }

            return $auth;
        }
        public function setAuthValue($key_id, $value) {
            if($value != "0" && $value != "1" && $value != "2" && $value != "3" && $value != "-1") {
                return false;
            }

            if(substr($key_id, 0, 4) != "role" && substr($key_id, 0, 4) != "user") {
                return false;
            }

            global $adb;

            if($value == "-1") {
                $sql = "DELETE FROM vtiger_wf_auth  WHERE `workflow_id` = ? AND `key_id` = ?";
                $adb->pquery($sql, array($this->_workflowID, $key_id));

                return true;
            }

            $sql = "SELECT auth_value FROM vtiger_wf_auth WHERE `workflow_id` = ? AND `key_id` = ?";
            $result = $adb->pquery($sql, array($this->_workflowID, $key_id));

            if($adb->num_rows($result) > 0) {
                $sql = "UPDATE vtiger_wf_auth SET auth_value = ? WHERE `workflow_id` = ? AND `key_id` = ?";
                $adb->pquery($sql, array($value, $this->_workflowID, $key_id));
            } else {
                $sql = "INSERT INTO vtiger_wf_auth SET auth_value = ?,`workflow_id` = ?, `key_id` = ?";
                $adb->pquery($sql, array($value, $this->_workflowID, $key_id));
            }
        }

        public function hasAuthManagement() {
            $settings = $this->getSettings();

            return $settings["authmanagement"] == "1";
        }

        /* Main Workflow Handing */
        public function start() {
            try {
                global $adb, $current_user;

                Workflow2::log($this->_context->getWsId(), $this->_workflowID, 0, "Exec WF");

                $settings = $this->getSettings();

                // handle only once per Record Check START
                if ($settings['once_per_record'] == "1" && !$this->_context->isDummy()) {
                    $dataKey = '__internal_oncePerRecord_' . $this->getId();
                    $sql = 'SELECT dataid FROM vtiger_wf_entityddata WHERE crmid = ? AND `key` = ?';
                    $result = $adb->pquery($sql, array($this->_context->getId(), $dataKey));

                    if ($adb->num_rows($result) > 0) {
                        return false;
                    } else {
                        $sql = 'INSERT INTO vtiger_wf_entityddata SET `crmid` = ?, `key` = ?, `value` = ?';
                        $adb->pquery($sql, array($this->_context->getId(), $dataKey, array(serialize(1))), true);
                    }
                }
                // handle only once per Record Check END

                $current_user = $this->getUser();
                VTEntity::setUser($current_user);


                $sql = "SELECT id FROM vtiger_wfp_blocks WHERE workflow_id = " . $this->_workflowID . " AND type='start' LIMIT 1";
                $result = \Workflow\VtUtils::query($sql);
                $row = $adb->fetch_array($result);

                $start = \Workflow\Manager::getTaskHandler("start", $row["id"], $this);

                $start->setExecId($this->_execID);

                $_SERVER["runningWorkflow" . $this->_workflowID] = true;

                $this->pushExecutionStack($start);
                $this->handleExecutionStack();
                #$this->handleTasks($start);

                unset($_SERVER["runningWorkflow" . $this->_workflowID]);

                Workflow2::log($this->_context->getWsId(), $this->_workflowID, 0, "Finish WF");
            } catch (\Exception $exp) {
                \Workflow2::error_handler($exp);
            }

            ExecutionLogger::setCurrentInstance($this->_PrevLogger);
        }

        public function getLastExecID() {
            return $this->_execID;
        }
        /**
         * @param $tasks Task[]
         */
        public function handleTasks($tasks, $lastBlockID = 0, $lastBlockOutput = "") {
            global $adb;

            if(!is_array($tasks))
                $tasks = array($tasks);

            foreach($tasks as $task) {
                $this->pushExecutionStack($task, false);
            }

            $this->handleExecutionStack();
        }

        /**
         * @param $task Task
         * @param $timestamp int
         * @deprecated
         */
        public function addQueue($task, $checkMode, $timestamp) {
            global $adb;

            Queue::addEntry($task, $this->_user, $this->_context, $checkMode, $timestamp);
        }

        /**
         * Gibt aktuellen User aus, oder false, wenn keiner festgelegt wurde
         * @return bool
         */
        public function getUser() {
            return $this->_user;
        }

        public function allowExecution($crmid) {
            $settings = $this->getSettings();

            if($settings["simultan"] == "2" && !$this->isRunning($crmid)) {
                return true;
            } else {
                return false;
            }
        }

        /* Helper */
        public function checkCondition($entityData) {
            $settings = $this->getSettings();

            $shouldRun = false;

            switch($settings["trigger"]) {
                case self::ON_EVERY_SAVE:
                   // Process shouldn't run twice on one record
                    if($settings["simultan"] == "2" && !$this->isRunning($entityData->getId())) {
                        return true;
                    } else if($settings["simultan"] == "1") {
                        // Process could run twice on one record
                       return true;
                    } else {
                       return false;
                    }
                    break;
                case self::ON_FIRST_SAVE:
                    if($this->_context->isNew()) {
                        if($settings["simultan"] == "2" && !$this->isRunning($entityData->getId())) {
                            return true;
                        } else if($settings["simultan"] == "1") {
                            // Process could run twice on one record
                           return true;
                        } else {
                           return false;
                        }
                    } else {
                        return false;
                    }
                    break;
                case self::MANUAL_START:
                    return false;
                    break;
                case self::SENDMAIL_START:
                case self::ADD_DOCUMENT:
                    if($settings["simultan"] == "2" && !$this->isRunning($entityData->getId())) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case self::BEFOREDELETE_START:
                    if($settings["simultan"] == "2" && !$this->isRunning($entityData->getId())) {
                        return true;
                    } else {
                        return false;
                    }
                break;
                case self::COMMENT_START:
                    if($settings["simultan"] == "2" && !$this->isRunning($entityData->getId())) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
                default:
                    // Return true to execute any other custom trigger
                    return true;
                    break;
            }
        }

        /**
         * Checks If this Workflow is running on this crmID
         *
         * @param $crmid
         * @return bool is this Process running on this crmID?
         */
        public function isRunning($crmid) {
            global $adb;

            if(empty($crmid)) return false;

            $sql = "SELECT id FROM vtiger_wf_queue WHERE workflow_id = ".$this->_workflowID." AND crmid = ? AND hidden = 0";
            $result = $adb->pquery($sql, array($crmid));

            if($adb->num_rows($result) > 0) {
                return $adb->query_result($result, 0, 'id');
            }

            if($_SERVER["runningWorkflow".$this->_workflowID] === true) {
                return true;
            }

            return false;
        }

        /**
         * @param Task $task
         * @param bool $atFirst
         */
        public function pushExecutionStack(Task $task, $atFirst = true) {
            if($atFirst) {
                array_unshift($this->executionStack, $task);
            } else {
                $this->executionStack[] = $task;
            }
        }

        /**
         * @return bool|Task
         */
        public function popExecutionStack() {

            if(is_array($this->executionStack) && count($this->executionStack) > 0) {
                return array_shift($this->executionStack);
            } else {
                return false;
            }

        }
        public function setExecutionTrigger($value) {
            $this->_executionTrigger = $value;
        }
        public function getExecutionTrigger() {
            return $this->_executionTrigger;
        }
        public function isEmptyExecutionStack() {
            return count($this->executionStack) == 0;
        }

        public function checkExecuteCondition($crmid, $conditions = false) {
            $adb = \PearDatabase::getInstance();

            if($crmid instanceof VTEntity) {
                $context = $crmid;
            } else {
                $context = VTEntity::getForId($crmid);
            }

            if($conditions === false) {
                $sql = "SELECT view_condition, active FROM vtiger_wf_settings WHERE id = ".$this->_workflowID;
                $result = $adb->query($sql);
                $conditions = $adb->query_result($result, 0, "view_condition");
                $active = $adb->query_result($result, 0, "active");
            }

            if($active == '0') {
                return false;
            }

            if((!is_array($conditions) && strlen($conditions) <= 5) || count($conditions) == 0) {
                return true;
            }
            try {
                $conditions = \Workflow\VtUtils::json_decode(html_entity_decode($conditions, ENT_QUOTES));
            } catch (Exception $exp) {
                return true;
            }
            $checked = ConditionCheck::getInstance();

            $result = $checked->check($conditions, $context);

            return $result;
        }

        public function initLogger() {
            if(empty($this->logger)) {
                $this->logger = new ExecutionLogger($this->_workflowID);
            }
        }
        public function handleExecutionStack() {

            $options = $this->getOptions();
            if(!empty($options['timezone'])) {
                date_default_timezone_set($options['timezone']);
            } else {
                date_default_timezone_set(vglobal('default_timezone'));
            }

            global $adb;
            \Workflow2::$currentWorkflowObj = $this;
            \Workflow2::$currentContext = &$this->_context;

            ExecutionLogger::setCurrentInstance($this->logger);
            $this->logger->setCRMID($this->_context->getId());

            while(!$this->isEmptyExecutionStack()) {
                $task = $this->popExecutionStack();

                $this->_execID = $task->getExecId();
                $this->logger->setExecId($this->_execID);

                if(wfIsCli()) {
                    echo "Start Block WF ".$this->_workflowID." - ".$task->getBlockId()."\n";
                }
                $task->setWorkflow($this);
                $task->setWorkflowId($this->_workflowID);

                //$start = microtime(true);
                $this->_runCounter++;

                Workflow2::log($this->_context->getWsId(), $this->_workflowID, $task->getBlockId(), "  Start Block");

                Workflow2::$currentBlock = $task->getBlockId();
                Workflow2::$currentBlockObj = $task;
                Workflow2::$formatCurrencies = $task->isFormatedCurrencyMode();

                $prevTask = $task->getPrevTask();
                if(!empty($prevTask)) {
                    $this->logger->setLastBlockId($prevTask->getBlockId(), $task->getPrevOutput());
                }

                $this->logger->startBlock($task->getBlockId());

                $return = $task->handleTask($this->_context);

                if($this->_context->getId() != $this->_origContextID) {
                    throw new \Exception("A task [ID".$task->getBlockId()."] manipulate the Workflow Context Record. This is a bug. Please report and attach: [ERR13,".$this->_context->getId().",".$this->_origContextID.",".get_class($task)."]!");
                }

                Workflow2::$lastBlock = $task->getBlockId();

                Workflow2::log($this->_context->getWsId(), $this->_workflowID, $task->getBlockId(), "  Finish Block");

                $statData = $task->getStat();
                /*
                if(!empty($statData)) {
                    $statBlob = gzcompress(serialize($statData), 4);
                } else {
                    $statBlob = "";
                }
                $prevTask = $task->getPrevTask();

                $sql = "INSERT INTO vtiger_wf_log SET durationms = ?, execID = ?, blockID = ?, workflow_id = ?, timestamp = NOW(), lastBlockID = ?, crmid = ?, lastBlockOutput = ?, `data` = ?";
                $adb->pquery($sql, array((microtime(true) - $start) * 1000, $task->getExecId(), $task->getBlockId(), $this->_workflowID, $prevTask!=false?$prevTask->getBlockId():$task->getBlockId(), $this->_context->getId(), $task->getPrevOutput(), $statBlob));
                */

                if($return !== false) {
                    if(empty($return)) $return = false;
                    if(!is_array($return) && $return !== false) $return = array($return);



                    if(isset($_COOKIE["stefanDebug"]) && $_COOKIE["stefanDebug"] >= "1") {
                        echo "Result:";
                        /* ONLY DEBUG*/ var_dump($return);
                        echo "<br>";
                    }

                    if(!empty($return["delay"])) {
                        $this->logger->log('Delay execution until '.$return["delay"]);
                        $this->logger->finishBlock('');

                        Queue::addEntry(
                            $task,
                            $this->_user,
                            $this->_context,
                            $return["checkmode"],
                            $return["delay"],
                            (!empty($return["locked"])?1:0),
                            !empty($return["field"])?$return["field"]:false,
                            (!empty($return["hidden"])?true:false)
                        );
                        continue;
                    }

                    if($this->_runCounter < 500000) {
                        $this->logger->finishBlock($return);
                        $nextTasks = $task->getNextTasks($return);
                        $nextTasks = array_reverse($nextTasks);

                        foreach($nextTasks as $nextTask) {
                            $this->pushExecutionStack($nextTask);
                        }
                    }
                } else {
                    $this->logger->log('Task return false to prevent continue current path.');
                    $this->logger->finishBlock('');
                }
            }

            $sql = "SELECT id FROM vtiger_wf_queue WHERE execID = '".$this->_execID."'";
            $result = $adb->query($sql, true);

            if($adb->num_rows($result) == 0) {
                $this->_context->unlinkTempFiles($this->_execID);
            }

            $this->_context->save();

            restore_error_handler();

        }

    }

