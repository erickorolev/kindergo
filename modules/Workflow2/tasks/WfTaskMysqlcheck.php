<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready */
class WfTaskMysqlcheck extends \Workflow\Task {

    public function handleTask(&$context) {
        $adb = PearDatabase::getInstance();

        if(defined("WF_DEMO_MODE") && constant("WF_DEMO_MODE") == true) {
            return "yes";
        }

        $query = $this->get("query");
        $numRows = $this->get("numrows", $context);

        if($query == -1) {
            return 'no';
        }

        $query = preg_replace('/%([A-Za-z0-9-_]+)%/', '%--\$--$1--\$--%', $query);
        $query = \Workflow\VTTemplate::parse($query, $context);
        $query = preg_replace('/%--\$--([A-Za-z0-9-_]+)--\$--%/', '%$1%', $query);

        $oldDieOnError = $adb->dieOnError;

        $adb->dieOnError = false;
        $result = $adb->query($query, false);

        $this->addStat('MySQL Query: '.$query);
        $this->addStat('numRows: '.$adb->num_rows($result));
        $this->addStat('need: '.$numRows);



        $adb->dieOnError = $oldDieOnError;

        if($adb->num_rows($result) == $numRows) {
            return "yes";
        }

        return "no";
    }


    public function beforeGetTaskform($viewer) {
        if(defined("WF_DEMO_MODE") && constant("WF_DEMO_MODE") == true) {
            echo "<p style='text-align:center;position:absolute;bottom:0;width:100%;margin:0;padding:5px 0;background-color:#fbcb09;font-weight:bold;'>This sendmail Task won't work in this DEMO.</p>";
        }
    }

    public function beforeSave(&$data) {

        //echo "<pre>";var_dump($data);echo "</pre>";
//        echo "RESULT\n";var_dump($data);echo "</pre>";
    }



}