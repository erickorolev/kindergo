<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */
namespace Workflow\Preset;

use \Workflow\VtUtils;
use \Workflow\VTEntity;


class StaticFields extends \Workflow\Preset
{
    protected $_JSFiles = array('StaticFields.js');
    protected $_fromFields = null;

    public function beforeSave($data) {
        unset($data[$this->field]["##SETID##"]);
        return $data;
    }

    public function getFromFields() {
        if($this->_fromFields === null) {
            $this->_fromFields = VtUtils::getFieldsWithBlocksForModule($this->parameter['fromModule'], true);
        }

        return $this->_fromFields;
    }

    public function beforeGetTaskform($data) {
        global $current_user;

        $adb = \PearDatabase::getInstance();

        list($data, $viewer) = $data;

        $fromModule = $this->parameter['fromModule'];
        $additionalToFields = $this->parameter['additionalToFields'];
        $refFields = !empty($this->parameter['refFields'])?true:false;

        /** Assigned Users */
        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, "id");

        $availUser = array('user' => array(), 'group' => array());

        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);
        while($user = $adb->fetchByAssoc($result)) {
            $user["id"] = $user["id"];
            $availUser["user"][] = $user;
        }

        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Groups'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, "id");

        $sql = "SELECT * FROM vtiger_groups ORDER BY groupname";
        $result = $adb->query($sql);
        while($group = $adb->fetchByAssoc($result)) {
            $group["groupid"] = $group["groupid"];
            $availUser["group"][] = $group;
        }
        $setter_fields = array();

        $viewer->assign("fromFields", $this->getFromFields());

        $viewer->assign("StaticFieldsField", $this->field);
        $viewer->assign("staticFields", $viewer->fetch("modules/Settings/Workflow2/helpers/StaticFields.tpl"));

        $options = $this->parameter;

        $script = "var StaticFieldsFrom = ".json_encode($this->getFromFields()).";\n";
        $script .= "var StaticFieldsCols = ".json_encode($data[$this->field]).";\n";
        $script .= "var StaticFieldsField = '".$this->field."';\n";
        $script .= "var available_users = ".json_encode($availUser).";\n";
        $script .= "var WfStaticFieldsFromModule = '".$fromModule."';\n";
        $script .= "var availCurrency = ".json_encode(getAllCurrencies()).";\n";
        $script .= "var dateFormat = '".$current_user->date_format."';\n";

        $this->addInlineJS($script);
    }

}

?>