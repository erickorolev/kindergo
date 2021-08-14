<?php

$Vtiger_Utils_Log = true;
chdir('../');

include_once 'include/database/PearDatabase.php';
include_once 'include/Webservices/Utils.php';

global $current_user, $adb;
$db = PearDatabase::getInstance();

$operationName = 'gettranslation';
$handler_path = 'include/Webservices/GetTranslation.php';
$handler_method = 'vtws_gettranslation';
$operation_type = 'POST';

$result = $db->pquery("SELECT 1 FROM vtiger_ws_operation WHERE name = ?", array($operationName));
if (!$db->num_rows($result)) {
    $operationId = vtws_addWebserviceOperation($operationName, $handler_path, $handler_method, $operation_type);
    vtws_addWebserviceOperationParam($operationId, 'totranslate', 'encoded', 0);
    vtws_addWebserviceOperationParam($operationId, 'language', 'string', 0);
    vtws_addWebserviceOperationParam($operationId, 'module', 'string', 0);
}
echo 'DONE!';
?>


