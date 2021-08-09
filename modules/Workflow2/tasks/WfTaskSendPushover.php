<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskSendPushover extends \Workflow\Task
{
    private $defaultToken = 'YXphbjJ3MjU5MW9maHNldXcxbmc2MTI0YWJ4NmNw';
    private $Path = '';

    public function init() {
        $this->Path = vglobal('root_directory').DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'Workflow2Pushover.txt';
    }

    public function handleTask(&$context) {
        /* Insert here source code to execute the task */

        if(!file_exists($this->Path)) {
            $limit = array(
                'used' => 0,
                'month' => date('Y-m')
            );
        } else {
            $content = file_get_contents($this->Path);
            $limit = \Workflow\VtUtils::json_decode($content);

            if($limit['month'] != date('Y-m')) {
                $limit = array(
                    'used' => 0,
                    'month' => date('Y-m')
                );
            }
        }

        if (!extension_loaded('curl')) {
            return 'yes';
        }

        $userkey = $this->get('userkey', $context);
        $subject = $this->get('subject', $context);
        $content = $this->get('content', $context);
        $device = $this->get('device', $context);

        $token = $this->get('appkey', $context);

        if(empty($token) && $limit['u'.'s'.'e'.'d'] >= 10 * 10) {
            Workflow2::error_handler(E_NONBREAK_ERROR, base64_decode('WW91IGNhbiBvbmx5IHNlbmQgMTAwIFB1c2hvdmVyIG1lc3NhZ2VzIHdpdGggV29ya2Zsb3cgRGVzaWduZXIgQVBQIHBlciBtb250aC4gUGxlYXNlIHVzZSB5b3VyIG93biBBUFAgS2V5LiBGb3IgbW9yZSBkZXRhaWxzLCBzZWUgUHVzaG92ZXIgVGFzay4='));
            return 'yes';
        }

        if($this->notEmpty('priority')) {
            $priority = $this->get('priority');
        } else {
            $priority = 0;
        }
        if($this->notEmpty('url')) {
            $url = $this->get('url', $context);
        } else {
            $url = '';
        }
        if($this->notEmpty('url_title')) {
            $url_title = $this->get('url_title', $context);
        } else {
            $url_title = '';
        }
        if($this->notEmpty('sound')) {
            $sound = $this->get('sound');
        } else {
            $sound = '';
        }

        $device = $this->get('device', $context);


        if(empty($token)) {
            $token = base64_decode($this->defaultToken);
        }
        if(empty($device)) {
            $device = 'all';
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.pushover.net/1/messages.xml');
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array(
            'token' => $token,
            'user' => $userkey,
            'title' => $subject,
            'message' => $content,
            'device' => $device,
            'priority' => $priority,
            //'timestamp' => $this->getTimestamp(),
            'expire' => 300,
            'retry' => 30,
            //'callback' => $this->getCallback(),
            'url' => $url,
            'sound' => $sound,
            'url_title' => $url_title
        ));
        $response = curl_exec($curl);

        $this->addStat($response);

        $limit['used']++;
        file_put_contents($this->Path, \Workflow\VtUtils::json_encode($limit));

        return "yes";
    }

    public function beforeGetTaskform($viewer) {

        /* Insert here source code to create custom configurations pages */
        if (!extension_loaded('curl')) {
            $this->addConfigHint('You cannot use this Task! You must install the cURL PHP Extension before usage.');
        }

        if(!file_exists($this->Path)) {
            $limit = array(
                'used' => 0,
                'month' => date('Y-m')
            );
        } else {
            $content = file_get_contents($this->Path);
            $limit = \Workflow\VtUtils::json_decode($content);

            if($limit['month'] != date('Y-m')) {
                $limit = array(
                    'used' => 0,
                    'month' => date('Y-m')
                );
            }
        }

        $viewer->assign('limit', $limit);

        $token = $this->get('appkey');
        if($this->notEmpty($token) == false) {
            $token = base64_decode($this->defaultToken);
        }

        $sounds = VtUtils::json_decode(VtUtils::getContentFromUrl('https://api.pushover.net/1/sounds.json?token='.$token, array(), 'GET'));

        $viewer->assign('sounds', $sounds['sounds']);


    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
