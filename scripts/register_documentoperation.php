<?php

$Vtiger_Utils_Log = true;
chdir('../');

include_once 'include/database/PearDatabase.php';
include_once 'include/Webservices/Utils.php';

global $current_user, $adb;

$operationInfo = array(
    'name'    => 'retrievedocattachment',
    'include' => 'include/Webservices/RetrieveDocAttachment.php',
    'handler' => 'vtws_retrievedocattachment',
    'prelogin'=> 0,
    'type'    => 'POST',
    'parameters' => array(
        array('name' => 'id','type' => 'string'),
        array('name' => 'returnfile','type' => 'string'),
    )
);



$adb->query("UPDATE `vtiger_ws_entity` SET
`handler_path` = 'include/Webservices/VtigerDocumentOperation.php',
`handler_class` = 'VtigerDocumentOperation'
WHERE `vtiger_ws_entity`.`name` = 'Documents'");

?>


