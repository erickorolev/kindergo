<?php
/**
This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

It belongs to the Workflow Designer and must not be distributed without complete extension
 **/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMysqlquery extends \Workflow\Task {

    /***
     * @param $context \Workflow\VTEntity
     * @return string
     */
    public function handleTask(&$context) {
        if($this->get('provider') == -1) {
            $this->set('provider', 'vtigerdb');
        }
        $connection = \Workflow\ConnectionProvider::getConnection($this->get('provider'));
        /**
         * @var $db \PDO
         */
        $db = $connection->getMySQLConnection();

        if($this->get('provider') == 'vtigerdb') {
            global $dbconfig;
            try {
                $db->query("USE `" . $dbconfig['db_name'] . '`;');
            } catch (\Exception $exp) {}
        }

        if(defined("WF_DEMO_MODE") && constant("WF_DEMO_MODE") == true) {
            return "yes";
        }

        $query = $this->get("query");
        $envVar = $this->get("envvar", $context);

        $query = preg_replace('/%([A-Za-z0-9-_]+)%/', '%--\$--$1--\$--%', $query);
        $query = \Workflow\VTTemplate::parse($query, $context);
        $query = preg_replace('/%--\$--([A-Za-z0-9-_]+)--\$--%/', '%$1%', $query);

        if(empty($envVar)) {
            Workflow2::error_handler(E_NONBREAK_ERROR, "You must configure an environment variable in this block to get the correct result.");
        }

        $resultMode = $this->get('resultmode');
        if(empty($resultMode) || $resultMode == -1) {
            $resultMode = 'single';
        }

        $this->addStat($query);
        try {
            $result = $db->query($query);

            if(!empty($envVar)) {
                if($result->rowCount() > 0) {
                    try {
                        if($resultMode == 'single') {
                            $row = $result->fetch(\PDO::FETCH_ASSOC);
                        } elseif($resultMode == 'multi') {
                            $row = $result->fetchAll(\PDO::FETCH_ASSOC);
                        }
                    } catch(\Exception $exp) {
                        $row = array();
                    }

                    $context->setEnvironment($envVar, $row);
                }
            }
        }
        catch (\Exception $exp) {
            \Workflow2::error_handler($exp);
            //throw new \Exception('MySQL Error: '.$exp->getMessage());
        }
        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        if(defined("WF_DEMO_MODE") && constant("WF_DEMO_MODE") == true) {
            echo "<p style='text-align:center;margin:0;padding:5px 0;background-color:#fbcb09;font-weight:bold;'>This Task won't work on demo.stefanwarnat.de</p>";
        }

        $provider = \Workflow\ConnectionProvider::getAvailableConfigurations('mysql');

        $viewer->assign('available_providers', $provider);
    }

    public function beforeSave(&$data) {

        //echo "<pre>";var_dump($data);echo "</pre>";
//        echo "RESULT\n";var_dump($data);echo "</pre>";
    }



}