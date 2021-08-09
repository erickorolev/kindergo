<?php
/**
This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

It belongs to the Workflow Designer and must not be distributed without complete extension
 * @version 1.2
 * @updated 2013-06-23
 **/
namespace Workflow;

use \Vtiger_Module;
use \Vtiger_Block;
use \Vtiger_Field;
use \stdClass;

class VtUtils
{
    protected static $UITypesName;
    public static $InventoryModules = array('SalesOrder', 'Invoice', 'Quotes', 'PurchaseOrder');

    /**
     * get all mandatory fields for one tabID
     *
     * @param int $tabid
     * @return array
     */
    public static function getMandatoryFields($tabid) {
        global $adb;

        $sql = "SELECT * FROM vtiger_field WHERE tabid = ".intval($tabid)." AND typeofdata LIKE '%~M%'";
        $result = $adb->query($sql);

        $mandatoryFields = array();
        while($row = $adb->fetchByAssoc($result)) {
            $typeofData = explode("~", $row["typeofdata"]);

            if($typeofData[1] == "M") {
                $mandatoryFields[] = $row;
            }
        }

        return $mandatoryFields;
    }

    public static function isInventoryModule($moduleName) {
        $mod = \Vtiger_Module_Model::getInstance($moduleName);
        return $mod instanceof  \Inventory_Module_Model;
    }

    public static function formatFilesize($size, $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'))
    {
        if ($size == 0) return('n/a');
        return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $sizes[$i]);
    }

    public static function num_rows($result) {
        $adb = \PearDatabase::getInstance();
        return $adb->num_rows($result);
    }

    // Function conv12to24hour Copyright by VtigerCRM Developers
    public static function conv12to24hour($timeStr){
        $arr = array();

        preg_match('/(\d{1,2}):(\d{1,2})(am|pm)/', $timeStr, $arr);
        if(empty($arr)) {
            return $timeStr;
        }

        if($arr[3]=='am'){
            $hours = ((int)$arr[1]) % 12;
        }else{
            $hours = ((int)$arr[1]) % 12 + 12;
        }
        return str_pad($hours, 2, '0', STR_PAD_LEFT).':'.str_pad($arr[2], 2, '0', STR_PAD_LEFT);
    }

    public static function generate_password($length = 20){
        $a = str_split("abcdefghijklmnopqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXY0123456789-_]}[{-_]}[{-_]}[{");
        shuffle($a);
        return substr( implode($a), 0, $length );
    }

