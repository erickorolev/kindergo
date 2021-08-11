<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 06.09.13
 * Time: 17:29
 */
namespace Workflow;

class Importer {
    protected $_hash = '';
    /**
     * @var $_obj Importer\Base
     */
    protected $_obj = null;
    public static $INSTANCE = array();
//    protected static $Filehandler = array();

    public static function getInstance($hash) {
        if(!isset(self::$INSTANCE[$hash])) {
            self::$INSTANCE[$hash] = new Importer($hash);
        }

        return self::$INSTANCE[$hash];
    }

    /**
     * @return Importer
     */
    public static function create() {
        $hash = sha1(microtime(true).mt_rand(1000000, 9999999));

        $_SESSION['_import_data'][$hash] = array(
            'file'      => '',
            'mode'      => 'csv',
            'importParams' => array(
                'delimiter' => ',',
                'encoding' => 'UTF-8',
                'skipfirst' => 0,
            ),
            'seek'      => 0,
            'lastPause' => 0,
        );

        $return = new Importer($hash);

//        var_dump($hash, $_SESSION['_import_data']);

        return $return;
    }

/*    public static function create($file, $mode, $importParams) {
        $md5 = md5($file.microtime(true));

        $_SESSION['_import_data'][$md5] = array(
            'file'      => $file,
            'mode'      => $mode,
            'importParams' => $importParams,
            'seek'      => 0,
            'lastPause' => 0,
        );

        $return = new Importer($md5);

        $_SESSION['_import_data'][$md5]['total'] = $return->getTotalRows();

        return $return;
    }*/

    public function setFile($filepath) {
        $_SESSION['_import_data'][$this->_hash]['file'] = $filepath;

//        var_dump($this->_hash, $_SESSION['_import_data']);
    }

    public function exportData() {
        return $_SESSION['_import_data'][$this->_hash];
    }

    public function refreshTotalRows() {
        $_SESSION['_import_data'][$this->_hash]['total'] = $this->getTotalRows(true);
    }

    public function handleFinish() {
        $this->set('ready', true);

        $result = array(
            'ready' => true,
            //'text' => sprintf(vtranslate('Import process done', 'Settings:Workflow2'), $this->get('seek'), $this->getTotalRows())
        );

        //@unlink($this->get('file'));
        if(!wfIsCli()) {
            echo json_encode($result);
            exit();
        } else {
            return;
        }
    }
    public function handlePause() {
        $result = array(
            'currentPos' => $this->get('seek'),
            'total' => $this->getTotalRows(),
            'ready' => false,
            'text' => sprintf(vtranslate('%s or %s rows done in Import process', 'Settings:Workflow2'), $this->get('seek'), $this->getTotalRows())
        );

        if(!wfIsCli()) {
            echo json_encode($result);
            exit();
        } else {
            return;
        }
    }

    public function getHash() {
        return $this->_hash;
    }

    /**
     * @param $key string
     * @return mixes
     */
    public function get($key) {
        if(isset($_SESSION['_import_data'][$this->_hash]) && isset($_SESSION['_import_data'][$this->_hash][$key])) {
            return $_SESSION['_import_data'][$this->_hash][$key];
        }

        return false;
    }

    /**
     * @return Main
     */
    public function getWorkflow() {
        global $current_user;
        $importParams = $this->get('importParams');

        $workflowId = $importParams['workflowid'];

        $context = \Workflow\VTEntity::getDummy();

        $objWorkflow = new \Workflow\Main($workflowId, false, $current_user);

        $environment = array('_import_hash' => $this->_hash, '_internal' => $_SESSION['_import_data'][$this->_hash]);
        $environment['importParams'] = $this->get('importParams');

        $context->loadEnvironment($environment);

        $objWorkflow->setContext($context);

        return $objWorkflow;

    }

    public function set($key, $value) {
        $_SESSION['_import_data'][$this->_hash][$key] = $value;
    }

    public function __construct($hash) {
        $this->_hash = $hash;
    }

    /**
     * @return Importer\Base
     */
    public function getHandler() {
        if(null !== $this->_obj) {
            return $this->_obj;
        }

        $mode = ucfirst(strtolower($this->get('mode')));
        $className = '\\Workflow\\Filehandler\\'.$mode;
        $importParams = $this->get('importParams');

        $this->_obj = new $className($this->get('file'), $this->get('seek'), $this->get('importParams'));

        return $this->_obj;
    }

    /**
     * @return int
     */
    public function getTotalRows($refresh = false) {
        $total = $this->get('total');

        if($refresh || empty($total)) {
            $handler = $this->getHandler();
            $return = $handler->getTotalRows();
            $this->set('total', $return);
            return $return;
        }

        return $total;
    }

    public function getNextRow() {
        $handler = $this->getHandler();

        // Increase seek must behind getNextRow, because the first renewal will skip one line

        $return = $handler->getNextRow();

        $seek = $this->get('seek');
//        $this->set('seek', $seek + 1);

        return $return;
    }

    public function resetPosition() {
        $this->set('seek', 0);
        $handler = $this->getHandler();
        $handler->resetPosition();
    }

}

?>