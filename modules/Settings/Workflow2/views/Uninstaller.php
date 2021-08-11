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

class Settings_Workflow2_Uninstaller_View extends Settings_Workflow2_Default_View {

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

        $obj = new Workflow2();
        /**
         * @var $settingsModel Settings_Colorizer_Module_Model
         */
        $settingsModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);

        ?>

        <div class="container-fluid" id="moduleManagerContents">

            <div class="widget_header row-fluid">
                <div class="span12">
                    <h3>
                        <b>
                            <a href="index.php?module=Workflow2&view=Index&parent=Settings">Workflow Designer</a> &raquo;
                            Uninstaller
                        </b>
                    </h3>
                </div>
            </div>
            <hr>
            <div class="settingsUI" style="width:600px;padding:10px;margin-left:10px;">
                <div style="padding:20px;border:1px solid #ccc;background-color:#fff;">
                    <?php
                    if(!empty($_POST['confirm0815'])) {
                        $sql = 'DELETE FROM vtiger_tab WHERE name = "Workflow2"';
                        $adb->query($sql);

                        $obj->disableModule();
                        ?>
                        <p>Links, Settings are removed ...</p>
                        <h3>This module do NOT remove any files or database tables.</h3>
                        <p>Please delete the following files/directories manually</p>
                        <ul>
                            <li>modules/Workflow2/</li>
                            <li>modules/Settings/Workflow2/</li>
                            <li>layouts/vlayout/modules/Workflow2/</li>
                            <li>layouts/vlayout/modules/Settings/Workflow2/</li>
                            <li>languages/*/Workflow2.php</li>
                            <li>languages/*/Settings/Workflow2.php</li>
                        </ul>
                        <p>Please delete all database tables prefix with vtiger_wf_ AND vtiger_wfp_</p>
                    <?php } else { ?>
                    <form method="POST" action="#">
                        Please confirm to uninstall the module: <input type="checkbox" name="confirm0815" required="required" value="confirm" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-primary" name="confirmation" value="Confirm" />
                    </form>
                    <?php } ?>
                </div>
            </div>

        </div>
        <?
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
            "~/modules/Settings/$moduleName/views/resources/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}