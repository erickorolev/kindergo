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

class Settings_Workflow2_HttpHandlerEditor_View extends Settings_Vtiger_Index_View {

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
        $edit_id = (int)$request->get('edit_id');

        $sql = "SELECT * FROM vtiger_wf_http_limits WHERE id = ".$edit_id;
        $result = $adb->query($sql);
        $limitData = $adb->fetchByAssoc($result);

        if(empty($limitData['url'])) {
            $options = array(
                  'handler_path'    => 'modules/Workflow2/HTTPHandler.php',
                  'handler_class'   => 'Workflow2_HTTPHandler_Handler',
                  'handler_function' => 'handle',
                  'handler_data'    => array(
                        'handlerid' => $edit_id,
                  )
            );
            $trackURL = Vtiger_ShortURL_Helper::generateURL($options);

            $sql = "UPDATE vtiger_wf_http_limits SET url = '" . $trackURL . "' WHERE id = ".$edit_id;
            $adb->query($sql);
        }

        $sql = "SELECT vtiger_wf_http_limits_value.*, vtiger_wf_settings.title 'wf_title' FROM
                vtiger_wf_http_limits_value
                 LEFT JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_http_limits_value.value)
            WHERE limit_id = ".$limitData["id"];
        $resultTMP = $adb->query($sql, true);
        $values = array();
        while($ip = $adb->fetchByAssoc($resultTMP)) {
            $values[$ip["mode"]][] = $ip["value"];
        }

        $trigger = array();
        $sql = "SELECT * FROM vtiger_wf_trigger WHERE custom = 1 AND deleted = 0 ORDER BY label";
        $result = $adb->query($sql);
        while($row = $adb->fetchByAssoc($result)) {
            $trigger[$row["key"]] = $row["label"];
        }

        $workflows = array();
        $sql = "SELECT * FROM vtiger_wf_settings WHERE active = 1 ORDER BY title";
        $result = $adb->query($sql);
        while($row = $adb->fetchByAssoc($result)) {
            $workflows[$row["id"]] = $row["id"].' - '.$row["title"];
        }

        $sql = "SELECT * FROM vtiger_wf_http_limits_ips WHERE limit_id = ".$limitData["id"];
        $resultTMP = $adb->query($sql, true);
        $ips = array();
        while($ip = $adb->fetchByAssoc($resultTMP)) {
            $ips[] = $ip["ip"];
        }

        $viewer->assign('ips', $ips);
        $viewer->assign('editId', $edit_id);
        $viewer->assign('limitData', $limitData);
        $viewer->assign('values', $values);
        $viewer->assign('trigger', $trigger);
        $viewer->assign('workflows', $workflows);

        echo $viewer->view('VT7/HttpHandlerEditor.tpl',$qualifiedModuleName,true);
	}

}