<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_OAuthHandler_Action extends Settings_Vtiger_Basic_Action {

    public function __construct() {
        parent::__construct();

        $this->exposeMethod('GetAuthUrl');
        $this->exposeMethod('CheckStatus');
    }

    public function CheckStatus($request) {
        echo \Workflow\OAuth::isDone($request->get('oauth_key'))?'true':'false';
        exit();
    }

    public function GetAuthUrl($request) {
        $OAuthKey = $request->get('oauth_key');

        $obj = new \Workflow\OAuth($OAuthKey);
        $url = $obj->getAuthorizationUrl();

        $result = array(
            'url' => $url,
        );

        return $result;
    }
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $result = array();

        $mode = $request->get('mode');
        if(!empty($mode)) {
            $result = $this->invokeExposedMethod($mode, $request);
        }

        echo json_encode($result);
    }
}