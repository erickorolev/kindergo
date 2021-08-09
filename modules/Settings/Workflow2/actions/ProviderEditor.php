<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ProviderEditor_Action extends Settings_Vtiger_Basic_Action
{

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);

        $type = $request->get('type');
        $id = $request->get('id');
        $availableTypes = \Workflow\ConnectionProvider::getAvailableProviders();

        $workflowObj = new Workflow2();

        $subProvider = false;
        if (strpos($type, '//') !== false) {
            $parts = explode('//', $type);
            $type = $parts[0];
            $subProvider = $parts[1];
        }

        if (!isset($availableTypes[$type])) {
            die('not possible');
        }

        $createMode = false;
        if (empty($id)) {
            $id = false;

            $provider = \Workflow\ConnectionProvider::getProvider($type);

            if ($provider->requireOAuth() == true) {
                $oauthhash = \Workflow\OAuth::init(get_class($provider) . '::OAuthCallback', 'connectionprovider_' . $type);
            }
            $createMode = true;
        } else {
            $provider = \Workflow\ConnectionProvider::getConnection($id);
            $oauthhash = $provider->get('oauth_key');
        }

        if ($subProvider !== false) {
            $provider->setSubProvider($subProvider);
        }

        $configFields = $provider->getConfigFields();

        $viewer->assign('requireOAuth', $provider->requireOAuth());

        $configFieldHTML = '';
        foreach ($configFields as $key => $config) {
            if ($config['type'] == 'test_button') {
                $configFieldHTML.= '<tr>';
                $configFieldHTML.='<td colspan="2"></td>';
                $configFieldHTML.='</tr>';
                continue;
            }
            $configFieldHTML.= '<tr>';
            $configFieldHTML.= '<td>' . $config['label'] . '</td>';
            $configFieldHTML.= '<td>';
            $config['name'] = 'settings[' . $key . ']';

            $viewer->assign('value', $provider->get($key));
            $viewer->assign('config', $config);

            $configFieldHTML.=$viewer->view('VT7/ConfigGenerator.tpl', 'Settings:Workflow2', true);

            if (!empty($config['description'])) {
                $configFieldHTML.= '<br/><em>' . $config['description'] . '</em>';
            }
            $configFieldHTML.= '</td>';
            $configFieldHTML.= '</tr>';
        }

        if ($provider->requireOAuth() == true) {
            $viewer->assign('OAUTHConfig', array(
                'type' => 'oauth',
                'name' => 'settings[oauth_key]',
                'oauth_key' => $oauthhash
            ));
        }

        $viewer->assign('providerTitle', $provider->getTitle());
        $viewer->assign('connectionId', intval($id));
        $viewer->assign('createMode', $createMode);
        $viewer->assign('connectionTitle', $provider->get('title'));
        $viewer->assign('configFieldHTML', $configFieldHTML);
        $viewer->assign('connectionType',$type . (!empty($subProvider) ? '//' . $subProvider : ''));
        echo $viewer->view('VT7/ProviderEditor.tpl', 'Settings:Workflow2', true);
    }
}