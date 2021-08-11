<?php

/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 25.05.2016
 * Time: 08:29
 */
namespace Workflow\CommunicationProvider;

use Workflow\VtUtils;

class ClickATell extends \Workflow\CommunicationPlugin
{
    protected $usernameLabel = 'Account SID';
    protected $passwordLabel = 'Auth Token';

    protected $name = 'Clickatell';

    protected $supported = array(
        'sms' => true
    );

    public function utf8_to_gsm0338($string)
    {
        $dict = array(
            '@' => "\x00", '£' => "\x01", '$' => "\x02", '¥' => "\x03", 'è' => "\x04", 'é' => "\x05", 'ù' => "\x06", 'ì' => "\x07", 'ò' => "\x08", 'Ç' => "\x09", 'Ø' => "\x0B", 'ø' => "\x0C", 'Å' => "\x0E", 'å' => "\x0F",
            'Δ' => "\x10", '_' => "\x11", 'Φ' => "\x12", 'Γ' => "\x13", 'Λ' => "\x14", 'Ω' => "\x15", 'Π' => "\x16", 'Ψ' => "\x17", 'Σ' => "\x18", 'Θ' => "\x19", 'Ξ' => "\x1A", 'Æ' => "\x1C", 'æ' => "\x1D", 'ß' => "\x1E", 'É' => "\x1F",
            // all \x2? removed
            // all \x3? removed
            // all \x4? removed
            'Ä' => "\x5B", 'Ö' => "\x5C", 'Ñ' => "\x5D", 'Ü' => "\x5E", '§' => "\x5F",
            '¿' => "\x60",
            'ä' => "\x7B", 'ö' => "\x7C", 'ñ' => "\x7D", 'ü' => "\x7E", 'à' => "\x7F",
            '^' => "\x1B\x14", '{' => "\x1B\x28", '}' => "\x1B\x29", '\\' => "\x1B\x2F", '[' => "\x1B\x3C", '~' => "\x1B\x3D", ']' => "\x1B\x3E", '|' => "\x1B\x40", '€' => "\x1B\x65"
        );

        $converted = strtr($string, $dict);

        // Replace unconverted UTF-8 chars from codepages U+0080-U+07FF, U+0080-U+FFFF and U+010000-U+10FFFF with a single ?
        return preg_replace('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m','?',$converted);
    }
    public function SMS($data) {
        $sid = $this->get('username');
        $token = $this->get('password');

        if(empty($data['from'])) {
            $data['from'] = $this->get('default_from');
        }

        if(substr($data['to'], 0, 2) == '00') {
            $data['to'] = '+'.ltrim($data['to'], '0');
        }

        if(substr($data['from'], 0, 2) == '00') {
            $data['from'] = '+'.ltrim($data['from'], '0');
        }

        $parameter = array(
            'to' => array($data['to']),
           // 'unicode' => 1,
            'text' => $this->utf8_to_gsm0338($data['content'])
        );
        var_dump($parameter);exit();

        $response = VtUtils::getContentFromUrl('https://api.clickatell.com/rest/message', \Workflow\VTUtils::json_encode($parameter), 'post', array('headers' => array('X-Version: 1', 'Content-Type: application/json'), 'successcode' => array(200, 202, 207), 'auth' => array('bearer' => $this->get('token'))));

        return $response;
    }

    public function test() {
        $sid = $this->get('username');
        $token = $this->get('password');

        $response = VtUtils::getContentFromUrl('https://api.clickatell.com/rest/account/balance', array(), 'get', array('headers' => array('X-Version: 1'), 'successcode' => array(200, 202, 207), 'auth' => array('bearer' => $this->get('token'))));

        if(empty($response)) {
            throw new \Exception('Login credentials could not be verified. Please check if you are using Account SID and no single API Key');
        }

    }

    public function getConfigFields()
    {
        return array(
            'token' => array(
                'label' => 'Auth Token',
                'type' => 'text',
            ),
            'default_from' => array(
                'label' => 'Default Sender',
                'type' => 'text',
            ),
        );
    }

    public function getDataFields($method) {
        $return = parent::getDataFields($method);

        $return['from']['placeholder'] = $this->get('default_from');

        return $return;
    }

}

//\Workflow\CommunicationPlugin::register('clickatell', '\\Workflow\\CommunicationProvider\\ClickATell');