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

use Workflow\VtUtils;
use Workflow\Main;

class Settings_Workflow2_Config_View extends Settings_Vtiger_Index_View {

    protected $isReadonly = false;
    protected $workflowData = false;
    protected $qualifiedModuleName = false;
    protected $moduleName = false;
    /**
     * @var bool|Settings_Workflow2_Module_Model
     */
    protected $settingsModel = false;

    public function initView(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);

        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
        $viewer->assign('VERSION', $moduleModel->version);

        global $current_user;
        $adb = PearDatabase::getInstance();
        //Zend_Json::$useBuiltinEncoderDecoder = true;

        $this->moduleName = $request->getModule();
        $this->qualifiedModuleName = $request->getModule(false);
        $this->settingsModel = Settings_Vtiger_Module_Model::getInstance($this->qualifiedModuleName);

        $viewer = $this->getViewer($request);

        $this->workflowID = $workflowID = intval($_GET["workflow"]);
        $workflowObj = new Main($workflowID);

        $runningCounter = $workflowObj->countRunningInstances();
        $errorCounter = $workflowObj->countLastError();

        $this->workflowData = $workflowObj->getSettings();
        $workflowObj->setExecutionTrigger($this->workflowData['trigger']);
        if(!empty($this->workflowData["module_name"])) {
            $types = $this->settingsModel->getTypes($this->workflowData["module_name"], $workflowObj->getExecutionTrigger());
            $typesCat = $this->settingsModel->getTypeCats($this->workflowData["module_name"]);

            $viewer->assign("types", $types);
            $viewer->assign("typesCat", $typesCat);

            $html = $this->getWorkflowBlockHTML();

            $viewer->assign("html", $html);
            $viewer->assign("WorkflowObjectHTML", $this->getWorkflowObjectHTML());

            $recordModel = \Vtiger_Module_Model::getInstance($this->workflowData["module_name"]);

            if($recordModel instanceof \Inventory_Module_Model) {
                $viewer->assign('IsInventory', true);
            } else {
                $viewer->assign('IsInventory', false);
            }

        }

        $viewer->assign("runningCounter", $runningCounter);
        $viewer->assign("errorCounter", $errorCounter);

        $viewer->assign("workflowID", $workflowID);
        $viewer->assign("maxConnections", MAX_CONNECTIONS);