    public static function encrypt($value) {
        if(!file_exists(MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'cryptkey.dat')) {
            file_put_contents(MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'cryptkey.dat', self::generate_password(50));
        }
        $key = file_get_contents(MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'cryptkey.dat');
        $traceData = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $key .= substr(md5(substr(md5(str_replace(vglobal('root_directory'), '', $traceData[0]['file'])), 0, 10)), 0, 5);

        $iv = 'abc123+=';
        $bf = new \Workflow\SWExtension\Crypt\Blowfish(\Workflow\SWExtension\Crypt\Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->paddable = true;
        $bf->setKey($key);

        return base64_encode($bf->encrypt(serialize($value)));
    }

    private static function decodeCrypt($rawSttings, $key) {
        $iv = 'abc123+=';
        $bf = new \Workflow\SWExtension\Crypt\Blowfish(\Workflow\SWExtension\Crypt\Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->paddable = true;
        $bf->setKey($key);

        $settings = $bf->decrypt($rawSttings);

        if(empty($settings)) {
            $bf = new \Workflow\SWExtension\Crypt\Blowfish(\Workflow\SWExtension\Crypt\Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);

            $settings = $bf->decrypt($rawSttings);
        }

        return trim($settings);
    }

    public static function decrypt($value) {
        if(!file_exists(MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'cryptkey.dat')) {
            throw new \Exception('Decryption could not be done, because cryptkey.dat file not existing!');
        }

        $key = file_get_contents(MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'cryptkey.dat');
        $traceData = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $key .= substr(md5(substr(md5(str_replace(vglobal('root_directory'), '', $traceData[0]['file'])), 0, 10)), 0, 5);

        return unserialize(self::decodeCrypt(base64_decode($value), $key));
    }

    /**
     * Enable Composer Autoloader
     * Example: for OAuth
     */
    public static function enableComposer() {
        require_once(MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
    }
    public static function getCurrentUserId() {
        global $current_user, $oldCurrentUser;

        if(!empty($oldCurrentUser)) {
            return $oldCurrentUser->id;
        } elseif(!empty($current_user)) {
            return $current_user->id;
        } else {
            return 0;
        }
    }
    public static function LastDBInsertID() {
        $adb = \PearDatabase::getInstance();

        $return = $adb->getLastInsertID();

        if(empty($return)) {
            if ($adb->isMySQL()) {
                $sql = 'SELECT LAST_INSERT_ID() as id';
                $result = $adb->query($sql, true);
                $return = $adb->query_result($result, 0, 'id');
            }
        }

        return $return;
    }

    public static function getLastComment($crmid) {
        $adb = \PearDatabase::getInstance();

        $sql = "SELECT commentcontent, userid, customer, first_name, last_name, createdtime
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE ( ((`vtiger_modcomments`.`related_to` = '".$crmid."')) ) AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_crmentity.crmid  ORDER BY createdtime DESC LIMIT 1";
        $result= $adb->query($sql);
        if($adb->num_rows($result) == 0) return '';

        $row = $adb->fetchByAssoc($result);

        $comment = (!empty($row['customer'])?\Vtiger_Functions::getCRMRecordLabel($row['customer']):$row["first_name"]." ".$row["last_name"])." - ".date("d.m.Y H:i:s", strtotime($row["createdtime"]));
        $comment .= PHP_EOL.'------';
        $comment .= PHP_EOL.$row["commentcontent"];

        return $comment;
    }
    public static function getComments($crmid, $limit = null) {
        $adb = \PearDatabase::getInstance();

        $sql = "SELECT commentcontent, userid, customer, first_name, last_name, createdtime
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE ( ((`vtiger_modcomments`.`related_to` = '".$crmid."')) ) AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_crmentity.crmid  ORDER BY createdtime DESC ".(!empty($limit)?' LIMIT '.$limit:'');
        $result= $adb->query($sql);
        if($adb->num_rows($result) == 0) return '';

        $comment = '';
        while($row = $adb->fetchByAssoc($result)) {
            $comment .= (!empty($row['customer'])?\Vtiger_Functions::getCRMRecordLabel($row['customer']):$row["first_name"]." ".$row["last_name"])." - ".date("d.m.Y H:i:s", strtotime($row["createdtime"]));
            $comment .= PHP_EOL.'------';
            $comment .= PHP_EOL.$row["commentcontent"].PHP_EOL.PHP_EOL;
        }


        return $comment;
    }
    /**
     * @param $sql
     * @param array $params
     * @param array $params2, ... [optional]
     * @return array
     */
    public static function fetchRows($sql, $params = array()) {
        if(!is_array($params)) {
            $args = func_get_args();
            array_shift($args);
            $params = $args;
        }

        $return = array();
        $result = self::pquery($sql, $params);

        while($row = self::fetchByAssoc($result)) {
            $return[] = $row;
        }
        return $return;
    }
    public static function fetchByAssoc($result, $params = array()) {
        if(is_string($result)) {
            if(!is_array($params)) {
                $args = func_get_args();
                array_shift($args);
                $params = $args;
            }

            $result = self::pquery($result, $params);
        }

        $adb = \PearDatabase::getInstance();
        return $adb->fetchByAssoc($result);
    }

    public static function query($sql) {
        $adb = \PearDatabase::getInstance();

        if($_COOKIE['stefanDebug'] == '1') {
            $debug = true;
        } else {
            $debug = false;
        }

        $return = $adb->query($sql, $debug);

        self::logSQLError($adb->database->errorMsg(), $sql);

        return $return;
    }
    public static function pquery($sql, $params) {
        $adb = \PearDatabase::getInstance();
        if($_COOKIE['stefanDebug'] == '1') {
            $debug = true;
        } else {
            $debug = false;
        }

        $return = $adb->pquery($sql, $params, $debug);

        self::logSQLError($adb->database->errorMsg(), $adb->convert2Sql($sql, $params));

        return $return;
    }

    public static function logSQLError($error, $sqlQuery = '') {
        if(!empty($error)) {
            \Workflow2::error_handler(E_ERROR, 'Database Error in Query '.$sqlQuery.' - '.$error, "", "");
        }
    }

    public static function str_replace_first($search, $replace, $subject) {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    /**
     * @param string $modulename
     * @param array $condition array(field => value, ...)
     */
    public static function findRecordIDs($modulename, $condition = array(), $returnQuery = false) {
        $adb = \PearDatabase::getInstance();
        if($modulename == 'Events') $modulename = 'Calendar';

        if(count($condition) == 0) {
            throw new \Exception('VtUtils::findRecords '.$modulename.' called without condition');
        }

        $searchSQL = 'SELECT vtiger_crmentity.crmid ' . self::getModuleTableSQL($modulename).' WHERE ';
        $fields = array();
        foreach($condition as $field => $value) {
            $fields[] = $field;
        }

        $sql = 'SELECT tablename, fieldname, columnname FROM vtiger_field WHERE tabid = ? AND fieldname IN ('.generateQuestionMarks($fields).')';

        array_unshift($fields, getTabid($modulename));

        $result = self::pquery($sql, $fields);

        $whereSQL = array();
        $params = array($modulename);
        $whereSQL[] = 'vtiger_crmentity.deleted = 0';
        $whereSQL[] = 'vtiger_crmentity.setype = ?';

        while($row = $adb->fetchByAssoc($result)) {
            $whereSQL[] = $row['tablename'].'.'.$row['columnname'].' = ?';
            $params[] = $condition[$row['fieldname']];
        }


        $searchSQL .= implode(' AND ', $whereSQL);

        if(ExecutionLogger::isInitialized()) {
            ExecutionLogger::getCurrentInstance()->log('Record Search Query');
            ExecutionLogger::getCurrentInstance()->log($adb->convert2Sql($searchSQL, $adb->flatten_array($params)));
        }

        if($returnQuery === true) {
            return array(
                'sql' => $searchSQL,
                'params' => $params
            );
        }

        $result = self::pquery($searchSQL, $params);

        $return = array();
        while($row = $adb->fetchByAssoc($result)) {
            $return[] = $row['crmid'];
        }

        return $return;
    }

    public static function getModuleNameForCRMID($crmid) {
        $adb = \PearDatabase::getInstance();

        $sql = "SELECT setype FROM vtiger_crmentity WHERE crmid=?";
        $result = $adb->pquery($sql, array($crmid));
        $data = $adb->fetchByAssoc($result);

        return $data['setype'];
    }

    public static function getModuleTableSQL($moduleName, $baseTableMode = 'from') {
        /**
         * @var $obj CRMEntity
         */
        $obj = \CRMEntity::getInstance($moduleName);
        $sql = array();

        if($baseTableMode == 'from') {
            $sql[] = "FROM " . $obj->table_name;
        } else {
            $sql[] = "INNER JOIN " . $obj->table_name.' ON ('.$obj->table_name.'.`'.$obj->table_index.'` = '.$baseTableMode.')';
        }

        $relations = $obj->tab_name_index;
        $pastJoinTables = array($obj->table_name);
        foreach($relations as $table => $index) {
            if(in_array($table, $pastJoinTables)) {
                continue;
            }

            $postJoinTables[] = $table;
            if($table == "vtiger_crmentity") {
                $join = "INNER";
            } else {
                $join = "LEFT";
            }

            $sql[] = $join." JOIN `".$table."` ON (`".$table."`.`".$index."` = `".$obj->table_name."`.`".$obj->table_index."`)";
        }

        return implode("\n", $sql);

    }

    /**
     * generate ColumnName from FieldName and tabid
     *
     * @param string $fieldname
     * @param int $tabid [optional]
     * @return mixed|string
     */
    public static function getColumnName($fieldname, $tabid = null) {
        global $adb;
        $sql = "SELECT columnname FROM vtiger_field WHERE fieldname = ?" . (!empty($tabid) ? ' AND tabid = '.$tabid : '');
        $result = $adb->pquery($sql, array($fieldname), true);

        if($adb->num_rows($result) == 0) {
            return $fieldname;
        }

        return $adb->query_result($result, 0, "columnname");
    }

    /**
     * generate ColumnName from FieldName and tabid
     *
     * @param string $fieldname
     * @param int $tabid [optional]
     * @return mixed|string
     */
    public static function getFieldName($columnname, $tabid = null) {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT fieldname FROM vtiger_field WHERE columnname = ?' . (!empty($tabid) ? ' AND tabid = '.$tabid : '');
        $result = $adb->pquery($sql, array($columnname));

        if($adb->num_rows($result) == 0) {
            return $columnname;
        }

        return $adb->query_result($result, 0, 'fieldname');
    }


    private static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false; //Invalid hex color code
        }
        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }

    public static function getGoodBorderColor($backgroundColor) {
        $rgb = self::hex2RGB($backgroundColor);

        $rgb['red'] -= 30;
        if($rgb['red'] < 0) $rgb['red'] = 0;

        $rgb['green'] -= 30;
        if($rgb['green'] < 0) $rgb['red'] = 0;

        $rgb['blue'] -= 30;
        if($rgb['blue'] < 0) $rgb['red'] = 0;

        return 'rgb('.$rgb['red'].','.$rgb['green'].','.$rgb['blue'].')';
    }
    public static function getTextColor($backgroundColor) {
        $rgb = self::hex2RGB($backgroundColor);
        $brightness = sqrt(
            $rgb["red"] * $rgb["red"] * .299 +
            $rgb["green"] * $rgb["green"] * .587 +
            $rgb["blue"] * $rgb["blue"] * .114);
//var_dump($backgroundColor, $brightness);
//        return $brightness;
        return ($brightness < 140) ? "#FFFFFF" : "#000000";
    }

    /**
     * Function returns all fielddata to the field from parameters
     *
     * @param string $fieldname The FieldName (NOT Columnname)
     * @param int [$tabid]
     * @return array|bool|null
     */
    public static function getFieldInfo($fieldname, $tabid = null) {
        global $adb;

        if($fieldname == 'crmid') {
            return array(
                'fieldlabel' => 'ID',
                'tablename' => 'vtiger_crmentity',
                'columnname' => 'crmid',
                'fieldname' => 'crmid',
            );
        }

        $sql = "SELECT * FROM vtiger_field WHERE fieldname = ?" . (!empty($tabid) ? ' AND tabid = '.$tabid : '');
        $result = $adb->pquery($sql, array($fieldname), true);

        if($adb->num_rows($result) == 0) {
            return false;
        }

        return $adb->fetchByAssoc($result);
    }

    public static function getFileDataFromAttachmentsId($attachmentsid) {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_attachments WHERE attachmentsid = ?';
        $result = $adb->pquery($sql, array($attachmentsid));

        $attachmentData = $adb->fetchByAssoc($result);

        $path = $attachmentData['path'] . intval($attachmentsid) . "_" . $attachmentData['name'];

        return array('path' => $path, 'filename' => $attachmentData['name']);
    }

    /**
     * Faster version to simple get fields for module and the fieldTypename if no uitype is filtered
     *
     * @param $module_name
     * @param bool $uitype [optional] If set, only return fields with this uitype and not fieldtype is returned
     * @return mixed
     */
    public static function getFieldsWithTypes($module_name, $uitype = false) {
        $adb = \PearDatabase::getInstance();

        if($uitype !== false && !is_array($uitype)) {
            $uitype = array($uitype);
        }

        $query = "SELECT columnname, uitype, fieldname, typeofdata FROM vtiger_field WHERE tabid = ? ".($uitype !== false ? ' AND uitype IN ('.implode(',', $uitype).')': '')." ORDER BY sequence";
        $queryParams = Array(getTabid($module_name));

        $result = $adb->pquery($query, $queryParams);
        $fields = array();

        while($valuemap = $adb->fetchByAssoc($result)) {
            $tmp = new \stdClass();

            $tmp->label = $valuemap['fieldlabel'];
            $tmp->name = $valuemap['fieldname'];
            $tmp->column = $valuemap['columnname'];

            if($uitype === false) {
                if(!isset($tmp->type)) {
                    $tmp->type = new \StdClass();
                }
                $tmp->type->name = self::getFieldTypeName(intval($valuemap['uitype']), $valuemap['typeofdata']);
            }

            $fields[$tmp->name] = $tmp;
        }

        return $fields;
    }

    private static $_FieldCache = array();
    public static function getFieldsForModule($module_name, $uitype = false) {
        global $current_language;

        if($uitype !== false && !is_array($uitype)) {
            $uitype = array($uitype);
        }

        $cacheKey = md5(serialize($uitype).$module_name);

        if(isset(self::$_FieldCache[$cacheKey])) {
            return unserialize(serialize(self::$_FieldCache[$cacheKey]));
        }

        $adb = \PearDatabase::getInstance();
        $query = "SELECT * FROM vtiger_field WHERE tabid = ? ORDER BY sequence";
        $queryParams = Array(getTabid($module_name));

        $result = $adb->pquery($query, $queryParams);
        $fields = array();

        while($valuemap = $adb->fetchByAssoc($result)) {
            $tmp = new \stdClass();
            $tmp->id = $valuemap['fieldid'];
            $tmp->name = $valuemap['fieldname'];
            $tmp->label= $valuemap['fieldlabel'];
            $tmp->column = $valuemap['columnname'];
            $tmp->table  = $valuemap['tablename'];
            $tmp->uitype = intval($valuemap['uitype']);
            $tmp->typeofdata = $valuemap['typeofdata'];
            $tmp->helpinfo = $valuemap['helpinfo'];
            $tmp->masseditable = $valuemap['masseditable'];
            $tmp->displaytype   = $valuemap['displaytype'];
            $tmp->generatedtype = $valuemap['generatedtype'];
            $tmp->readonly      = $valuemap['readonly'];
            $tmp->presence      = $valuemap['presence'];
            $tmp->defaultvalue  = $valuemap['defaultvalue'];
            $tmp->quickcreate = $valuemap['quickcreate'];
            $tmp->sequence = $valuemap['sequence'];
            $tmp->summaryfield = $valuemap['summaryfield'];

            $fields[] = $tmp;
        }

        $module = $module_name;
        if($module != "Events") {
//            $modLang = return_module_language($current_language, $module);
        }
        $moduleFields = array();

        /*
                // Fields in this module
                include_once("vtlib/Vtiger/Module.php");

                   #$alle = glob(dirname(__FILE__).'/functions/*.inc.php');
                   #foreach($alle as $datei) { include $datei; }


                   $instance = Vtiger_Module::getInstance($module);
                   //$blocks = Vtiger_Block::getAllForModule($instance);



                $fields = Vtiger_Field::getAllForModule($instance);
        */
        //$blocks = Vtiger_Block::getAllForModule($instance);
        if(is_array($fields)) {

            foreach($fields as $field) {
                if($uitype !== false && !in_array($field->uitype, $uitype)) {
                    continue;
                }

                $field->label = getTranslatedString((isset($modLang[$field->label])?$modLang[$field->label]:$field->label), $module_name);
                $field->type = new StdClass();
                $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);

                if($field->type->name == 'reference') {
                    $modules = self::getModuleForReference($field->block->module->id, $field->name, $field->uitype);

                    $field->type->refersTo = $modules;
                }
                if($field->type->name == 'picklist' || $field->type->name == 'multipicklist') {
                    $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                    if(empty($language)) {
                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                    }

                    if($field->uitype == 98) {
                        $query = "select * from vtiger_role";
                        $result = $adb->pquery($query, array());

                        while ($row = $adb->fetchByAssoc($result)) {
                            $field->type->picklistValues[$row['roleid']] = str_repeat('&nbsp;&nbsp;', $row['depth']).$row['rolename'];
                        }
                    } else {
                        switch ($field->name) {
                            case 'hdnTaxType':
                                $field->type->picklistValues = array(
                                    'group' => 'Group',
                                    'individual' => 'Individual',
                                );
                                break;
                            case 'email_flag':
                                $field->type->picklistValues = array(
                                    'SAVED' => 'SAVED',
                                    'SENT' => 'SENT',
                                    'MAILSCANNER' => 'MAILSCANNER',
                                );
                                break;
                            case 'currency_id':
                                $field->type->picklistValues = array();
                                $currencies = getAllCurrencies();
                                foreach ($currencies as $currencies) {
                                    $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                                }

                                break;
                            default:
                                $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                                break;
                        }
                    }

                    //$field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                }

                $moduleFields[] = $field;

            }

            if($uitype === false) {
                $crmid = new StdClass();
                $crmid->name = 'crmid';
                $crmid->label = 'ID';
                $crmid->type = 'string';
                $moduleFields[] = $crmid;
            }

        }

        self::$_FieldCache[$cacheKey] = $moduleFields;
//7f18c166060f17d0ce582a4359ad1cbc
        return unserialize(serialize($moduleFields));
    }
    public static function getReferenceFieldsForModule($module_name) {
        global $adb;
        $relations = array();
        $tabid = getTabID($module_name);
        if(empty($tabid)) return array();

        $sql = "SELECT tabid, fieldname, fieldlabel, uitype, fieldid FROM vtiger_field WHERE tabid = ".$tabid." AND (uitype = 10 OR uitype = 51 OR uitype = 101 OR uitype = 52 OR uitype = 53 OR uitype = 57 OR uitype = 58 OR uitype = 59 OR uitype = 73 OR uitype = 75 OR uitype = 76 OR uitype = 78 OR uitype = 80 OR uitype = 81 OR uitype = 68)";
        $result = $adb->query($sql);

        while($row = $adb->fetchByAssoc($result)) {
            switch ($row["uitype"]) {
                case "51":
                    $row["module"] = "Accounts";
                    $relations[] = $row;
                    break;
                case "52":
                    $row["module"] = "Users";
                    $relations[] = $row;
                    break;
                case "1024":
                    $row["module"] = "Users";
                    $relations[] = $row;
                    break;
                case "53":
                    $row["module"] = "Users";
                    $relations[] = $row;
                    break;
                case "57":
                    $row["module"] = "Contacts";
                    $relations[] = $row;
                    break;
                case "58":
                    $row["module"] = "Campaigns";
                    $relations[] = $row;
                    break;
                case "59":
                    $row["module"] = "Products";
                    $relations[] = $row;
                    break;
                case "73":
                    $row["module"] = "Accounts";
                    $relations[] = $row;
                    break;
                case "75":
                    $row["module"] = "Vendors";
                    $relations[] = $row;
                    break;
                case "81":
                    $row["module"] = "Vendors";
                    $relations[] = $row;
                    break;
                case "76":
                    $row["module"] = "Potentials";
                    $relations[] = $row;
                    break;
                case "78":
                    $row["module"] = "Quotes";
                    $relations[] = $row;
                    break;
                case "101":
                    $row["module"] = "Users";
                    $relations[] = $row;
                    break;
                case "80":
                    $row["module"] = "SalesOrder";
                    $relations[] = $row;
                    break;
                case "68":
                    $row["module"] = "Accounts";
                    $relations[] = $row;
                    $row["module"] = "Contacts";
                    break;
                case "10": # Possibly multiple relations
                    $resultTMP = \Workflow\VtUtils::pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', array($row["fieldid"]));
                    while ($data = $adb->fetch_array($resultTMP)) {
                        $row["module"] = $data["relmodule"];
                        $relations[] = $row;
                    }
                    break;
            }
        }

        return $relations;
    }
    public static $referenceUitypes = array(51,52,53,57,58,59,73,75,66,81,76,78,80,68,10,1024);

    public static function getModuleForReference($tabid, $fieldname, $uitype) {
        $addReferences = array();

        switch ($uitype) {
            case "51":
                $addReferences[] = "Accounts";
                break;
            case "1024":
            case "52":
                $addReferences[] = "Users";
                break;
            case "53":
                $addReferences[] = "Users";
                break;
            case "57":
                $addReferences[] = "Contacts";
                break;
            case "58":
                $addReferences[] = "Campaigns";
                break;
            case "59":
                $addReferences[] = "Products";
                break;
            case "66":
                $addReferences[] = "Accounts";
                $addReferences[] = "Leads";
                $addReferences[] = "Potentials";
                $addReferences[] = "HelpDesk";
                $addReferences[] = "Campaigns";
                break;
            case "73":
                $addReferences[] = "Accounts";
                break;
            case "75":
                $addReferences[] = "Vendors";
                break;
            case "81":
                $addReferences[] = "Vendors";
                break;
            case "76":
                $addReferences[] = "Potentials";
                break;
            case "78":
                $addReferences[] = "Quotes";
                break;
            case "80":
                $addReferences[] = "SalesOrder";
                break;
            case "68":
                $addReferences[] = "Accounts";
                $addReferences[] = "Contacts";
                break;
            case "10": # Possibly multiple relations
                global $adb;

                $sql = "SELECT fieldid FROM vtiger_field WHERE tabid = ".intval($tabid)." AND fieldname = ?";
                $result = $adb->pquery($sql, array($fieldname), true);

                $fieldid = $adb->query_result($result, 0, "fieldid");

                $result = \Workflow\VtUtils::pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', array($fieldid));
                while ($data = $adb->fetch_array($result)) {
                    $addReferences[] = $data["relmodule"];
                }
                break;
        }

        return $addReferences;
    }

    public static function getFieldTypeName($uitype, $typeofdata = false) {
        global $adb;
        switch($uitype) {
            case 117:
            case 115:
            case 15:
            case 16:
            case 98:
                return 'picklist';
                break;
            case 5:
            case 70:
            case 23:
                return 'date';
                break;
            case 6:
                return 'datetime';
                break;
            case 10:
            case 1024:
                return 'reference';
                break;
        }

        if(empty(self::$UITypesName)) {
            $result = self::query("select * from vtiger_ws_fieldtype");

            while($row = $adb->fetchByAssoc($result)) {
                self::$UITypesName[$row['uitype']] = $row['fieldtype'];
            }
        }

        if(!empty(self::$UITypesName[$uitype])) {
            return self::$UITypesName[$uitype];
        }

        $type = explode('~', $typeofdata);
        switch($type[0]){
            case 'T': return "time";
            case 'D':
            case 'DT': return "date";
            case 'E': return "email";
            case 'N':
            case 'NN': return "double";
            case 'P': return "password";
            case 'I': return "integer";
            case 'V':
            default: return "string";
        }

    }

    public static function getFieldsWithBlocksForModule($module_name, $references = false, $refTemplate = "([source]: ([module]) [destination])", $activityType = 'Event') {
        global $current_language, $adb, $app_strings;
        \Vtiger_Cache::$cacheEnable = false;

        $start = microtime(true);
        if(empty($refTemplate) && $references == true) {
            $refTemplate = "([source]: ([module]) [destination])";
        }
        //////echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        // Fields in this module
        include_once("vtlib/Vtiger/Module.php");

        #$alle = glob(dirname(__FILE__).'/functions/*.inc.php');
        #foreach($alle as $datei) { include $datei;		 }

        if($module_name == 'Calendar' && $activityType == 'Task') {
            $module_name = 'Events';
        }

        $tmpEntityModules = VtUtils::getEntityModules(false);
        $entityModules = array();
        foreach($tmpEntityModules as $tabid => $data) {
            $entityModules[$data[0]] = $data[0];
        }
        $module = $module_name;
        $instance = Vtiger_Module::getInstance($module);
        $blocks = Vtiger_Block::getAllForModule($instance);

        ////echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        if($module != "Events") {
            $langModule = $module;
        } else {
            $langModule = "Calendar";
        }

        $modLang = return_module_language($current_language, $langModule);
        //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $moduleFields = array();

        $addReferences = array();
        $referenceFields = array();

        if(is_array($blocks)) {
            foreach($blocks as $block) {
                /**
                 * @var $fields \Vtiger_Field
                 */
                $fields = Vtiger_Field::getAllForBlock($block, $instance);

                //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                if(empty($fields) || !is_array($fields)) {
                    continue;
                }

                foreach($fields as $field) {
                    if($field->presence == 1) continue;
                    if($field->name == 'eventstatus' && $module_name != 'Events') continue;
                    if($field->name == 'taskstatus' && $module_name != 'Calendar') continue;

                    $field->label = getTranslatedString($field->label, $langModule);
                    $field->type = new StdClass();

                    $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);

                    if($field->type->name == 'picklist' || $field->type->name == 'multipicklist') {
                        if($field->uitype == 98) {
                            $query = "select * from vtiger_role";
                            $result = $adb->pquery($query, array());

                            while ($row = $adb->fetchByAssoc($result)) {
                                $field->type->picklistValues[$row['roleid']] = str_repeat('&nbsp;', $row['depth']).$row['rolename'];
                            }
                        } else {
                            switch ($field->name) {
                                case 'hdnTaxType':
                                    $field->type->picklistValues = array(
                                        'group' => 'Group',
                                        'individual' => 'Individual',
                                    );
                                    break;
                                case 'email_flag':
                                    $field->type->picklistValues = array(
                                        'SAVED' => 'SAVED',
                                        'SENT' => 'SENT',
                                        'MAILSCANNER' => 'MAILSCANNER',
                                    );
                                    break;
                                case 'currency_id':
                                    $field->type->picklistValues = array();
                                    $currencies = getAllCurrencies();
                                    foreach ($currencies as $currencies) {
                                        $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                                    }

                                    break;
                                default:
                                    $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                                    if (empty($language)) {
                                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                                    }

                                    $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                                    break;
                            }
                        }
                    }

                    if($field->uitype == 26) {
                        $field->type->name = 'picklist';

                        $sql = 'SELECT * FROM vtiger_attachmentsfolder ORDER BY foldername';
                        $result = $adb->query($sql);

                        $field->type->picklistValues = array();
                        while($row = $adb->fetchByAssoc($result)) {
                            $field->type->picklistValues[$row['folderid']] = $row['foldername'];
                        }
                    }

                    if(in_array($field->uitype, self::$referenceUitypes)) {
                        $modules = self::getModuleForReference($field->block->module->id, $field->name, $field->uitype);

                        $field->type->refersTo = $modules;
                    }

                    if($field->type->name == 'reference') {
                        $field->label .= ' ID';
                        $referenceFields[] = $field;
                    }

                    if($references !== false) {

                        switch ($field->uitype) {
                            case "51":
                                $addReferences[] = array($field, "Accounts");
                                break;
                            case "52":
                                $addReferences[] = array($field, "Users");
                                break;
                            case "53":
                                $addReferences[] = array($field, "Users");
                                break;
                            case "57":
                                $addReferences[] = array($field, "Contacts");
                                break;
                            case "58":
                                $addReferences[] = array($field,"Campaigns");
                                break;
                            case "59":
                                $addReferences[] = array($field,"Products");
                                break;
                            case "73":
                                $addReferences[] = array($field,"Accounts");
                                break;
                            case "75":
                                $addReferences[] = array($field,"Vendors");
                                break;
                            case "81":
                                $addReferences[] = array($field,"Vendors");
                                break;
                            case "76":
                                $addReferences[] = array($field,"Potentials");
                                break;
                            case "78":
                                $addReferences[] = array($field,"Quotes");
                                break;
                            case "80":
                                $addReferences[] = array($field,"SalesOrder");
                                break;
                            case "66":
                                $addReferences[] = array($field,"Accounts");
                                $addReferences[] = array($field,"Leads");
                                $addReferences[] = array($field,"Potentials");
                                $addReferences[] = array($field,"HelpDesk");
                                $addReferences[] = array($field,"Campaigns");
                                break;
                            case "68":
                                $addReferences[] = array($field,"Accounts");
                                $addReferences[] = array($field,"Contacts");
                                break;
                            case "10": # Possibly multiple relations
                                $result = \Workflow\VtUtils::pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', array($field->id));
                                while ($data = $adb->fetch_array($result)) {
                                    $addReferences[] = array($field,$data["relmodule"]);
                                }
                                break;
                        }
                    }

                    $moduleFields[getTranslatedString($block->label, $langModule)][] = $field;
                }
            }

            $crmid = new StdClass();
            $crmid->name = 'crmid';
            $crmid->label = 'ID';
            $crmid->type = 'string';
            reset($moduleFields);
            $first_key = key($moduleFields);
            $moduleFields[$first_key] = array_merge(array($crmid), $moduleFields[$first_key]);

            if($module == 'Products' || $module == 'Services') {
                $currency_details = getAllCurrencies('all');
                foreach($currency_details as $currency) {
                    if($currency['currencylabel'] == vglobal('currency_name')) continue;
                    $field = new StdClass();
                    $field->name = "curname".$currency['curid'];
                    $field->label = getTranslatedString('Unit Price', $langModule).' '.$currency['currencycode'];
                    $field->type = 'currency';

                    array_push($moduleFields[getTranslatedString('LBL_PRICING_INFORMATION', $langModule)], $field);
                }
            }

            if(in_array($module, self::$InventoryModules)) {
                $crmid = new StdClass();
                $crmid->name = 'hdnS_H_Amount';
                $crmid->label = getTranslatedString("Shipping & Handling Charges", $module);
                $crmid->type = 'string';
                reset($moduleFields);
                $first_key = key($moduleFields);
                $moduleFields[$first_key] = array_merge($moduleFields[$first_key], array($crmid));
            }



        }
        //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $rewriteFields = array(
            "assigned_user_id" => "smownerid"
        );

        if($references !== false) {
            $field = new StdClass();
            $field->name = "current_user";
            $field->label = getTranslatedString("LBL_CURRENT_USER", "Workflow2");
            $addReferences[] = array($field, "Users");

            if(!empty($referenceFields)) {
                foreach($referenceFields as $refField) {
                    $crmid = new StdClass();
                    $crmid->name = str_replace(array("[source]", "[module]", "[destination]"), array($refField->name, 'ModuleName', 'ModuleName'), $refTemplate);
                    $crmid->label = $refField->label.' / Modulename';
                    $crmid->type->name = 'picklist';
                    $crmid->type->picklistValues = $entityModules;
                    reset($moduleFields);
                    $first_key = key($moduleFields);
                    $moduleFields[$first_key] = array_merge($moduleFields[$first_key], array($crmid));
                }
            }

        }

        if(is_array($addReferences)) {

            foreach($addReferences as $refField) {
                //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                $fields = self::getFieldsForModule($refField[1]);

                foreach($fields as $field) {
                    $field->label = "(".(isset($app_strings[$refField[1]])?$app_strings[$refField[1]]:$refField[1]).") ".$field->label;

                    if(!empty($rewriteFields[$refField[0]->name])) {
                        $refField[0]->name = $rewriteFields[$refField[0]->name];
                    }
                    $name = str_replace(array("[source]", "[module]", "[destination]"), array($refField[0]->name, $refField[1], $field->name), $refTemplate);
                    $field->name = $name;

                    $moduleFields["References (".$refField[0]->label.")"][] = $field;
                }
            }
        }

        \Vtiger_Cache::$cacheEnable = true;
        return $moduleFields;
    }

    /**
     * @return \Users
     */
    public static function getAdminUser() {
        return \Users::getActiveAdminUser();
    }

    public static function getEntityModules($sorted = false, $onlyInventoryModules = false) {
        global $adb;

        $tmpModules = \Vtiger_Module_Model::getAll(array(0,2));
        $module = array();
        foreach($tmpModules as $tmp) {
            if($tmp->isEntityModule()) {
                if($onlyInventoryModules && !$tmp instanceof \Inventory_Module_Model) {
                    continue;
                }

                if($tmp->name == 'Calendar') {
                    $label = 'LBL_TASK';
                } else {
                    $label = $tmp->label;
                }
                $module[$tmp->id] = array($tmp->name, getTranslatedString($label, $tmp->name));
            }
        }
        if($sorted == true) {
            asort($module);
        }
        return $module;
        /*
        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = \Workflow\VtUtils::query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            $module[$row["tabid"]] = array($row["name"], getTranslatedString($row["tablabel"], $row["name"]));
        }
        if($sorted == true) {
            asort($module);
        }
var_dump($module);
        return $module;*/
    }
    public static function initViewer($viewer) {

        $viewer->register_function('helpurl', array("\\Workflow\\VtUtils", 'Smarty_HelpURL'));

        return $viewer;
    }
    public static function Smarty_HelpURL($params, &$smarty) {
        if(empty($params["height"])) {
            $params["height"] = 18;
        } else {
            $params["height"] = intval($params["height"]);
        }
        return "<a href='http://support.stefanwarnat.de/en:extensions:".$params["url"]."' target='_blank'><img src='https://shop.stefanwarnat.de/help.png' style='margin-bottom:-".($params["height"] - 18)."px' border='0'></a>";
    }
    public static function getRelatedModules($module_name) {
        global $adb, $current_user, $app_strings;

        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        $sql = "SELECT vtiger_relatedlists.related_tabid,vtiger_relatedlists.label, vtiger_relatedlists.name, vtiger_tab.name as module_name FROM
                vtiger_relatedlists
                    INNER JOIN vtiger_tab ON(vtiger_tab.tabid = vtiger_relatedlists.related_tabid)
                WHERE vtiger_relatedlists.tabid = '".getTabId($module_name)."' AND related_tabid not in (SELECT tabid FROM vtiger_tab WHERE presence = 1) ORDER BY sequence, vtiger_relatedlists.relation_id";
        $result = \Workflow\VtUtils::query($sql);

        $relatedLists = array();
        while($row = $adb->fetch_array($result)) {

            // Nur wenn Zugriff erlaubt, dann zugreifen lassen
            if ($profileTabsPermission[$row["related_tabid"]] == 0) {
                if ($profileActionPermission[$row["related_tabid"]][3] == 0) {
                    $relatedLists[] = array(
                        "related_tabid" => $row["related_tabid"],
                        "module_name" => $row["module_name"],
                        "action" => $row["name"],
                        "label" => isset($app_strings[$row["label"]])?$app_strings[$row["label"]]:$row["label"],
                    );
                }
            }

        }

        return $relatedLists;
    }

    public static function getRelatedRecords($moduleName, $crmid, $relatedModuleName) {
        $parentRecordModel = \Vtiger_Record_Model::getInstanceById($crmid, $moduleName);

        /**
         * @var \Vtiger_RelationListView_Model $relatedListView
         */
        $relationListView = \Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);

        $query = $relationListView->getRelationQuery();

        $query = preg_replace('/SELECT(.+)FROM/imU', 'SELECT vtiger_crmentity.crmid FROM', $query);
        $adb = \PearDatabase::getInstance();

        $result = $adb->query($query);

        $records = array();
        while($row = $adb->fetchByAssoc($result)) {
            $records[] = $row['crmid'];
        }

        return $records;
    }

    private static $RecordDataCache = array();

    /**
     * @param string $moduleName
     * @param array $crmIds
     */
    public static function getMainRecordData($moduleName, $crmIds) {
        $adb = \PearDatabase::getInstance();
        if(count($crmIds) == 0) {
            return array();
        }
        $tabid = getTabId($moduleName);
        $focus = \CRMEntity::getInstance($moduleName);

        if(empty(self::$RecordDataCache[$moduleName])) {
            $sql = "SELECT * FROM vtiger_field WHERE tabid = ".$tabid." AND uitype = 4";
            $resultTMP = $adb->query($sql, true);

            if($adb->num_rows($resultTMP) > 0) {
                self::$RecordDataCache[$moduleName]["link_no"] = $adb->fetchByAssoc($resultTMP);
            } else {
                self::$RecordDataCache[$moduleName]["link_no"] = "no_available";
            }
        }

        $sql = "SELECT ".
            (self::$RecordDataCache[$moduleName]["link_no"] != "no_available" ? self::$RecordDataCache[$moduleName]["link_no"]["columnname"]." as nofield,":'')."
                vtiger_crmentity.crmid,
                vtiger_crmentity.label
                FROM vtiger_crmentity
                    ".(self::$RecordDataCache[$moduleName]["link_no"] != "no_available" ? "INNER JOIN ".self::$RecordDataCache[$moduleName]["link_no"]["tablename"]." ON (".$focus->table_index." = vtiger_crmentity.crmid)":'')."
                WHERE vtiger_crmentity.crmid IN (".implode(',',$crmIds).')';
        $result = $adb->query($sql, true);

        $recordData = array();

        while($row = $adb->fetchByAssoc($result)) {
            $recordData[$row['crmid']] = array(
                'link' => 'index.php?module='.$moduleName.'&view=Detail&record='.$row['crmid'],
                'label' => $row['label'],
                'crmid' => $row['crmid'],
                'number' => empty($row['nofield']) ? $row['crmid'] : $row['nofield']
            );
        }

        return $recordData;
    }

    public static function getModuleName($tabid) {
        global $adb;

        $sql = "SELECT name FROM vtiger_tab WHERE tabid = ".intval($tabid);
        $result = $adb->query($sql);

        return $adb->query_result($result, 0, "name");
    }

    public static function formatUserDate($date) {
        return \DateTimeField::convertToUserFormat($date);
    }

    public static function convertToUserTZ($date) {
        if(class_exists("DateTimeField")) {
            $return = \DateTimeField::convertToUserTimeZone($date);
            return $return->format("Y-m-d H:i:s");
        } else {
            return $date;
        }
    }

    public static function describeModule($moduleName, $loadReferences = false, $nameFormat = "###") {
        global $current_user;
        $columnsRewrites = array(
            "assigned_user_id" => "smownerid"
        );
        $loadedRefModules = array();

        require_once("include/Webservices/DescribeObject.php");
        $refFields = array();
        $return = array();
        $describe = vtws_describe($moduleName, $current_user);

        $return["crmid"] = array(
            "name" => "crmid",
            "label" => "ID",
            "mandatory" => false,
            "type" => array("name" => "string"),
            "editable" => false
        );

        /** Current User mit aufnehmen! */
        $describe["fields"][] = array ( 'name' => 'current_user', 'label' => 'current user ', 'mandatory' => false, 'type' => array ( 'name' => 'reference', 'refersTo' => array ( 0 => 'Users' ) ) );

        foreach($describe["fields"] as $field) {
            if(!empty($columnsRewrites[$field["name"]])) {
                $field["name"] = $columnsRewrites[$field["name"]];
            }
            if($field["name"] == "smownerid") {
                $field["type"]["name"] = "reference";
                $field["type"]["refersTo"] = array("Users");
            }

            if($field["type"]["name"] == "reference" && $loadReferences == true) {
                foreach($field["type"]["refersTo"] as $refModule) {
                    #if(!empty($loadedRefModules[$refModule])) continue;

                    $refFields = array_merge($refFields, self::describeModule($refModule, false, "(".$field["name"].": (".$refModule.") ###)"));

                    #var_dump($refFields);
                    $loadedRefModules[$refModule] = "1";
                }
            }

            $fieldName = str_replace("###", $field["name"], $nameFormat);

            $return[$fieldName] = $field;

        }

        /** Assigned Users */
        global $adb;
        $availUser = array('user' => array(), 'group' => array());
        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);
        while($user = $adb->fetchByAssoc($result)) {
            $user["id"] = "19x".$user["id"];
            $availUser["user"][] = $user;
        }
        $sql = "SELECT * FROM vtiger_groups ORDER BY groupname";
        $result = $adb->query($sql);
        while($group = $adb->fetchByAssoc($result)) {
            $group["groupid"] = "20x".$group["groupid"];
            $availUser["group"][] = $group;
        }
        /** Assigned Users End */

        $return["assigned_user_id"]["type"]["name"] = "picklist";
        $return["assigned_user_id"]["type"]["picklistValues"] = array();

        $return["assigned_user_id"]["type"]["picklistValues"][] = array("label" => '$currentUser', "value" => '$current_user_id');

        for($a = 0; $a < count($availUser["user"]); $a++) {
            $return["assigned_user_id"]["type"]["picklistValues"][] = array("label" => $availUser["user"][$a]["user_name"], "value" => $availUser["user"][$a]["id"]);
        }
        for($a = 0; $a < count($availUser["group"]); $a++) {
            $return["assigned_user_id"]["type"]["picklistValues"][] = array("label" => "Group: " . $availUser["group"][$a]["groupname"], "value" => $availUser["group"][$a]["groupid"]);
        }

        $return["smownerid"] = $return["assigned_user_id"];


        $return = array_merge($return, $refFields);

        return $return;
    }

    public static function existTable($tableName) {
        return \Workflow\DbCheck::existTable($tableName);
    }
    public function checkColumn($table, $colum, $type, $default = false) {
        global $adb;

        $result = $adb->query("SHOW COLUMNS FROM `".$table."` LIKE '".$colum."'");
        $exists = ($adb->num_rows($result))?true:false;

        if($exists == false) {
            echo "Add column '".$table."'.'".$colum."'<br>";
            $adb->query("ALTER TABLE `".$table."` ADD `".$colum."` ".$type." NOT NULL".($default !== false?" DEFAULT  '".$default."'":""));
        }

        return $exists;

    }

    public static function is_utf8($str){
        $strlen = strlen($str);
        for($i=0; $i<$strlen; $i++){
            $ord = ord($str[$i]);
            if($ord < 0x80) continue; // 0bbbbbbb
            elseif(($ord&0xE0)===0xC0 && $ord>0xC1) $n = 1; // 110bbbbb (exkl C0-C1)
            elseif(($ord&0xF0)===0xE0) $n = 2; // 1110bbbb
            elseif(($ord&0xF8)===0xF0 && $ord<0xF5) $n = 3; // 11110bbb (exkl F5-FF)
            else return false; // ungültiges UTF-8-Zeichen
            for($c=0; $c<$n; $c++) // $n Folgebytes? // 10bbbbbb
                if(++$i===$strlen || (ord($str[$i])&0xC0)!==0x80)
                    return false; // ungültiges UTF-8-Zeichen
        }
        return true; // kein ungültiges UTF-8-Zeichen gefunden
    }

    public static function decodeExpressions($expression) {
        $expression = preg_replace_callback('/\\$\{(.*)\}\}&gt;/s', array("VtUtils", "_decodeExpressions"), $expression);

        return $expression;
    }
    public static function maskExpressions($expression) {
        $expression = preg_replace_callback('/\\$\{(.*)\}\}>/s', array("VtUtils", "_maskExpressions"), $expression);

        return $expression;
    }
    protected static function _maskExpressions($match) {
        return '${ ' . htmlentities(($match[1])) . ' }}>';
    }
    protected static function _decodeExpressions($match) {
        return '${ ' . html_entity_decode(htmlspecialchars_decode($match[1])) . ' }}>';
    }

    // copyright MangaII  http://php.net/manual/en/reserved.variables.httpresponseheader.php
    public static function parseHeaders( $headers )
    {
        $head = array();
        foreach( $headers as $k=>$v )
        {
            $t = explode( ':', $v, 2 );
            if( isset( $t[1] ) )
                $head[ trim($t[0]) ] = trim( $t[1] );
            else
            {
                $head[] = $v;
                if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                    $head['response_code'] = intval($out[1]);
            }
        }
        return $head;
    }
    // Copyright: https://gist.github.com/mohsinrasool/50e0f43af626867dd05c
    public static function http_build_query_for_curl( $arrays, &$new = array(), $prefix = null ) {

        if ( is_object( $arrays ) ) {
            $arrays = get_object_vars( $arrays );
        }

        foreach ( $arrays AS $key => $value ) {
            $k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
            if ( is_array( $value ) OR is_object( $value )  ) {
                self::http_build_query_for_curl( $value, $new, $k );
            } else {
                $new[$k] = $value;
            }
        }
    }

    public static function getContentFromUrl($url, $params = array(), $method = 'auto', $options = array()) {
        if(defined('DEV_OFFLINEMODE')) return 'OFFLINE';

        $method = strtolower($method);
        $userpwd = $bearer = false;
        $header = array();
        if(!empty($options['headers'])) {
            $header = $options['headers'];
        }

        if(!empty($_COOKIE['REQUEST_DEBUG'])) {
            $options['debug'] = true;
        }

        if(!empty($options['auth']['user']) && !empty($options['auth']['password'])) {
            $userpwd = $options['auth']['user'].':'.$options['auth']['password'];
        }
        if(!empty($options['auth']['bearer'])) {
            $authorization = "Authorization: Bearer ".$options['auth']['bearer'];
            $header[] = $authorization;
        }

        if(empty($options['successcode'])) $options['successcode'] = array(200);
        if(!is_array($options['successcode'])) {
            $options['successcode'] = array($options['successcode']);
        }
        if(!empty($_COOKIE['CONTENT_DEBUG'])) {
            $options['debug'] = true;
        }

        if (function_exists('curl_version') && ($method == 'auto' || $method == 'post'))
        {
            $curl = curl_init();
            #curl_setopt($curl, 	CURLOPT_HEADER, 1);

            curl_setopt($curl, 	CURLOPT_URL, $url);
            curl_setopt($curl, 	CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl,  CURLOPT_REFERER, vglobal('site_URL'));

            if($method == 'post' || !empty($params)) {
                curl_setopt($curl, CURLOPT_POST, count($params));
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            }

            $urlParts = parse_url($url);
            $ip = gethostbyname($urlParts['host']);
            if(strpos($url, 'https://') === false) {
                $port = 80;
            } else {
                $port = 443;
                if(defined('CURLOPT_IPRESOLVE')) {
                    curl_setopt($curl, CURLOPT_SSLVERSION, 1);
                }
            }

            if(defined('CURLOPT_IPRESOLVE')) {
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            if(defined('CURLOPT_RESOLVE')) {
                curl_setopt($curl, CURLOPT_RESOLVE, array($urlParts['host'] . ":" . $port . ":" . $ip));
            }

            if(!empty($options['cainfo'])) {
                curl_setopt($curl, 	CURLOPT_CAINFO, $options['cainfo']);
            } else {
                curl_setopt($curl, 	CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cacert.pem');
            }
            if(!empty($options['capath'])) {
                curl_setopt($curl, 	CURLOPT_CAPATH, $options['capath']);
            }

            if(!empty($options['debug'])) {
                curl_setopt($curl, 	CURLOPT_VERBOSE, 1);

                $verbose = fopen('php://temp', 'w+');
                curl_setopt($curl, CURLOPT_STDERR, $verbose);

            }

            if($userpwd !== false) {
                curl_setopt($curl,	CURLOPT_USERPWD, $userpwd);
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, 	CURLOPT_FOLLOWLOCATION, true);

            $content = curl_exec($curl);

            if(!empty($options['debug'])) {
                var_dump('URL: '.$url);
                var_dump('Parameters: ', $params);
                echo 'Response:'.PHP_EOL;
                var_dump($content);

                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);

                echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
                unlink($verbose);
            }
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if(!empty($responseCode) && !in_array($responseCode, $options['successcode'])) {
                throw new \Exception('Error Code '.$responseCode.' - '.$content, $responseCode);
            }

            curl_close($curl);
        }
        else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen') && ($method == 'auto' || $method == 'get'))
        {
            if(count($params) > 0) {
                $query = http_build_query($params);
                if(strpos($url, '?') === false) {
                    $url .= '?'.$query;
                } else {
                    $url .= '&'.$query;
                }
            }
            if(!empty($userpwd)) {
                $header[] = "Authorization: Basic " . base64_encode($userpwd);
            }

            $context = stream_context_create(array(
                'http' => array(
                    'header'  => $header
                )
            ));

            $content = file_get_contents($url, false, $context);

            if(!empty($options['debug'])) {
                echo 'Response:'.PHP_EOL;
                var_dump($content);
            }

            $header = self::parseHeaders($http_response_header);

            if(!in_array($header['response_code'], $options['successcode'])) {
                throw new \Exception('Error Code '.$header['response_code'].' - '.$context, $header['response_code']);
            }
        }
        else
        {
            throw new \Exception('You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!');
        }
        return $content;
    }

    public static function json_encode($value) {
        $result = json_encode($value);

        if(empty($result) && !empty($value)) {
            \Zend_Json::$useBuiltinEncoderDecoder = true;
            $result = \Zend_Json::encode($value);
        }

        if(empty($result) && !empty($value)) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;
            $result = \Zend_Json::encode($value);
        }

        return $result;
    }
    public static function json_decode($value) {
        $result = json_decode($value, true);

        if(empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;
            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {}
        }

        if(empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = true;
            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {}
        }

        if(empty($result) && strlen($value) > 4) {
            // Decode HTML Entities
            $value = html_entity_decode($value, ENT_QUOTES);

            \Zend_Json::$useBuiltinEncoderDecoder = true;
            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {}
        }

        if(empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;
            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {}
        }

        return $result;
    }

    public static function getAdditionalPath($key, $relativeToRoot = false) {
        $key = preg_replace('[^a-zA-Z0-9-_]', '_', $key);

        $return = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'extends' . DIRECTORY_SEPARATOR . 'additionally' . DIRECTORY_SEPARATOR . '' . $key) . DIRECTORY_SEPARATOR;

        if($relativeToRoot === true) {
            $return = str_replace(vglobal('root_directory'), '', $return);
        }

        return $return;
    }

    public static function getRecordId($idOrText, $moduleArray) {
        if(strpos($idOrText, "x") !== false) {
            $idParts = explode("x", $idOrText);
            return $idParts[1];
        }

        if(strpos($idOrText, "@") !== false) {
            $id = explode("@", $idOrText);
            return $id[0];
        }

        if(is_numeric($idOrText)) {
            return $idOrText;
        }

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_crmentity WHERE setype IN ('.generateQuestionMarks($moduleArray).') AND label = ? AND deleted = 0';

        $result = $adb->pquery($sql, array($moduleArray, $idOrText));

        if($adb->num_rows($result) > 0) {
            return $adb->query_result($result, 0, 'crmid');
        }
    }
}
