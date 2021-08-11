<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Workflow2_Module_Model extends Vtiger_Module_Model{

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);
		return $links;
	}

    public function getModuleBasicLinks() {

    }
    /**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
        $settingsLinks = array();

        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('Language Downloader', 'Settings:Workflow2'),
                'linkurl' => 'index.php?module=Workflow2&view=LanguageManager&parent=Settings',
        );

        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('Remove the module', 'Settings:Workflow2'),
                'linkurl' => 'index.php?module=Workflow2&view=Uninstaller&parent=Settings',
        );

        return $settingsLinks;
        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => getTranslatedString('LBL_TASK_MANAGEMENT',"Settings:Workflow2"),
                'linkurl' => 'index.php?module=Workflow2&view=TaskManagement&parent=Settings',
                'linkicon' => 'themes/images/set-IcoTwoTabConfig.gif'
        );
        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => getTranslatedString('LBL_SETTINGS_LOGGING',"Settings:Workflow2"),
                'linkurl' => 'index.php?module=Workflow2&action=settingsLogging&parenttab=Settings',
                'linkicon' => 'themes/images/set-IcoTwoTabConfig.gif'
        );
        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => getTranslatedString('LBL_SETTINGS_REMOVE',"Settings:Workflow2"),
                'linkurl' => 'index.php?module=Workflow2&action=settingsRemove&parenttab=Settings',
                'linkicon' => 'themes/images/set-IcoTwoTabConfig.gif'
        );
        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => getTranslatedString('LBL_SETTINGS_TRIGGERMANAGER',"Settings:Workflow2"),
                'linkurl' => 'index.php?module=Workflow2&action=settingsTrigger&parenttab=Settings',
                'linkicon' => 'themes/images/set-IcoTwoTabConfig.gif'
        );
        $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => getTranslatedString('LBL_SETTINGS_HTTPHANDLER',"Settings:Workflow2"),
                'linkurl' => 'index.php?module=Workflow2&view=HttpHandlerManager&parent=Settings',
                'linkicon' => 'themes/images/set-IcoTwoTabConfig.gif'
        );
           /* $settingsLinks[] = array(
                    'linktype' => 'LISTVIEWSETTING',
                    'linklabel' => 'config Relations',
                    'linkurl' => 'index.php?parent=Settings&module=ModComments&view=Relations',
                    'linkicon' => '');*/


		return $settingsLinks;
	}

    public function runTrigger($triggerKey, $crmid, $envValues = array()) {
        global $root_directory;
        require_once($root_directory."/modules/Workflow2/autoload_wf.php");

        $wfManager = new \Workflow\Manager();

        if(!empty($crmid)) {
            $context = \Workflow\VTEntity::getForId($crmid);
            $workflows = $wfManager->GetWorkflows($context->getModuleName(), $triggerKey);
        } else {
            $context = \Workflow\VTEntity::getDummy();
            $workflows = $wfManager->GetWorkflows(false, $triggerKey);
        }

        $user = Users::getActiveAdminUser();
        \Workflow\VTEntity::setUser($user);

        if(!empty($envValues)) {
            $context->loadEnvironment($envValues);
        }

        /**
         * @var $workflows \Workflow\Main[]
         */
        foreach($workflows as $wf) {
            $wf->setContext($context);

            if(!$context->isDummy()) {
                if(!$wf->checkCondition($context)) {
                    continue;
                }

                if(!$wf->checkExecuteCondition($context->getId())) {
                    continue;
                }
            }

            $wf->start();
        }
    }

    public function runWorkflow($workflowId, $crmid, $envValues = array()) {
        global $root_directory;
        require_once($root_directory."/modules/Workflow2/autoload_wf.php");

        $user = Users::getActiveAdminUser();
        \Workflow\VTEntity::setUser($user);

        if(!empty($crmid)) {
            $context = \Workflow\VTEntity::getForId($crmid);
        } else {
            $context = \Workflow\VTEntity::getDummy();
        }

        if(!empty($envValues)) {
            $context->loadEnvironment($envValues);
        }

        $objWorkflow = new \Workflow\Main(intval($workflowId), false, $user);
        $objWorkflow->setContext($context);

        $objWorkflow->start();
    }

    public function refreshFrontendJs() {
        $startTimer = microtime(true);

        $adb = \PearDatabase::getInstance();

        $start = '/** HANDLER START **/';
        $file = 'modules/Workflow2/js/frontend.js';

        $sql = 'SELECT *
                  FROM vtiger_wf_frontendmanager
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id AND active = 1)
                  LEFT JOIN vtiger_wf_frontendtype ON (vtiger_wf_frontendtype.`key` = vtiger_wf_frontendmanager.position)
                WHERE (position IN ("relatedbtn", "morebtn", "listviewbtn") OR vtiger_wf_frontendtype.id IS NOT NULL) AND invisible = 0 ORDER BY `order`';
        $result = $adb->query($sql);

        $frontendConfig = array();
        while($row = $adb->fetchByAssoc($result)) {
            if(!isset($frontendConfig[$row['position']])) {
                $frontendConfig[$row['position']] = array();
            }
            if(!isset($frontendConfig[$row['position']][$row['module_name']])) {
                $frontendConfig[$row['position']][$row['module_name']] = array();
            }

            if(strlen($row['config']) > 3) {
                $row['config'] = \Workflow\VtUtils::json_decode(html_entity_decode($row['config']));
            } else {
                $row['config'] = array();
            }

            if(!empty($row['config']['defaultlayout'])) {
                $row['color'] = '';
            }

            $frontendConfig[$row['position']][$row['module_name']][] = array(
                'workflowid' => $row['workflow_id'],
                'label' => $row['label'],
                'color' => $row['color'],
                'config' => $row['config'],
                'textcolor' => \Workflow\VtUtils::getTextColor($row['color']),
            );
        }

        $jsScript = 'var WFDFrontendConfig = '.json_encode($frontendConfig).';'.PHP_EOL;

        $WFDLanguage = array();
        $WFDLanguage['These Workflow requests some values'] = vtranslate('These Workflow requests some values', 'Settings:Workflow2');
        $WFDLanguage['Execute Workflow'] = vtranslate('Execute Workflow', 'Settings:Workflow2');
        $WFDLanguage['enter values later'] = vtranslate('enter values later', 'Settings:Workflow2');
        $WFDLanguage['stop Workflow'] = vtranslate('stop Workflow', 'Settings:Workflow2');
        $WFDLanguage['Executing Workflow ...'] = vtranslate('Executing Workflow ...', 'Settings:Workflow2');

        $jsScript .= 'var WFDLanguage = '.json_encode($WFDLanguage).';'.PHP_EOL;
        $scripts = \Workflow\FrontendJS::generateScripts();

        $extScript = '';
        if(!empty($scripts['onready']))  {
            $extScript .= '/* Start OnReady */ jQuery(function() { '.$scripts['onready'].' '.PHP_EOL.' }); /* Finish OnReady */'.PHP_EOL;
        }
        if(!empty($scripts['script']))  {
            $extScript .= '/* Start Script */'.$scripts['script'].' /* Finish Script */ '.PHP_EOL;
        }

        if(!empty($scripts['global']))  {
            $jsScript .= '/* Start Global */'.$scripts['global'].' /* Finish Global */ '.PHP_EOL;
        }

        if(!empty($extScript)) {
            $jsScript .= '(function($) { '.PHP_EOL;
            $jsScript .= $extScript.PHP_EOL;
            $jsScript .= '})(jQuery);';
        }

        $jsScript .= '/* Render take '.round(microtime(true) - $startTimer, 2).'s */';
        $content = file_get_contents($file);

        $content = substr($content, 0, strpos($content, $start));

        $content = $content . $start . PHP_EOL . $jsScript;
        file_put_contents($file, $content);

        \Workflow2_Module_Model::updateJSStrings('modules/Workflow2/js/frontend.js');

        $sql = 'UPDATE vtiger_links SET linkurl = "'.$file.'?'.time().'" WHERE linklabel = "Workflow2JS" AND linktype = "HEADERSCRIPT"';
        $adb->query($sql);
    }

    public function updateJSStrings($file, $updateHeaderlinks = true) {
	    $modName = basename(dirname(dirname(__FILE__)));
        $adb = \PearDatabase::getInstance();

	    $filepath = vglobal('root_directory') . $file;

	    $content = file_get_contents($file);
	    $content = str_replace('#/\*\* MODULELANGUAGESTRINGS START \*\*/(.*)/\*\* MODULELANGUAGESTRINGS END \*\*/#', '', $content);

	    $content .= PHP_EOL.PHP_EOL.'/** MODULELANGUAGESTRINGS START **/'.PHP_EOL;

        $languages = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $modName);
        $baseStrings = $languages['jsLanguageStrings'];

        $language = array();
        $sql = 'SELECT language FROM vtiger_users GROUP BY language';
        $result = $adb->pquery($sql);
        while($row = $adb->fetchByAssoc($result)) {
            $strings = \Vtiger_Language_Handler::getModuleStringsFromFile($row['language'], $modName);
            $language[$row['language']] = array_merge($baseStrings, $strings['jsLanguageStrings']);
        }

        $content .= 'if(typeof FLEXMODLANGUAGE == "undefined") var FLEXMODLANGUAGE = {};'.PHP_EOL;
        $content .= 'if(typeof FLEXLANG == "undefined") var FLEXLANG = function(key, module) { var lang = app.getUserLanguage(); if(typeof FLEXMODLANGUAGE[module] != "undefined" && typeof FLEXMODLANGUAGE[module][lang] != "undefined" &&  typeof FLEXMODLANGUAGE[module][lang][key] != "undefined") { return FLEXMODLANGUAGE[module][lang][key]; } return key; };'.PHP_EOL;
        $content .= 'FLEXMODLANGUAGE["'.$modName.'"] = '.json_encode($language).';'.PHP_EOL;
        $content .= '/** MODULELANGUAGESTRINGS END **/';

        file_put_contents($filepath, $content);

        if($updateHeaderlinks === true) {
            $sql = 'UPDATE vtiger_links SET linkurl = "' . $file . '?' . time() . '" WHERE linkurl LIKE "'.$file.'%" AND linktype = "HEADERSCRIPT"';
            $adb->query($sql);
        }

    }

    public function registerFrontendTypes($key, $title, $langModulename, $options) {

    }
}

