<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 02.08.2016
 * Time: 16:53
 */

$sql = 'SELECT COUNT(*) as num FROM vtiger_eventhandlers WHERE handler_path = "modules/Workflow2/WfEventHandler.php" AND is_active = 1';
$result = $adb->query($sql);
if($adb->query_result($result, 0, 'num') < 3) {
    $viewer->assign("SHOW_EVENT_NOTICE", true);
} else {
    $viewer->assign("SHOW_EVENT_NOTICE", false);
}
