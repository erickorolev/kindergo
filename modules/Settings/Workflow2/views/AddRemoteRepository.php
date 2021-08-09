<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_AddRemoteRepository_View extends Settings_Workflow2_Default_View {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        $viewer = $this->getViewer($request);

        $url = base64_decode($request->get('repository_url'));
        if(strpos($url, 'http://') !== false) {
           $viewer->assign('SHOW_HTTP_WARNING', true);
        } else {
            $viewer->assign('SHOW_HTTP_WARNING', false);
        }

        try {
            if(!filter_var($url, FILTER_VALIDATE_URL))
            {
                throw new Exception("URL is not valid");
            }
            $params = array(
                'module' => 'Workflow2',
                'releasepath' =>'stable',
                'mod_version' => 1
            );
            $content = VtUtils::getContentFromUrl($url, $params, 'post');

            try {
                $root = new SimpleXMLElement($content);
            } catch(Exception $exp) {
                throw new Exception('no task repository');
            }

            if(!isset($root['repoversion'])) {
                $version = 1;
            } else {
                $version = (string)$root['repoversion'];
            }
            $viewer->assign('Version', $version);

            if(isset($root->systemkey)) {
                $viewer->assign('SystemKey', (string)$root->systemkey);
            } else {
                $viewer->assign('SystemKey', '');
            }

            if(empty($root->title)) {
                throw new Exception('no task repository (title missing)');
            }
            $needLicense = (string)$root->needLicense == "1";

            $data = array(
                'title' => $root->title,
                'needLicense' => $needLicense,
                'url' => $url
            );

            $viewer->assign('nonce', sha1(vglobal('site_URL').$url.'0s-f,mäp'.$data['title']));

            $viewer->assign('data', $data);

            $viewer->view('AddRemoteRepository.tpl', $request->getModule(false));

            //$response->setResult(array("success" => true, 'title' => (string)$root->title, 'license' => $needLicense));
        } catch(Exception $exp) {
            echo 'no valid Repository: '.$exp->getMessage();
        }


    }


}