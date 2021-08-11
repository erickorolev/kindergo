<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 11.01.14 17:04
 * You must not use this file without permission.
 */
define('UPDATE_MODULENAME', basename(dirname(dirname(__FILE__))));

global $root_directory;
require_once($root_directory."/modules/".UPDATE_MODULENAME."/autoloader.php");

class Settings_Workflow2_Upgrade_View extends Settings_Vtiger_Index_View {

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();

        $className = '\\'.UPDATE_MODULENAME.'\\Autoload';
        $className::registerDirectory("~/modules/".UPDATE_MODULENAME."/lib");

        $objUpdater = new \Workflow\SWExtension\AutoUpdate($moduleName, "stable");

        $step = $request->get("step");
        if(empty($step)) $step = 1;
        global $vtiger_current_version;

        if($step == 3) {
            $objUpdater->installCurrentVersion();
            exit();
        }

    if($step == 1) {
        $viewer = $this->getViewer($request);
        ?>
        <div class="modal-dialog modelContainer" style="width:600px;">
        <?php
        $viewer->assign('TITLE', 'Check module update');
        $viewer->view('ModalHeader.tpl');
?>
            <div class="modal-content">
                <div  style="padding:20px;">
                <?php
                $currentVersion = $objUpdater->getCurrentInstalledVersion();
                $latestVersion = $objUpdater->getLatestVersion();

                $licenseHint = false;
                if(is_array($latestVersion)) {
                    $licenseHint = $latestVersion[1];
                    $latestVersion = $latestVersion[0];
                }
                echo "<div style='font-size:15px;'>Current installed version: ".$currentVersion."</div>";
                if(empty($latestVersion)) {
                    echo "<div style='font-size:14px;margin-top:10px;border:2px solid #a7003f;padding:5px;'><strong>No module update information for your VtigerCRM version</strong></div>";
                } else {
                    echo "<div style='font-size:15px;'>Current available version: " . $latestVersion . "</div>";
                }

                $button = '';
                if($latestVersion > $currentVersion) {
                    $changelog = $objUpdater->getChangelog();
                    echo "<div style='font-weight:bold;margin-top:25px;'>Update available".(!empty($changelog)?" | <a href='".$objUpdater->getChangelog()."' target='blank'>see Changelog</a>":"")."</div>";

                    $upgradeUrl = "index.php?module=".$request->get("module")."&view=".$request->get("view")."&step=2";
                    $parent = $request->get("parent");
                    if(!empty($parent)) {
                        $upgradeUrl .= "&parent=".$parent;
                    }
                    $stefanDebug = $request->get("stefanDebug");
                    if(!empty($stefanDebug)) {
                        $upgradeUrl .= "&stefanDebug=1";
                    }

                    //echo "<br><button class='btn addButton' onclick=\"window.location.href='".$upgradeUrl."';\"><strong>Install update</strong></button>";
                    $button = '<button class="btn btn-success StartUpdate" data-module="'.$moduleName.'" type="submit" name="saveButton"><strong>'.vtranslate('START UPDATE').'</strong></button>';
                }

                echo '<div id="RUNNING_UPDATE" style="display:none;">';
                echo "<div style='font-weight:bold;margin-top:25px;font-size:20px;'>Upgrade module '".UPDATE_MODULENAME."' to version ".$latestVersion."!</div>";
                echo "<div id='pendingUpdate' style='text-align:center;'><img src='layouts/v7/skins/images/install_loading.gif'></div>";
                echo '</div>';

                echo '</div>';
                ?>
                    <div class="modal-footer">
                        <center>
                            <?php echo $button; ?>
                            <a href="#" class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL'); ?></a>
                        </center>
                    </div>
            </div>
        </div>


    <?php return; }

   	}

	public function getHeaderScripts(Vtiger_Request $request) {

	}

    public function checkPermission(Vtiger_Request $request) {

   	}
}