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

class Settings_Workflow2_SettingsDBCheck_View extends Settings_Workflow2_Default_View {

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

        ?>

<div class="container-fluid" id="moduleManagerContents">
        <div class="editViewHeader">
            <h4>
                <a href="index.php?module=Workflow2&view=Index&parent=Settings"><?php echo vtranslate('Workflow Designer', 'Workflow2'); ?></a> &raquo;
                DB Check
            </h4>
        </div>
        <hr>
        <div class="listViewActionsDiv">
        <div style="padding:20px;border:1px solid #ccc;background-color:#fff;">
        <?php
            $steps = 5;
            $objWorkflow = new Workflow2();
            
            echo "<strong>Step 1 / ".$steps." - Check Database Structure</strong><br>";
            $objWorkflow->checkDB(true);

            echo "ok<br/>";
            echo "<strong>Step 2 / ".$steps." - Check Links</strong>";
            $objWorkflow->AddGlobalEvents();
            $objWorkflow->AddHeaderLink();

            /**
             * @var $workflowObj Workflow2_Module_Model
             */
            $workflowObj = Vtiger_Module_Model::getInstance('Workflow2');
            $workflowObj->refreshFrontendJs();

            $objWorkflow->addLinks();
            $objWorkflow->checkSettingsField();
            $objWorkflow->addDefaultTrigger();
            echo " - ok<br/>";
            echo "<strong>Step 3 / ".$steps." - check external Extensions</strong>";
            $objWorkflow->installExtensions();
            echo " - ok<br/>";
            echo "<strong>Step 4 / ".$steps." - install latest languages</strong>";
            $objWorkflow->installLanguages();
            echo " - ok<br/>";
            echo "<strong>Step 5 / ".$steps." - check custom inventory fields</strong>";
            $objWorkflow->checkCustomInventoryFields();
            echo " - ok<br/>";
            echo "<strong>Step 6 / ".$steps." - check custom inventory fields</strong>";
            $objWorkflow->checkRepository();
            echo " - ok<br/><br/>";


            echo "<p style='text-align:center;font-weight:bold;'><a href='index.php?module=Workflow2&view=Index&parent=Settings'>&laquo; Back to Workflow Designer Settings</a></p>";
        ?>
        </div>
    </div>

</div>
        <?php
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