        $viewer->assign("workflowData", $this->workflowData);
        $viewer->assign("is_admin", $current_user->is_admin == "on");

        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = $adb->query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            $module[$row["tabid"]] = getTranslatedString($row["tablabel"],$row["name"]);
        }
        asort($module);
        $viewer->assign("module", $module);

    }

    public function getWorkflowObjectHTML() {
        $adb = PearDatabase::getInstance();

        $sql = "SELECT * FROM vtiger_wf_objects WHERE workflow_id = ".$this->workflowID;
        $result = $adb->query($sql);
        $html = "";

        while($row = $adb->fetchByAssoc($result)) {
            $html .= "<div id='workflowDesignerObject_".$row["id"]."' style='top:".$row["y"]."px;left:".$row["x"]."px;' class='workflowDesignerObject_text'>".html_entity_decode($row["content"], ENT_QUOTES, "UTF-8")."</div>";
        }

        $html .= "";
        return $html;
    }
    public function getInputPointHtml($inputPoints) {
        $html = "";
        $types = $this->settingsModel->getTypes($this->workflowData["module_name"]);

        foreach($inputPoints as $point => $type) {
            $html .= $types[$type]->getInputHTML($point);
        }
        //$html .= "\n\n";

        //$html .= "";

        return $html;
    }
    public function getOutputPointHtml($outputPoints) {
        $html = "";
        $types = $this->getTypes();

        foreach($outputPoints as $pointID => $type) {
            $html .= $types[$type]->getOutputHTML($pointID);
        }
        $html .= "";
        $html .= "\n\n";
        return $html;
    }

    public function getTypes() {
        if($this->settingsModel === false) {
            $this->settingsModel = Settings_Vtiger_Module_Model::getInstance($this->qualifiedModuleName);
        }

        return $this->settingsModel->getTypes($this->workflowData["module_name"]);
    }
    public function getPersonInputPointHtml($personInputPoints) {
        $html = "";
        $types = $this->getTypes();

        foreach($personInputPoints as $pointID => $type) {
            $html .= $types[$type]->getPersonInputHTML($pointID);
        }
        $html .= "";
        $html .= "\n\n";

        return $html;
    }
    public function getPersonOutputPointHtml($personOutputPoints) {
        $html = "";

        foreach($personOutputPoints as $pointID) {
            $html .= '
                endpoints["'.$pointID.'__person"] = jsPlumbInstance.addEndpoint("'.$pointID.'", { anchor:bottomAnchor, maxConnections:'.MAX_CONNECTIONS.' }, jQuery.extend(getInput("modules/Workflow2/icons/peopleOutput.png", "person", true, false, true), {parameters:{ out:"'.$pointID.'__person" }}));
            ';
        }
        $html .= "";
        $html .= "\n\n";

        return $html;
    }
    public function getConnectionHtml($connections) {
        $html = "";

        foreach($connections as $conn) {
            $html .='
            if(endpoints["'.$conn[0].'"] != undefined && endpoints["'.$conn[1].'"] != undefined) {
                connectEndpoints("'.$conn[0].'", "'.$conn[1].'");
            }
            ';
        }

        return $html;
    }
    public function getWorkflowBlockHTML() {
        $types = $this->settingsModel->getTypes($this->workflowData["module_name"]);
        $persons = $this->settingsModel->getWorkflowObjects($this->workflowID);
        $elements = $this->settingsModel->getWorkflowBlocks($this->workflowID);
        $connections = $this->settingsModel->getWorkflowConnections($this->workflowID);

        $html = "";

        $inputPoints = array();

        $maxOutputPoints = 3;
       	$outputPoints = array();

           $personInputPoints = array();
           $personOutputPoints = array();
            $maxLeft = 0;
            $maxTop = 0;
            foreach($elements as $element) {
                if(empty($types[$element["type"]])) {
                    //echo 'Error in init of '.$element["type"].' block';
                    //continue;
                    $types[$element["type"]] = $this->settingsModel->getDummyTask($element["type"]);
                }

                $type = $types[$element["type"]]->getData();

                if($element["y"] < 0) {
                   $element["y"] = 0;
                }
                if($element['x'] > $maxLeft) {
                    $maxLeft = $element['x'];
                }
                if($element['y'] > $maxTop) {
                    $maxTop = $element['y'];
                }
                $html .= '<div data-type="'.$element["type"].'" class="context-wfBlock noselect wfBlock '.(!empty($type["styleclass"])?" ".$type["styleclass"]:"").' '.($element["active"]=="0"?" wfBlockDeactive":"").'" id="'.$element["id"].'" style="top:'.$element["y"].'px;left:'.$element["x"].'px;"><div class="imgElement '.(!empty($type["styleclass"])?" ".$type["styleclass"]:"").'" style="'.(!empty($type["background"])?"background-image:url(modules/".$type["module"]."/icons/".$type["background"].".png);":"").''.(!empty($type["backgroundFile"])?"background-image:url(".$type["backgroundFile"].");":"").'"></div><span class="blockDescription">'.$type["text"].'<span style="font-weight:bold;" id="'.$element["id"].'_description">'.(!empty($element["text"])?'<br>'.$element["text"].'':'').'</span></span>'.($element["type"]!="start"?'<div class="idLayer" style="display:none;">'.$element["block_id"].'</div>':'').'<div data-color="'.(!empty($element["colorlayer"])?"#".$element["colorlayer"]:"").'" style="background-color:'.(!empty($element["colorlayer"])?"#".$element["colorlayer"]:"").'" class="colorLayer '.(!empty($element["colorlayer"])?"colored":"").'">&nbsp;</div><img style="z-index:30;position:relative;" class="settingsIcon" src="modules/Workflow2/icons/settings.png"></div>';
                if($type["input"] !== false) {
                   $inputPoints[$element["id"]] = $element["type"];
                }

                if(count($type["output"]) > $maxOutputPoints)
                   $maxOutputPoints = $type["output"];

                if(is_array($type["output"])) {
                   $outputPoints[$element["id"]] = $element["type"];
                }
                if(is_array($type["persons"])) {
                   $personInputPoints[$element["id"]] = $element["type"];
                }
            }

           foreach($persons as $person) {
               $personOutputPoints[] = $person["id"]."";
               $html .= '<div class="wfBlock noselect wfPerson" alt="double click to change"title="double click to change" id="'.$person["id"].'" style="top:'.$person["y"].'px;left:'.$person["x"].'px;"><span>'.(empty($person["name"])?"Not connected":$person["name"]).'</span><img src="modules/Workflow2/icons/cross-button.png" class="removePersonIcon" onclick="removePerson(\''.$person["id"].'\');"></div>';
           }

        $tmpArray = array();
         for($a = 1; $a <= $maxOutputPoints; $a++) {
             $steps = 1 / ($a + 1);

             $tmp = array();
             for($i = 1; $i <= $a; $i++) {
                 $tmp[] = array(round($i * $steps, 2), 0, 0, -1, 0, -2);
             }
             $array[$a] = $tmp;
         }
        $html .= "
<script type='text/javascript'>
    topAnchor = ".json_encode($array).";
</script>";
        $html .= "<script type='text/javascript'>";
            $html .= "jQuery('#mainWfContainer').css('width', '".($maxLeft+250)."px');";
            $html .= "var currentWorkSpaceHeight = ".($maxTop+200)."; jQuery('#mainWfContainer').css('height', '".($maxTop+200)."px');";

        $html .= "jQuery(function() { jsPlumb.ready(function() {";
        $html .= 'initJsPlumb(function(jsPlumbInstance) {';
        $html .= $this->getInputPointHtml($inputPoints);
        $html .= $this->getOutputPointHtml($outputPoints);

        $html .= $this->getPersonInputPointHtml($personInputPoints);
        $html .= $this->getPersonOutputPointHtml($personOutputPoints);

        $html .= $this->getConnectionHtml($connections);
        $html .= "});";
        $html .= "});";
        $html .= "});";
        $html .= "</script>";



        return $html;

    }

	public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);

        $viewer->view('VT7/Config.tpl', $this->qualifiedModuleName);
	}

    public function preProcessDisplay (Vtiger_Request $request) {
        $this->initView($request);

   		$viewer = $this->getViewer($request);

   		$viewer->assign('MODE', 'Config');

        parent::preProcessDisplay($request);
   		//$viewer->view('ConfigMenuStart.tpl', $this->qualifiedModuleName);
   	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $jsFileNames = array(
            "~/modules/Settings/$moduleName/views/resources/Designer.js",
            "modules.Settings.$moduleName.views.resources.Workflow2",
        );

        if('Settings_Workflow2_Statistic_View' != get_class($this) ) {
            $jsFileNames[] = "modules.Settings.$moduleName.views.resources.Config";
        }

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

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
        $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");

        $cssFileNames = array(
            "~layouts/".Vtiger_Viewer::getLayoutName()."/modules/Settings/$moduleName/resources/Workflow2.css",
            "~/modules/$moduleName/views/resources/switcher.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);

        foreach($headerStyleInstances as $obj) {
            $src = $obj->get('href');
            if(!empty($src) && strpos($src, $moduleName) !== false) {
                $obj->set('href', $src.'?v='.$moduleModel->version);
            }
        }

        return $headerStyleInstances;
    }
}