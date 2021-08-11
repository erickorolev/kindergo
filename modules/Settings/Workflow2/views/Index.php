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

class Settings_Workflow2_Index_View extends Settings_Workflow2_Default_View {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $adb = PearDatabase::getInstance();

        $sql = 'UPDATE vtiger_cron_task SET status = 1 WHERE name = "Workflow2 Queue" AND status = 0 AND laststart < '.(time() - 1800);
        $adb->query($sql);

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        /**
         * @var $settingsModel Settings_Colorizer_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);

        $className = "\\Workflow\\S"."WE"."xt"."ension\\"."ca62d58e352291a"."30c165c444877b1c92c5d28d5c";
        $as2df = new $className("Workflow2", $moduleModel->version);

        if(false === $as2df->g5e88fdd0c90580423fdf35595dceef598bcb2487()) {
            echo "<br/><br/><p style='text-align:center;color:red;font-weight:bold;'>".getTranslatedString("Failure during Reactivation.", "Settings:Workflow2")."<br>".getTranslatedString("You couldn't use the Workflow Designer Admin at the moment. Workflows are not stopped!", "Settings:Workflow2")."<br><br>".getTranslatedString("Please make sure, the VtigerCRM could connect to the internet.", "Settings:Workflow2")."</p>";
            $method = 'ha'.'sLi'.'cen'.'seKe'.'y';
            echo '<div style="text-align:center;">';
            if(!$as2df->$method()) {
                echo '<button type="button" class="btn btn-primary btn-lg" onclick="setLicense();">Set License</button>';
            } else {
                echo '<div style="font-weight:bold;">'.getTranslatedString("LBL_ALREADY_SET_KEY", "Settings:Workflow2").'</div>';
                echo '<button type="button" class="btn btn-primary btn-lg"  onclick="setLicense();">Set License</button>';
            }
            echo '</div>';

            $response = $as2df->checkConnection();
            if($response !== true) {
                $externalIp = file_get_contents('http://ipecho.net/plain');

                echo '<div><strong>Response of License Server:</strong><br/> '.$response.'</div>';
                if(strpos($response, 'blocked') !== false) {
                    $externalContent = file_get_contents('http://checkip.dyndns.com');

                    echo '<div class="alert alert into">Your IP is blocked by our Firewall. Unfortunately this could happen. Send us this IP and we will remove your IP: '.$externalIp.'</div>';
                }
            }
            return;
        }

        $targetModule = intval($request->get('targetModule'));
        if(empty($targetModule) && $targetModule != '0') {
            $targetModule = getTabId('Accounts');
        }
        $viewer->assign('targetModule', $targetModule);

        $sql = 'SELECT laststart FROM vtiger_cron_task WHERE name = "Workflow2 Queue"';
        $result = $adb->query($sql);
        $cronCheck = $adb->query_result($result, 0, 'laststart');
        if($cronCheck < time() - 86400) {
            $viewer->assign("SHOW_CRON_NOTICE", true);
        } else {
            $viewer->assign("SHOW_CRON_NOTICE", false);
        }

        $sql = 'SELECT COUNT(*) as num FROM vtiger_eventhandlers WHERE handler_path = "modules/Workflow2/WfEventHandler.php" AND is_active = 1';
        $result = $adb->query($sql);
        if($adb->query_result($result, 0, 'num') < 3) {
            $viewer->assign("SHOW_EVENT_NOTICE", true);
        } else {
            $viewer->assign("SHOW_EVENT_NOTICE", false);
        }

        $viewer->assign("ERROR_HANDLER_VALUE", ERROR_HANDLER_VALUE);
        $viewer->assign("is_admin", $current_user->is_admin == "on");

        $allowCreation = true;

        if($request->has('viewmode')) {
            if($request->get('viewmode') == 'folder') {
                \Workflow\Options::set(0, 'default_view', 'folder');
            } else {
                \Workflow\Options::set(0, 'default_view', 'module');
            }
        }
        $currentView = \Workflow\Options::get(0, 'default_view', 'module');

        switch($request->get("act")) {
            case "create":
                if($allowCreation) {
                    $sql = "INSERT INTO vtiger_wf_settings SET active = 0";
                    $adb->query($sql);

                    $workflow_id = \Workflow\VtUtils::LastDBInsertID();
                    $sql = "UPDATE vtiger_wf_settings SET title = 'Workflow ".$workflow_id."', `trigger` = 'WF2_MANUELL' WHERE id = ".$workflow_id;
                    $adb->query($sql);

                    $sql = "INSERT INTO vtiger_wfp_blocks SET workflow_id = ".$workflow_id.", active = 1, env_vars = '', type = 'start', `x` = 300, y = 300";
                    $adb->query($sql);

                    ob_get_clean();
                    header('Location:index.php?module=Workflow2&view=Config&parent=Settings&workflow='.$workflow_id);
                    //exit();

                    echo "Success. You will be redirected";
                    echo '<meta http-equiv="refresh" content="0; url=index.php?module=Workflow2&view=Config&parent=Settings&workflow='.$workflow_id.'">';
                    exit();
                } else {
                    echo "<div style='font-weight:bold;color:red;'>".getTranslatedString("LBL_WAR"."NING"."_LICE"."NSE_COU"."NT", "Workflow2")."</div>";
                }

                break;
            case "deactivate":
                $sql = "UPDATE vtiger_wf_settings SET active = 0 WHERE id = ".intval($request->get("workflow"));
                $adb->query($sql);
                break;

                $moduleModel->refreshFrontendJs();

            case "activate":
                $sql = "UPDATE vtiger_wf_settings SET active = 1 WHERE id = ".intval($request->get("workflow"));
                $adb->query($sql);

                $moduleModel->refreshFrontendJs();
                break;
            case "toggleTrigger":
                break;
            case "duplicate":
                //if($allowCreation) {
                $old_id = intval($_GET["workflow"]);

                $sql = "SELECT * FROM vtiger_wf_settings WHERE id = ".$old_id;
                $result = $adb->query($sql, true);

                $row = $adb->fetchByAssoc($result);
                $setter = array();
                $values = array();
                unset($row["id"]);
                $row["active"] = 0;
                $row["title"] .= " Copy";

                foreach($row as $key => $value) {
                    $setter[] = "`".$key."` = ?";
                    $values[] = html_entity_decode($value);
                }

                $sql = "INSERT INTO vtiger_wf_settings SET ".implode(",",$setter);
                $adb->pquery($sql, $values, false);

                $workflow_id = \Workflow\VtUtils::LastDBInsertID();

                $sql = "SELECT * FROM vtiger_wfp_blocks WHERE workflow_id = ".$old_id;
                $result = $adb->query($sql, true);
                $blockTransfer = array();

                while($row = $adb->fetchByAssoc($result)) {
                    $setter = array();
                    $values = array();
                    $row["workflow_id"] = $workflow_id;
                    $block_id = $row["id"];
                    unset($row["id"]);

                    foreach($row as $key => $value) {
                        $setter[] = "`".$key."` = ?";
                        $values[] = html_entity_decode($value);
                    }

                    $sql = "INSERT INTO vtiger_wfp_blocks SET ".implode(",",$setter);
                    $adb->pquery($sql, $values, false);

                    $blockTransfer["block_".$block_id] = \Workflow\VtUtils::LastDBInsertID();
                }

                $sql = "SELECT * FROM vtiger_wfp_connections WHERE workflow_id = ".$old_id." AND deleted = 0";
                $result = $adb->query($sql, false);

                while($row = $adb->fetchByAssoc($result)) {
                    $setter = array();
                    $values = array();
                    $row["workflow_id"] = $workflow_id;
                    $row["source_id"] = $blockTransfer["block_".$row["source_id"]];
                    $row["destination_id"] = $blockTransfer["block_".$row["destination_id"]];
                    unset($row["id"]);

                    foreach($row as $key => $value) {
                        $setter[] = "`".$key."` = ?";
                        $values[] = html_entity_decode($value);
                    }

                    $sql = "INSERT INTO vtiger_wfp_connections SET ".implode(",",$setter);
                    $adb->pquery($sql, $values, false);
                }

                $sql = "SELECT * FROM vtiger_wfp_objects WHERE workflow_id = ".$old_id."";
                $result = $adb->query($sql, false);

                while($row = $adb->fetchByAssoc($result)) {
                    $setter = array();
                    $values = array();
                    $row["workflow_id"] = $workflow_id;
                    unset($row["id"]);

                    foreach($row as $key => $value) {
                        $setter[] = "`".$key."` = ?";
                        $values[] = html_entity_decode($value);
                    }

                    $sql = "INSERT INTO vtiger_wfp_objects SET ".implode(",",$setter);
                    $adb->pquery($sql, $values, false);
                }

                echo "<script type='text/javascript'>window.location.href='index.php?module=Workflow2&view=Index&parent=Settings';</script>";
                exit();
                // } else {
                //  echo "<div>".getTranslatedString("LBL_WAR"."NING"."_LICE"."NSE_COU"."NT", "Workflow2")."</div>";
                //}
                break;
            case "delete":
                $sql = "DELETE FROM vtiger_wf_settings WHERE id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $sql = "DELETE FROM vtiger_wf_queue WHERE workflow_id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $sql = "DELETE FROM vtiger_wf_log WHERE workflow_id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $sql = "DELETE FROM vtiger_wfp_objects WHERE workflow_id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $sql = "DELETE FROM vtiger_wfp_connections WHERE workflow_id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $sql = "DELETE FROM vtiger_wfp_blocks WHERE workflow_id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $sql = "DELETE FROM vtiger_wf_confirmation WHERE workflow_id = ".intval($_GET["workflow"]);
                $adb->query($sql);

                $moduleModel->refreshFrontendJs();

                break;
        }
        #$className = "S"."WE"."xt"."ension_"."Colorizer_"."721d6ffafc464e72f7e"."afca66f949ed76486afbf";
        #$asdf = new $className("Colorizer", $moduleModel->version);
        #$viewer->assign("versi"."onC", $asdf->ge2055887bb4f19d2c67775b32b574553());

        $triggerLabel = array();
        $sql = "SELECT * FROM vtiger_wf_trigger";
        $result = $adb->query($sql);
        while($row = $adb->fetchByAssoc($result)) {
            $triggerLabel[$row["key"]] = getTranslatedString($row["label"], "Settings:Workflow2");
        }

        if (!empty($_COOKIE['wf_visibility'])) {
            $userVisibility = @json_decode($_COOKIE['wf_visibility'], true);
        } else {
            $userVisibility = false;
        }

        if($currentView == 'module') {
            $sql = "SELECT vtiger_wf_settings.*, vtiger_users.user_name, COUNT(*) as errornum, vtiger_wf_errorlog.block_id as errorblockid FROM vtiger_wf_settings
            LEFT JOIN vtiger_users ON(vtiger_users.id = vtiger_wf_settings.last_modify_by)
            LEFT JOIN vtiger_wf_errorlog ON(vtiger_wf_errorlog.workflow_id = vtiger_wf_settings.id AND vtiger_wf_errorlog.datum_eintrag >= '" . date('Y-m-d', time() - (86400 * 7)) . "')
              WHERE module_name != '' " . (!empty($targetModule) ? ' AND module_name = "' . \Workflow\VtUtils::getModuleName($targetModule) . '"' : '') . " GROUP BY vtiger_wf_settings.id ORDER BY module_name, active DESC, title";
            $result = $adb->query($sql);

            $workflows = array();
            $lastModule = "";
            $activeSidebarWidget = false;
            while ($row = $adb->fetch_array($result)) {
                $moduleName = getTranslatedString($row["module_name"], $row["module_name"]);

                if (vtlib_isModuleActive($row["module_name"])) {
                    $sql = "SELECT linkid FROM vtiger_links WHERE linktype = 'DETAILVIEWSIDEBARWIDGET' AND linklabel = 'Workflow Designer' AND tabid = " . Vtiger_Functions::getModuleId($row["module_name"]);
                    $sidebarWidget = $adb->query($sql);
                    if ($adb->num_rows($sidebarWidget) > 0) {
                        $activeSidebarWidget = true;
                    } else {
                        $activeSidebarWidget = false;
                    }
                } else {
                    $activeSidebarWidget = false;
                }
                $row["sidebar_active"] = $activeSidebarWidget;
                $row["startCondition"] = "";

                if ($row["active"] == "1") {
                    $row["startCondition"] = $triggerLabel[$row["trigger"]];
                }

                if (empty($row['errorblockid'])) {
                    $row['errornum'] = 0;
                }
                $workflows[$moduleName][] = $row;
            }

            $viewer->assign("workflows", $workflows);

            $visibility = array();
            $entityModules = \Workflow\VtUtils::getEntityModules();

            $visibility = unserialize($_COOKIE['visibility']);
            $sql = 'SELECT id, module_name, active, COUNT(*) as num FROM vtiger_wf_settings WHERE module_name != "" GROUP BY module_name, active';
            $countResult = $adb->query($sql);

            while ($row = $adb->fetchByAssoc($countResult)) {
                if (!isset($moduleWfCount[getTabid($row['module_name'])])) $moduleWfCount[getTabid($row['module_name'])] = array('1' => 0, '0' => 0);
                $moduleWfCount[getTabid($row['module_name'])][$row['active']] = $row['num'];
            }

            foreach ($entityModules as $tabid => $mod) {
                if (!empty($targetModule)) {
                    $visibility[\Workflow\VtUtils::getModuleName($targetModule)] = true;
                }
                $visibility[$mod[0]] = $userVisibility !== false && $userVisibility[$tabid] ? true : false;
            }

            $viewer->assign('moduleWfCount', $moduleWfCount);
            $viewer->assign('entityModules', $entityModules);
        }
        if($currentView == 'folder') {
            if(!empty($_REQUEST['targetFolder'])) {
                $targetFolder = urldecode($_REQUEST['targetFolder']);
                $viewer->assign('targetFolder', $targetFolder);
            } else {
                $viewer->assign('targetFolder', '');
            }
            $visibility = array();

            $sql = "SELECT
                      vtiger_wf_settings.*,
                      vtiger_users.user_name, COUNT(*) as errornum, vtiger_wf_errorlog.block_id as errorblockid
                  FROM vtiger_wf_settings
                    LEFT JOIN vtiger_users ON(vtiger_users.id = vtiger_wf_settings.last_modify_by)
                    LEFT JOIN vtiger_wf_errorlog ON(vtiger_wf_errorlog.workflow_id = vtiger_wf_settings.id AND vtiger_wf_errorlog.datum_eintrag >= '" . date('Y-m-d', time() - (86400 * 7)) . "')
              WHERE module_name != '' " . (!empty($targetFolder) ? ' AND folder = "' . $targetFolder . '"' : '') . "
              GROUP BY vtiger_wf_settings.id
              ORDER BY folder, sort";
            $result = $adb->query($sql);

            $workflows = array();

            $folderCounts = array();
            while ($row = $adb->fetch_array($result)) {
                $moduleName = getTranslatedString($row["module_name"], $row["module_name"]);

                if (vtlib_isModuleActive($row["module_name"])) {
                    $sql = "SELECT linkid FROM vtiger_links WHERE linktype = 'DETAILVIEWSIDEBARWIDGET' AND linklabel = 'Workflow Designer' AND tabid = " . Vtiger_Functions::getModuleId($row["module_name"]);
                    $sidebarWidget = $adb->query($sql);
                    if ($adb->num_rows($sidebarWidget) > 0) {
                        $activeSidebarWidget = true;
                    } else {
                        $activeSidebarWidget = false;
                    }
                } else {
                    $activeSidebarWidget = false;
                }
                $row["sidebar_active"] = $activeSidebarWidget;
                $row["startCondition"] = "";

                if ($row["active"] == "1") {
                    $row["startCondition"] = $triggerLabel[$row["trigger"]];
                }

                if (empty($row['errorblockid'])) {
                    $row['errornum'] = 0;
                }
                $workflows[strtolower($row['folder'])][$moduleName][] = $row;

                if(!isset($folderCounts[strtolower($row['folder'])])) {
                    $folderCounts[strtolower($row['folder'])] = 0;
                }

                $folderCounts[strtolower($row['folder'])]++;
            }

            $sql = 'SELECT * FROM vtiger_wf_folder';
            $result = $adb->pquery($sql);

            $workflowFolderSettings = array();
            while($row = $adb->fetchByAssoc($result)) {
                $workflowFolderSettings[$row['title']] = $row;
            }

            $viewer->assign('folderSettings', $workflowFolderSettings);

            $workflowModules = array();

            // Load all available Folders
            $sql = 'SELECT folder FROM vtiger_wf_settings WHERE folder != "" GROUP BY folder ORDER BY folder';
            $result = $adb->query($sql);
            $availFolders = array();
            while($row = $adb->fetchByAssoc($result)) {
                $availFolders[] = html_entity_decode($row['folder'], ENT_QUOTES, 'UTF-8');

                $workflowModules[$row['folder']] = implode(', ', array_keys($workflows[$row['folder']]));
            }

            $viewer->assign("workflows", $workflows);
            $viewer->assign('folderCounts', $folderCounts);
            $viewer->assign('workflowModules', $workflowModules);

            $viewer->assign('availFolder', $availFolders);
            $visibility = $userVisibility;
        }

        $viewer->assign('visibility', $visibility);

        $viewer->view('VT7/Index.' . $currentView . '.tpl', $qualifiedModuleName);
        #require_once("modules/Workflow2/admin.php");
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
            "modules.Settings.$moduleName.views.resources.Workflow2",
            "modules.Settings.$moduleName.views.resources.Index",
            "modules.Settings.$moduleName.views.resources.LicenseManager",
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
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);

        return $headerStyleInstances;
    }
}