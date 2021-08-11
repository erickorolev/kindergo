<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
//ini_set('display_errors', 1);
error_reporting(E_ALL&~E_NOTICE);
class WfTaskAdd2GoogleCal extends \Workflow\Task
{
    private $additionallyDir = null;

    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;

    public function init() {
        $this->additionallyDir = $directory = vglobal('root_directory').'modules/Workflow2/extends/additionally/googleapi/';

        $this->_SC = $this->addPreset("SimpleConfig", "details", array(
            'templatename' => 'mainconfig',
        ));

        if($this->isConfiguration()) {
            $this->_SC->setColumnCount(1);

            $this->_SC->addField('alldayevent', 'All day Event', 'checkbox');
            //$this->_SC->addRepeatField('attachmenturl', 'Attachment URLs', 'template');
        }
    }

    private function _initGoogle() {
        $tokenFilename = vglobal('root_directory').'modules/Workflow2/extends/additionally/googlecal/token/tokenSession3_'.$this->getBlockId();

        if(file_exists($tokenFilename)) {
            global $currentBlockID;
            $currentBlockID = $this->getBlockId();
            if(!function_exists('google_api_php_client_autoload')) {
                require_once($this->additionallyDir . "/google-api-php-client/autoload.php");
            }

            $callback = 'urn:ietf:wg:oauth:2.0:oob';
            $client = new \Google_Client();
            $client->setAccessType('offline');
            $client->setApplicationName("VtigerCRM_WorkflowDesigner");
            $client->setClientId('165426828888-7982si8gvbtvgid01nf7lkiolt0bs3vt.apps.googleusercontent.com');
            $client->setClientSecret('R1M9RkVUbxc7XGRLUWld376n');
            $client->setRedirectUri($callback);
            $client->setScopes(array('https://www.googleapis.com/auth/calendar'));

            if (!empty($_POST["code"])) {
                $client->authenticate($_POST['code']);
                $googleToken = $client->getAccessToken();

                file_put_contents($tokenFilename, serialize(array('accessToken' => $googleToken)));
                echo '<script type="text/javascript">window.location.href="index.php?module=Workflow2&parent=Settings&view=TaskConfig&taskid=' . $this->getBlockId() . '&done=1";</script>';
                exit();
            }

            if (file_exists($tokenFilename)) {
                $sessionToken = unserialize(file_get_contents($tokenFilename));
            } else {
                $sessionToken = "";
            }

            if (empty($sessionToken['accessToken'])) {
                $sessionToken = null;
            }

            if (empty($sessionToken)) {
                if (function_exists('csrf_get_tokens')) {
                    $csrf = "<input type='hidden' name='" . $GLOBALS['csrf']['input-name'] . "' value='" . csrf_get_tokens() . "' />";
                } else {
                    $csrf = '';
                }

                $authUrl = $client->createAuthUrl();
                echo '<script type="text/javascript">window.open("' . $authUrl . '");</script>';
                echo '<div style="text-align:center;margin:40px 0;">', getTranslatedString('Because of Login Restrictions, you need to do the Login and Authorization within the PopUp and copy the Code you get in this Textfield.', 'Settings:Workflow2');
                echo '<form method="POST" action="#">' . $csrf . '<br/><input type="text" style="width:400px;" name="code"><br/><input type="submit" class="btn  btn-primary" name="submit" value="Submit the Code & Unlock Google Calendar Access" /> </form>';
                exit();
            }

            $client->setAccessToken($sessionToken['accessToken']);
            $this->service = new Google_Service_Calendar($client);
            $this->client = $client;
        }
    }

