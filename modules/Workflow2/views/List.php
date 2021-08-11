<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_List_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
	}

	function process (Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $aid = (int)$request->get('aid');
        $h = $request->get('h');
        $a = $request->get('a');

        if(1==0 && !empty($aid) && !empty($a) && $h == md5($current_user->id."##".$a."##".$aid)) {
        }

        $sql = "SELECT
                    vtiger_wf_confirmation.*,
                    vtiger_wf_confirmation.id as conf_id,
                    vtiger_wf_settings.*,
                    vtiger_wfp_blocks.text as block_title,
                    vtiger_wfp_blocks.settings as block_settings,
                    vtiger_users.user_name,
                    vtiger_users.first_name,
                    vtiger_users.last_name,
                    result_user.user_name as result_user_name,
                    result_user.first_name as result_first_name,
                    result_user.last_name as result_last_name
                FROM
                    vtiger_wf_confirmation_user
                INNER JOIN vtiger_wf_confirmation ON(vtiger_wf_confirmation.id = vtiger_wf_confirmation_user.confirmation_id)
                INNER JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_confirmation.crmid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_confirmation.workflow_id)
                INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_confirmation.blockID)
                INNER JOIN vtiger_wf_queue ON(vtiger_wf_queue.crmid = vtiger_wf_confirmation.crmid AND vtiger_wf_queue.execID = vtiger_wf_confirmation.execID AND vtiger_wf_queue.block_id =vtiger_wf_confirmation.blockID)
                INNER JOIN vtiger_users ON(vtiger_users.id = vtiger_wf_confirmation.from_user_id)
                LEFT JOIN vtiger_users as result_user ON(result_user.id = vtiger_wf_confirmation.result_user_id)
                WHERE
                    user_id = ".$current_user->id." AND vtiger_wf_confirmation.visible = 1
                GROUP BY
                    vtiger_wf_confirmation.id ORDER BY block_title
                ";
        $result = $adb->query($sql, true);

        $workflows = array();
        $buttons = array();

        while($row = $adb->fetchByAssoc($result)) {
            if(!is_array($workflows[$row["title"].' - '.$row["block_title"]])) {
                $workflows[$row["title"].' - '.$row["block_title"]] = array();
            }

            $referenceModule = $row["module"];
            $focus = CRMEntity::getInstance($referenceModule);

            if(empty($noCols[$referenceModule])) {
                $sql = "SELECT * FROM vtiger_field WHERE tabid = ".getTabId($row["module"])." AND uitype = 4";
                $resultTMP = $adb->query($sql);
                if($adb->num_rows($resultTMP) > 0) {
                    $noCols[$referenceModule]["link_no"] = $adb->fetchByAssoc($resultTMP);
                } else {
                    $noCols[$referenceModule]["link_no"] = "no_available";
                }

                $sql = "SELECT * FROM vtiger_field WHERE tabid = ".getTabId($row["module"])." AND fieldname = ?";
                $resultTMP = $adb->pquery($sql, array($focus->list_link_field));
                if($adb->num_rows($resultTMP) > 0) {
                    $noCols[$referenceModule]["link_name"] = $adb->fetchByAssoc($resultTMP);
                } else {
                    $noCols[$referenceModule]["link_name"] = "no_available";
                }
            }

            if($noCols[$referenceModule]["link_no"] != "no_available") {
                $sql = "SELECT ".$noCols[$referenceModule]["link_no"]["columnname"]." as nofield FROM ".$noCols[$referenceModule]["link_no"]["tablename"]." WHERE ".$focus->table_index." = ".$row["crmid"];
                $linkFieldRst = $adb->query($sql);
                $recordNumber = $adb->query_result($linkFieldRst, 0, "nofield");
            }

            if($noCols[$referenceModule]["link_name"] != "no_available") {
                $sql = "SELECT ".$noCols[$referenceModule]["link_name"]["columnname"]." as linkfield FROM ".$noCols[$referenceModule]["link_name"]["tablename"]." WHERE ".$focus->table_index." = ".$row["crmid"];
                $linkFieldRst = $adb->query($sql);
                $linkField = $adb->query_result($linkFieldRst, 0, "linkfield");
            }

            $recordLink = "<a target='_blank' href='index.php?module=$referenceModule&view=Detail&record=".
                "".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$linkField."</a>";
			
			if($noCols[$referenceModule]["link_no"] != "no_available") {
				$numberField = "<a target='_blank' href='index.php?module=$referenceModule&view=Detail&record=".
					"".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$recordNumber."</a>";
			} else {
				$numberField = "<a target='_blank' href='index.php?module=$referenceModule&view=Detail&record=".
					"".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$row["crmid"]."</a>";
			}
			
            $row['recordLink'] = $recordLink;
            $row['numberField'] = $numberField;

            //Zend_Json::$useBuiltinEncoderDecoder = true;
            $settings = \Workflow\VtUtils::json_decode(html_entity_decode($row["block_settings"]));

            if(!isset($settings["btn_accept"])) {
                $settings["btn_accept"] = "LBL_OK";
            }
            if(!isset($settings["btn_rework"])) {
                $settings["btn_rework"] = "LBL_REWORK";
            }
            if(!isset($settings["btn_decline"])) {
                $settings["btn_decline"] = "LBL_DECLINE";
            }

            if(strpos($settings["btn_accept"], '$') !== false) {
                $context = \Workflow\VTEntity::getForId($row["crmid"], $referenceModule);
                $settings["btn_accept"] = \Workflow\VTTemplate::parse($settings["btn_accept"], $context);
            }
            if(strpos($settings["btn_rework"], '$') !== false) {
                $context = \Workflow\VTEntity::getForId($row["crmid"], $referenceModule);
                $settings["btn_rework"] = VTTemplate::parse($settings["btn_accept"], $context);

            }
            if(strpos($settings["btn_decline"], '$') !== false) {
                $context = \Workflow\VTEntity::getForId($row["crmid"], $referenceModule);
                $settings["btn_decline"] = VTTemplate::parse($settings["btn_accept"], $context);
            }

            $buttons['btn_accept'] = $settings["btn_accept"];
            $buttons['btn_rework'] = $settings["btn_rework"];
            $buttons['btn_decline'] = $settings["btn_decline"];

            $row['block'] = $settings;

            if(!empty($row['result'])) {
                $row['btn_accept_class'] = $row['result'] == 'ok'?'pressed':'unpressed';
                $row['btn_rework_class'] = $row['result'] == 'rework'?'pressed':'unpressed';
                $row['btn_decline_class'] = $row['result'] == 'decline'?'pressed':'unpressed';
            }

            $row['timestamp'] = DateTimeField::convertToUserFormat($row['timestamp']);
            if(!empty($row['result_user_id'])) {
                $row['result_timestamp'] = DateTimeField::convertToUserFormat($row['result_timestamp']);
            }

            $row['hash1'] = md5($current_user->id."##ok##".$row["conf_id"]);
            $row['hash2'] = md5($current_user->id."##rework##".$row["conf_id"]);
            $row['hash3'] = md5($current_user->id."##decline##".$row["conf_id"]);

            $row['textcolor'] = \Workflow\VtUtils::getTextColor($row['backgroundcolor']);

            $workflows[$row["title"].' - '.$row["block_title"]]['records'][] = $row;

            $workflows[$row["title"].' - '.$row["block_title"]]['buttons'] = $buttons;
            $workflows[$row["title"].' - '.$row["block_title"]]['blockid'] = $row['blockid'];

        }

        $viewer->assign('blocks', $workflows);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $sql = "SELECT
                    vtiger_wf_userqueue.*,
                    vtiger_wf_queue.*,
                    vtiger_wf_settings.*,
                    vtiger_wf_userqueue.id as userqueue_id,
                    vtiger_wfp_blocks.text as block_title,
                    vtiger_wfp_blocks.settings as block_settings
                FROM
                    vtiger_wf_userqueue
                INNER JOIN vtiger_wf_queue ON(vtiger_wf_queue.id = vtiger_wf_userqueue.queue_id)
                INNER JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_queue.workflow_id)
                INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_queue.block_id)

                WHERE
                    vtiger_wf_queue.execution_user = ".$current_user->id."
                GROUP BY
                    vtiger_wf_userqueue.id ORDER BY block_title
                ";
        $result = $adb->query($sql, true);
        $userqueue = array();

        while($row = $adb->fetchByAssoc($result)) {

            $referenceModule = $row["module_name"];
            $focus = CRMEntity::getInstance($referenceModule);

            if(empty($noCols[$referenceModule])) {
                $sql = "SELECT * FROM vtiger_field WHERE tabid = '".getTabId($referenceModule)."' AND uitype = 4";
                $resultTMP = $adb->query($sql);

                if($adb->num_rows($resultTMP) > 0) {
                    $noCols[$referenceModule]["link_no"] = $adb->fetchByAssoc($resultTMP);
                } else {
                    $noCols[$referenceModule]["link_no"] = "no_available";
                }

                $sql = "SELECT * FROM vtiger_field WHERE tabid = ".getTabId($referenceModule)." AND fieldname = ?";
                $resultTMP = $adb->pquery($sql, array($focus->list_link_field));
                if($adb->num_rows($resultTMP) > 0) {
                    $noCols[$referenceModule]["link_name"] = $adb->fetchByAssoc($resultTMP);
                } else {
                    $noCols[$referenceModule]["link_name"] = "no_available";
                }
            }

            $recordNumber = 'none';

            if($noCols[$referenceModule]["link_no"] != "no_available") {
                $sql = "SELECT ".$noCols[$referenceModule]["link_no"]["columnname"]." as nofield FROM ".$noCols[$referenceModule]["link_no"]["tablename"]." WHERE ".$focus->table_index." = ".$row["crmid"];
                $linkFieldRst = $adb->query($sql);
                $recordNumber = $adb->query_result($linkFieldRst, 0, "nofield");
            }

			if($noCols[$referenceModule]["link_name"] != "no_available") {
				$sql = "SELECT ".$noCols[$referenceModule]["link_name"]["columnname"]." as linkfield FROM ".$noCols[$referenceModule]["link_name"]["tablename"]." WHERE ".$focus->table_index." = ".$row["crmid"];
				$linkFieldRst = $adb->query($sql, true);
				$linkField = $adb->query_result($linkFieldRst, 0, "linkfield");
			}

            $recordLink = "<a target='_blank' href='index.php?module=$referenceModule&view=Detail&record=".
                "".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$linkField."</a>";
			
			if($noCols[$referenceModule]["link_no"] != "no_available") {
				$numberField = "<a target='_blank' href='index.php?module=$referenceModule&view=Detail&record=".
					"".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$recordNumber."</a>";
			} else {
				$numberField = "<a target='_blank' href='index.php?module=$referenceModule&view=Detail&record=".
					"".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$row["crmid"]."</a>";
			}

            switch($row['type']) {
                case 'requestValue':
                    $row['button'] = array('value' => getTranslatedString('LBL_DO_ACTION', 'Workflow2'));
                    break;
            }

            $row['recordLink'] = $recordLink;
            $row['numberField'] = $numberField;
            $row["subject"]= getTranslatedString($row["subject"], 'Workflow2');

            $userqueue[$row["subject"]][] = $row;
        }

        $viewer->assign('userqueue', $userqueue);
		$viewer->view('PermissionListPage.tpl', $moduleName);
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
			'modules.Vtiger.resources.List',
			'modules.'.$moduleName.'.views.resources.js.List',
			'modules.'.$moduleName.'.js.frontend',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

    function getHeaderCss(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = array(
            "~/modules/$moduleName/views/resources/Workflow2.css"
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }


}

?>