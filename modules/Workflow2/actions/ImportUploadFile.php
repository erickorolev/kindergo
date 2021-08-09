<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_ImportUploadFile_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        global $current_user;

        $ImportHash = $request->get('ImportHash');

        $objImporter = \Workflow\Importer::getInstance($ImportHash);

        if(!empty($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $objImporter = \Workflow\Importer::getInstance($ImportHash);

            @mkdir(vglobal('root_directory')."/test/Workflow2/", 0777, true);

            $filePath = tempnam(vglobal('root_directory')."/test/Workflow2/", "Importer");
            if(is_uploaded_file($_FILES["file"]["tmp_name"])) {
                move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
            } else {
                throw new \Exception('Error during Upload');
            }

            $objImporter->setFile($filePath);
        }

        // will be never arrived
    }
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}

?>