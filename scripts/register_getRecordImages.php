<?php

$Vtiger_Utils_Log = true;
chdir('../');

include_once 'include/database/PearDatabase.php';
include_once 'include/Webservices/Utils.php';

global $current_user, $adb;
$db = PearDatabase::getInstance();

//#1278 - registered new webservice api
        $operationName = 'getRecordImages';
        $handler_path = 'include/Webservices/getRecordImages.php';
        $handler_method = 'cbws_getrecordimageinfo';
        $operation_type = 'GET';

        $result = $db->pquery("SELECT 1 FROM vtiger_ws_operation WHERE name = ?", array($operationName));
        if(!$db->num_rows($result)) {
            $operationId = vtws_addWebserviceOperation($operationName, $handler_path, $handler_method, $operation_type);
            vtws_addWebserviceOperationParam($operationId, 'id', 'string', 1);
        }
        //4537596 - END

?>


