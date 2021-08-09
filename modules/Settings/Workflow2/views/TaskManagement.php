<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskManagement_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
   		return true;
   	}
	public function process(Vtiger_Request $request) {
		global $current_user;
        global $root_directory;
        $adb = PearDatabase::getInstance();

        $moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

        /**
         * @var $settingsModel Settings_Colorizer_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $sql = 'SELECT
                    vtiger_wf_repository.title as repo_title,
                    vtiger_wf_repository.messages as repo_messages
                FROM vtiger_wf_repository
                WHERE deleted = 0
                ORDER BY ID';
        $result = $adb->query($sql, true);

        $messages = array();
        while($row = $adb->fetchByAssoc($result)) {
            $tmp = unserialize(html_entity_decode($row['repo_messages']));

            foreach($tmp as $msg) {
                $messages[] = array($msg[0], $row['repo_title'], $msg[1]);
            }
        }

        $viewer->assign('messages', $messages);

        $sql = 'SELECT
                    vtiger_wf_types.*,
                    vtiger_wf_repository_types.id as type_id,
                    vtiger_wf_repository_types.repos_id AS repos_id,
                    vtiger_wf_repository_types.name,
                    vtiger_wf_repository_types.mode,
                    vtiger_wf_repository_types.status,
                    vtiger_wf_repository_types.module_required,
                    vtiger_wf_repository_types.min_version,
                    vtiger_wf_repository_types.version as latest_version,
                    vtiger_wf_repository_types.last_update as last_update,
                    vtiger_wf_repository.url as repo_url,
                    vtiger_wf_repository.title as repo_title,
                    vtiger_wf_repository.title as repo_messages,
                    vtiger_wf_repository_core.version as installed_core_version
                FROM vtiger_wf_repository_types
                    LEFT JOIN vtiger_wf_repository ON (vtiger_wf_repository.id = vtiger_wf_repository_types.repos_id)
                    LEFT JOIN vtiger_wf_types ON (
                        vtiger_wf_repository_types.repos_id = vtiger_wf_types.repo_id AND
                        vtiger_wf_repository_types.name = vtiger_wf_types.type
                    )
                    LEFT JOIN vtiger_wf_repository_core ON (
                        vtiger_wf_repository_types.name = vtiger_wf_repository_core.type
                    )
                WHERE vtiger_wf_repository.title IS NOT NULL
                ORDER BY vtiger_wf_types.ID';
        $result = $adb->query($sql, true);

        $blocks = array();

        while($row = $adb->fetchByAssoc($result)) {
            if($row['repo_id'] == '0' && strpos($row['repo_url'], 'repository.stefanwarnat.de') === false) {
                continue;
            }
            if($row['repo_id'] == '0') {
                $row['repo_title'] = 'Internal';
            }

            $row['last_update'] = DateTimeField::convertToUserFormat($row['last_update']);

            $text = getTranslatedString($row['text'], $row['module']);

            $prevent = false;
            if($row['min_version'] > $moduleModel->version) {
                $prevent = 'Require Workflow Designer Version '.$row['min_version'];
            }

            if(!empty($row['module_required'])) {
                $parts = explode(',', $row['module_required']);
                foreach($parts as $part) {
                    if(!vtlib_isModuleActive($part)) {
                        $prevent = 'Additional module <em>'.$part.'</em> required (<a target="_blank" href="https://repository.stefanwarnat.de/required.php?'.$part.'">Link with information about this module</a>)';
                        break;
                    }
                }
            }

            $blocks[$row['repo_title']]["repo_id"] = $row['repos_id'];
			$blocks[$row['repo_title']][$row['mode']][] = array(
                'type_id'        => $row['id'],
                'id'             => $row['type_id'],
                'repo_id'        => $row['repos_id'],
                'text'           => empty($text)?$row['name']:$text,
                'version'        => $row['mode']=='task'?$row['version']:$row['installed_core_version'],
                'latest_version' => empty($row['latest_version'])?'-':$row['latest_version'],
                'status'         => $row['status'],
                'last_update'    => $row['last_update'],

                'prevent'        => $prevent,
            );
        }

        $sql = 'SELECT
                    vtiger_wf_types.*
                FROM vtiger_wf_types
                WHERE repo_id = 0
                ORDER BY vtiger_wf_types.ID';
        $result = $adb->query($sql, true);

        while($row = $adb->fetchByAssoc($result)) {
            if($row['repo_id'] == '0') {
                $row['repo_title'] = 'Internal';
            }

            $text = getTranslatedString($row['text'], $row['module']);

            $prevent = false;
            $blocks['without Repository']['no_update'] = true;
            $blocks['without Repository']['task'][] = array(
                'type_id'        => $row['id'],
                'id'             => $row['id'],
                'repo_id'        => $row['repos_id'],				
                'text'           => $text,
                'version'        => $row['version'],
                'latest_version' => '',
                'last_update'    => '',
                'prevent'        => ''
            );
        }

        $viewer->assign('blocks', $blocks);

        if(isset($_REQUEST['export']) && $_REQUEST['export'] == '1') {
            $viewer->assign('download', true);
        } else {
            $viewer->assign('download', false);
        }


        $viewer->view('VT7/TaskManagement.tpl', $qualifiedModuleName);

	}


	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.views.resources.js.Essentials",
			"modules.Settings.$moduleName.views.resources.Workflow2",
            "modules.Settings.$moduleName.views.resources.TaskManager",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        foreach($headerScriptInstances as $obj) {
            $src = $obj->get('src');
            if(!empty($src) && strpos($src, $moduleName) !== false) {
                $obj->set('src', $src.'?v='.$moduleModel->version);
            }
        }

        return $headerScriptInstances;
	}
    function getHeaderCss(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = array(
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/css/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}