<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

require_once('WfTaskCustomCreator.php');
/* vt6 ready */
class WfTaskEventCreator extends WfTaskCustomCreator
{
    protected $_fields = array("subject", "description", "eventstatus", "activitytype", "date_start", "due_date", "time_start", "time_end", "sendnotification",  "assigned_user_id", 'visibility');
    protected $_customModule = "Events";
    protected $_activityType = 'Event';

    protected $_hiddenValues = array("duration_hours" => "0");

    public function init() {
        parent::init();
        $this->_javascriptFile[] = 'WfTaskEventcreator.js';
    }
    public function beforeGetTaskform($viewer) {
        global $adb, $vtiger_current_version;

        if(version_compare($vtiger_current_version, '5.3.0', '>=')) {
            // I have to respect Users timezone
            $setter = $this->get("setter");

            if(!empty($setter) && is_array($setter)) {
                foreach($setter as $key => $field) {
                    if(strpos($field["value"], "$") === false && strpos($field["value"], "?") === false && ($field["field"] == "time_start" || $field["field"] == "time_end")) {
                        $date = DateTimeField::convertToUserTimeZone(date("Y-m-d")." ".$field["value"]);
                        $setter[$key]["value"] = $date->format("H:i");
                    }
                }
            }

            $this->set("setter", $setter);
        }

        parent::beforeGetTaskform($viewer);
    }

    public function beforeSave(&$values) {
        global $adb, $vtiger_current_version;

        if(version_compare($vtiger_current_version, '5.3.0', '>=')) {
            foreach($values["setter"] as $key => $field) {

    //             I have to respect Users timezone
                if(strpos($field["value"], "$") === false && strpos($field["value"], "?") === false && ($field["field"] == "time_start" || $field["field"] == "time_end")) {
                    $date = DateTimeField::convertToDBTimeZone(date("Y-m-d")." ".$field["value"]);
                    $values["setter"][$key]["value"] = date("H:i", $date->format('U'));
                }
            }
        }

        parent::beforeSave($values);
    }

    public function handleTask(&$context) {
        $setter = $this->get("setter");
        $this->set("new_module", $this->_customModule);

        $reminderTime = null;

        if($setter != -1 && is_array($setter)) {
            foreach($setter as $field) {
                if($field['field'] == 'reminder_time') {
                    $reminderTime = $field['value'];
                    break;
                }
            }
        }

        if(!empty($reminderTime)) {
            $this->_hiddenValues['set_reminder'] = 'Yes';

            $reminder = $reminderTime;

            $minutes = (int)($reminder)%60;
            $hours = (int)($reminder/(60))%24;
            $days =  (int)($reminder/(60*24));

            //at vtiger there cant be 0 minutes reminder so we are setting to 1
            if($minutes == 0){
                $minutes = 1;
            }

            $this->_hiddenValues['remmin'] = $minutes;
            $this->_hiddenValues['remhrs'] = $hours;
            $this->_hiddenValues['remdays'] = $days;

        }

        parent::handleTask($context);

        if(!empty($this->_newObj)) {
            $startDate = $this->_newObj->get('date_start');

            // Repeat Function - Copyright by VtigerCRM Developers from Internal Workflow Module
            if($this->notEmpty('recurring') && !empty($startDate)) {
                $repeatConfig = $this->get('repeat');
                $objTemplate = new \Workflow\VTTemplate($context);
                $repeatConfig =  $objTemplate->render($repeatConfig);


                if(!empty($repeatConfig['calendar_repeat_limit_date'])) {
                    $resultRow = array();

                    $resultRow['date_start'] = $startDate;
                    $resultRow['time_start'] = $this->_newObj->get('time_start');
                    $resultRow['due_date'] = $repeatConfig['calendar_repeat_limit_date'];
                    $resultRow['time_end'] = $this->_newObj->get('time_end');
                    $resultRow['recurringtype'] = $repeatConfig['recurring_type'];
                    $resultRow['recurringfreq'] = $repeatConfig['repeat_frequency'];

                    $daysOfWeekToRepeat = array();
                    if (!empty($repeatConfig['sun_flag'])) {
                        $daysOfWeekToRepeat[] = 0;
                    }
                    if (!empty($repeatConfig['mon_flag'])) {
                        $daysOfWeekToRepeat[] = 1;
                    }
                    if (!empty($repeatConfig['tue_flag'])) {
                        $daysOfWeekToRepeat[] = 2;
                    }
                    if (!empty($repeatConfig['wed_flag'])) {
                        $daysOfWeekToRepeat[] = 3;
                    }
                    if (!empty($repeatConfig['thu_flag'])) {
                        $daysOfWeekToRepeat[] = 4;
                    }
                    if (!empty($repeatConfig['fri_flag'])) {
                        $daysOfWeekToRepeat[] = 5;
                    }
                    if (!empty($repeatConfig['sat_flag'])) {
                        $daysOfWeekToRepeat[] = 6;
                    }

                    $recurringInfo = '';
                    if ($repeatConfig['recurring_type'] == 'Daily' || $repeatConfig['recurring_type'] == 'Yearly') {
                        $recurringInfo = $repeatConfig['recurring_type'];
                    } elseif ($repeatConfig['recurring_type'] == 'Weekly') {
                        if (!empty($daysOfWeekToRepeat)) {
                            $recurringInfo = $repeatConfig['recurring_type'] . '::' . implode('::', $daysOfWeekToRepeat);
                        } else {
                            $recurringInfo = $repeatConfig['recurring_type'];
                        }
                    } elseif ($repeatConfig['recurring_type'] == 'Monthly') {
                        $recurringInfo = $repeatConfig['recurring_type'] . '::' . $repeatConfig['repeatMonth'];
                        if ($repeatConfig['repeatMonth'] == 'date') {
                            $recurringInfo = $recurringInfo . '::' . $repeatConfig['repeatMonth_date'];
                        } else {
                            $recurringInfo = $recurringInfo . '::' . $repeatConfig['repeatMonth_daytype'] . '::' . $repeatConfig['repeatMonth_day'];
                        }
                    }
                    $resultRow['recurringinfo'] = $recurringInfo;

                    // Added this to relate these events to parent module.
                   /* $_REQUEST['createmode'] = 'link';
                    $_REQUEST['return_module'] = $this->_newObj->getModuleName();
                    $_REQUEST['return_id'] = $this->_newObj->getId();
*/
                    $recurObj = RecurringType::fromDBRequest($resultRow);

                    include_once 'modules/Calendar/RepeatEvents.php';
                    Calendar_RepeatEvents::repeat($this->_newObj->getInternalObject(), $recurObj);
                }
            }
        }

        return 'yes';
    }
}