    public function handleTask(&$context) {
        $entityDataKey = "block_".$this->getBlockId()."_eventid";

        // Setze das Datum und verwende das RFC 3339 Format.
        $startDate = $this->get("eventstartdate", $context);
        $this->addStat('Startdate'.$startDate);

        $startTime = $this->get("eventstarttime", $context);
        $this->addStat('Starttime'.$startTime);

        $parts = explode(':',$startTime);
        if(count($parts) == 3) {
            $startTime = $parts[0].':'.$parts[1];
        }

		if(strlen($startTime) < 5) {
			$startTime = "0".$startTime;
		}

        $tzOffset = "+01";

        if($this->notEmpty('eventenddate')) {

            $endDate = $this->get('eventenddate', $context);
            $endTime = $this->get('eventendtime', $context);

            $parts = explode(':',$endTime);
            if(count($parts) == 3) {
                $endTime = $parts[0].':'.$parts[1];
            }

            if(strlen($endTime) < 5) {
                $endTime = "0".$endTime;
            }

        }

        if(empty($endTime)) {

            $duration = $this->get("eventduration", $context);

            $date = strtotime("+".$duration." minutes", strtotime($startDate." ".$startTime));

            if(empty($endDate)) {
                $endDate = date("Y-m-d", $date);
            }

            $endTime = date("H:i", $date);

        }

        $this->_initGoogle();

        $service = $this->getService();
        if(1==0 && $context->existEntityData($entityDataKey)) {
            $entityId = $context->getEntityData($entityDataKey);

            try {
                $event = $this->getService()->getCalendarEventEntry($entityId);
                $when = $this->getService()->newWhen();
                $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
                $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";

                $event->when = array($when);
                $event->save();

                return "yes";

            } catch (Zend_Gdata_App_Exception $e) {
                $this->addStat("existing Event not found. Create new!");
            }

        }

		$event = new Google_Service_Calendar_Event();
		$event->setSummary($this->get("eventtitle", $context));

        if($this->notEmpty('location')) {
            $event->setLocation($this->get('location', $context));
        }

		$start = new Google_Service_Calendar_EventDateTime();

        if($this->_SC->has('alldayevent') && $this->_SC->get('alldayevent') == '1') {
            $start->setDate("{$startDate}");
        } else {
            $start->setDateTime("{$startDate}T{$startTime}:00.000{$tzOffset}:00");
        }

		#$start->setTimeZone('America/Los_Angeles');
		$event->setStart($start);
        $event->setDescription($this->get("eventdescr", $context));

		$end = new Google_Service_Calendar_EventDateTime();

        if($this->_SC->has('alldayevent') && $this->_SC->get('alldayevent') == '1') {
            $end->setDate("{$endDate}");
        } else {
            $end->setDateTime("{$endDate}T{$endTime}:00.000{$tzOffset}:00");
        }


		#$end->setTimeZone('America/Los_Angeles');
		$event->setEnd($end);
		$event->setVisibility($this->get("privacy"));
/*
        $attachments = $this->_SC->get('attachmenturl');
        if(!empty($attachments)) {
            $attachmentArray = array();
            foreach($attachments as $url) {
                $attachmentArray[] = array(
                    'fileUrl' => $url,
                );
            }
            $event->setAttachments($attachmentArray);
            var_dump($attachmentArray);
        }
*/
		$event = $this->getService()->events->insert(html_entity_decode($this->get("calendar")), $event, array('supportsAttachments' => true));

        $context->addEntityData($entityDataKey, $event->getId());
		$this->storeAccessKey();

		return "yes";
    }

    public function storeAccessKey() {
        if(empty($this->service)) return;

        $tokenFilename = $this->additionallyDir."token/tokenSession3_".$this->getBlockId();
   		$googleToken = $this->client->getAccessToken();
   		file_put_contents($tokenFilename, serialize(array('accessToken' => $googleToken)));
   	}

    private function getService() {
        if(!empty($this->service)) return $this->service;

        if($this->notEmpty('provider')) {
            /**
             * @var $provider \Workflow\Plugins\ConnectionProvider\GoogleCalendar
             */
            $provider = \Workflow\ConnectionProvider::getConnection($this->get('provider'));

            return $provider->getCalendarService();
        }

        return false;
    }

    public function beforeGetTaskform($viewer) {
        $this->_initGoogle();

        $calenderList = array();

        $service = $this->getService();
        if(!empty($service)) {
            try {
                $listFeed = $service->calendarList->listCalendarList();
            } catch (\Exception $e) {
                echo "Fehler: " . $e->getMessage();
            }

            foreach ($listFeed as $calendar) {
                $calenderList[$calendar->getId()] = $calendar->getSummary();
            }

            $viewer->assign("calendar", $calenderList);
        }

        if(empty($this->service)) {
            $provider = \Workflow\ConnectionProvider::getAvailableConfigurations('googlecalendar');
            $viewer->assign('provider', $provider);
            $viewer->assign('showprovider', true);
        } else {
            $viewer->assign('showprovider', false);
        }

        $privacySettings = array(
            "default" => getTranslatedString("LBL_PRIV_DEFAULT", "Settings:Workflow2"),
            "public" => getTranslatedString("LBL_PRIV_PUBLIC", "Settings:Workflow2"),
            "private" => getTranslatedString("LBL_PRIV_PRIVATE", "Settings:Workflow2"),
        );
        $viewer->assign("privacySettings", $privacySettings);
    }

    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
