<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_DownloadTask_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $current_user, $root_directory;

        if(class_exists('\\ZipArchive') == false) {
            echo 'Pleae install php-zip extension to use this function.';
            exit();
        }
        $adb = PearDatabase::getInstance();
        $workflowID = intval($request->get("workflow"));

        $sql = 'SELECT * FROM vtiger_wf_types WHERE id = ?';
        $result = $adb->pquery($sql, array((int)$_REQUEST['task']));
        $taskData = $adb->fetchByAssoc($result);


        $taskDir = $root_directory.'/test/taskdir/';
        mkdir($taskDir);

        copy($root_directory.'/modules/Workflow2/tasks/'.$taskData['handlerclass'].'.php', $taskDir.'task.php');

        if(file_exists($root_directory.'/modules/Workflow2/tasks/'."WfTask".ucfirst(strtolower(str_replace("WfTask", "", $taskData["handlerclass"]))).'.js')) {
            copy($root_directory.'/modules/Workflow2/tasks/'."WfTask".ucfirst(strtolower(str_replace("WfTask", "", $taskData["handlerclass"]))).'.js', $taskDir.'task.js');
        }

        if(file_exists($root_directory.'/modules/Workflow2/icons/'.$taskData['background'].'.png')) {
            copy($root_directory.'/modules/Workflow2/icons/'.$taskData['background'].'.png', $taskDir.'icon.png');
        }

        if(file_exists($root_directory.'/layouts/v7/modules/Settings/Workflow2/taskforms/WfTask'.ucfirst(strtolower($taskData['type'])).'.tpl')) {
            copy($root_directory.'/layouts/v7/modules/Settings/Workflow2/taskforms/WfTask'.ucfirst(strtolower($taskData['type'])).'.tpl', $taskDir.'task.tpl');
        }
        if(file_exists($root_directory.'/layouts/v7/modules/Settings/Workflow2/taskforms/WfStat'.ucfirst(strtolower($taskData['type'])).'.tpl')) {
            copy($root_directory.'/layouts/v7/modules/Settings/Workflow2/taskforms/WfStat'.ucfirst(strtolower($taskData['type'])).'.tpl', $taskDir.'statistik.tpl');
        }

        $newzip = new \ZipArchive();
        $ret = $newzip->open($taskDir.'tmp.zip', ZipArchive::CREATE);

        $newzip->addFile($taskDir.'task.php',  'task.php');
        if(file_exists($taskDir.'icon.png')) {
            $newzip->addFile($taskDir.'icon.png',  'icon.png');
        }
        if(file_exists($taskDir.'task.js')) {
            $newzip->addFile($taskDir.'task.js',  'task.js');
        }
        if(file_exists($taskDir.'task.tpl')) {
            $newzip->addFile($taskDir.'task.tpl',  'task.tpl');
        }
        if(file_exists($taskDir.'statistik.tpl')) {
            $newzip->addFile($taskDir.'statistik.tpl',  'statistik.tpl');
        }

        $fh = fopen($taskDir.'task.xml', 'w+');
        $code = '<?xml version="1.0" encoding="UTF-8"?>
<task input="'.($taskData['input']=='1'?'true':'false').'" styleclass="'.$taskData['styleclass'].'" version="'.$taskData['version'].'">
  <name>'.$taskData['type'].'</name>
  <classname>'.$taskData['handlerclass'].'</classname>
  <label>'.$taskData['text'].'</label>
  <group>'.$taskData['category'].'</group>
  <outputs>
';
$outputs = @json_decode(html_entity_decode($taskData['output']), true);
foreach($outputs as $output) {

    $code .= '<output value="'.$output[0].'" text="'.$output[1].'">'.$output[1].'</output>'."\n";
}

$code .= '</outputs>
  <support_url>'.$taskData['helpurl'].'</support_url>
  <author>
    <name>Administrator</name>
    <email prefix="info" domain="domain.com" />
  </author>
</task>
';
        fwrite($fh, $code);
        $newzip->addFile($taskDir.'task.xml',  'task.xml');

        $newzip->close();

        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="'.strtolower($taskData['type']).'.zip"');

        readfile($taskDir.'tmp.zip');
    }
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}

