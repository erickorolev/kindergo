<?php

$Vtiger_Utils_Log = true;
chdir('../');

include_once 'include/database/PearDatabase.php';
include_once 'include/Webservices/Utils.php';

global $current_user, $adb;
$db = PearDatabase::getInstance();

//#1278 - registered new webservice api
        $operationName = 'files_retrieve';
        $handler_path = 'include/Webservices/FileRetrieve.php';
        $handler_method = 'vtws_file_retrieve';
        $operation_type = 'GET';

        $result = $db->pquery("SELECT 1 FROM vtiger_ws_operation WHERE name = ?", array($operationName));
        if(!$db->num_rows($result)) {
            $operationId = vtws_addWebserviceOperation($operationName, $handler_path, $handler_method, $operation_type);
            vtws_addWebserviceOperationParam($operationId, 'id', 'string', 1);
        }
        //4537596 - END

//image uitype added for webservice fieldtype
    $sql = 'INSERT INTO vtiger_ws_fieldtype(uitype,fieldtype) VALUES (?,?)';
    $params = array('69', 'image');
    $db->pquery($sql, $params);

?>


