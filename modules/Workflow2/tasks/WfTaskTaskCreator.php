<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
require_once('WfTaskCustomCreator.php');

class WfTaskTaskCreator extends WfTaskCustomCreator
{
    protected $_fields = array("subject", "description", "taskstatus", "taskpriority", "date_start", "time_start", "due_date", "sendnotification",  "assigned_user_id");
    protected $_customModule = "Calendar";
    protected $_activityType = 'Task';

    protected $_hiddenValues = array("activitytype" => "Task");

    public function beforeGetTaskform($viewer) {
        global $adb, $vtiger_current_version;

        // I have to respect Users timezone
        $setter = $this->get("setter");

        if(version_compare($vtiger_current_version, '5.3.0', '>=')) {
            if(!empty($setter) && is_array($setter)) {
                foreach($setter as $key => $field) {
                    if(strpos($field["value"], "$") === false && strpos($field["value"], "?") === false && ($field["field"] == "time_start" || $field["field"] == "time_end")) {
                        $date = DateTimeField::convertToUserTimeZone(date("Y-m-d")." ".$field["value"]);
                        $setter[$key]["value"] = $date->format("H:i");
                    }
                }
            }
        }
        $this->set("setter", $setter);

        parent::beforeGetTaskform($viewer);
    }

    public function beforeSave(&$values) {
        global $adb, $vtiger_current_version;

        if(version_compare($vtiger_current_version, '5.3.0', '>=')) {
            foreach($values["setter"] as $key => $field) {
                // I have to respect Users timezone
                if(strpos($field["value"], "$") === false && strpos($field["value"], "?") === false && ($field["field"] == "time_start" || $field["field"] == "time_end")) {
                    $date = DateTimeField::convertToDBTimeZone(date("Y-m-d")." ".$field["value"]);
                    $values["setter"][$key]["value"] = date("H:i", $date->format('U'));
                }
            }
        }

    }
}
