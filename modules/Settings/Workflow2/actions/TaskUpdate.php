<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskUpdate_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $params = $request->getAll();

        $type_id = (int)$request->get('type_id');
        $skipSignatureCheck = (int)$request->get('skipSignatureCheck');

        $sql = 'SELECT * FROM vtiger_wf_repository_types WHERE id = '.$type_id;
        $result = $adb->query($sql);

        if($adb->num_rows($result) > 0) {
            $data = $adb->fetchByAssoc($result);
            $fileDownloadUrl = $data['url'];

            $content = VtUtils::getContentFromUrl(html_entity_decode($fileDownloadUrl));

            $tmpfname = tempnam(sys_get_temp_dir(), 'WFD2');

            file_put_contents($tmpfname, $content);

            if (!$skipSignatureCheck && false == \Workflow\Repository::checkSignature($tmpfname, $data['repos_id'], $data['checksum'])) {
                @ob_end_flush();
                echo 'checksum';
                @unlink($tmpfname);
                return;
            }

            $allowDownGrade = false;
            if(!empty($_POST['allowDowngrade']) && $_POST['allowDowngrade'] == '1') {
                $allowDownGrade = true;
            }

            \Workflow\Repository::installFile($tmpfname, $data['version'], $data['repos_id'], true, $allowDownGrade);
            @unlink($tmpfname);
        }

    }


}