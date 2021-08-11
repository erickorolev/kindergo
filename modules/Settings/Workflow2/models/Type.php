<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Workflow2_Type_Model extends Vtiger_Base_Model {

    public function __construct($values) {
        if(!empty($values["backgroundFile"])) {
            unset($values["background"]);
        }

        parent::__construct($values);
    }

    public function getInputHTML($point) {
        return 'initInputPoint("'.$point.'");';//."\n";
    }
    public function getPersonInputHTML($pointID) {
        // Person outputs are not used anymore
        return '';
        $points = $this->get("persons");
        $html = "";
        $a = 0;

        foreach($points as $point) {
            $html .= 'initPersonInputPoint("'.addslashes($pointID).'","'.htmlentities($point[0]).'","'.htmlentities(getTranslatedString($point[1], "Settings:Workflow2")).'", '.count($points).', '.$a.');';

/*            $html .= 'endpoints["'.$pointID.'__'.strtolower($point[0]).'"] = jsPlumb.addEndpoint("'.$pointID.'", {
                    anchor:topAnchor['. count($points).']['.$a.'],
                    maxConnections:'.MAX_CONNECTIONS.',
                    overlays:getOverlay("'.getTranslatedString(utf8_encode($point[1]), "Workflow2").'", "personLabel")  },
                    jQuery.extend(getInput("modules/Workflow2/icons/peopleInput.png", "person", false, true, true), {parameters:{ "in":"'.$pointID.'__'.strtolower($point[0]).'" }}));';
           $html .= '_listeners(endpoints["'.$pointID.'__'.strtolower($point[0]).'"]);'; */
           $a++;
        }
        return $html;
    }

    public function getOutputHTML($pointID) {
        $points = $this->get("output");
        $html = "";
        $a = 0;

        foreach($points as $point) {
            $html .= 'initOutputPoint("'.addslashes($pointID).'","'.htmlentities($point[0], ENT_QUOTES, "UTF-8").'","'.htmlentities(getTranslatedString($point[1], "Settings:Workflow2"), ENT_QUOTES, "UTF-8").'", "'.htmlentities((isset($point[2])?getTranslatedString($point[2], "Settings:Workflow2"):""), ENT_QUOTES, "UTF-8").'", '.count($points).', '.$a.');';
/*            $html .= 'endpoints["'.$pointID.'__'.strtolower($point[0]).'"] =
jsPlumb.addEndpoint("'.$pointID.'", {
    anchor:rightAnchor['.($point[1]=="Start"?"0":count($points)).']['.$a.'],
    maxConnections:'.MAX_CONNECTIONS.',
    connectorOverlays:[[ "Label", { location:0.4,cssClass:"connectionLabel",label:"'.(isset($point[2])?getTranslatedString($point[2], "Settings:Workflow2"):"").'", id:"label"}]],
    overlays:getOverlay("'.getTranslatedString($point[1], "Settings:Workflow2").'") }, jQuery.extend(getInput("modules/Workflow2/icons/output.png", "flowChart", true, false, false), {parameters:{ out:"'.$pointID.'__'.strtolower($point[0]).'" }}));';
            $html .= '_listeners(endpoints["'.$pointID.'__'.strtolower($point[0]).'"]);'; */
            $a++;
        }
        $html .= "\n";

        return $html;
    }

}