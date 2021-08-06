<?php

$Vtiger_Utils_Log = true;
chdir('../');

include_once 'include/database/PearDatabase.php';
include_once 'include/Webservices/Utils.php';

global $current_user, $adb;
$db = PearDatabase::getInstance();

//#1278 - registered new webservice api
        $operationName = 'retrievedocattachment';
        $handler_path = 'include/Webservices/RetrieveDocAttachment.php';
        $handler_method = 'vtws_retrievedocattachment';
        $operation_type = 'POST';

        $result = $db->pquery("SELECT 1 FROM vtiger_ws_operation WHERE name = ?", array($operationName));
        if(!$db->num_rows($result)) {
            $operationId = vtws_addWebserviceOperation($operationName, $handler_path, $handler_method, $operation_type);
            vtws_addWebserviceOperationParam($operationId, 'id', 'string', 1);
        }
        //4537596 - END


$adb->query("UPDATE `vtiger_ws_entity` SET
`handler_path` = 'include/Webservices/VtigerDocumentOperation.php',
`handler_class` = 'VtigerDocumentOperation'
WHERE `vtiger_ws_entity`.`name` = 'Documents'");

?>


