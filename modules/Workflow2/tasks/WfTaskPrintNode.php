<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskPrintNode extends \Workflow\Task
{
    private $request = null;

    public function init() {
		
		$apiKey = $this->get('apikey');
        if(!empty($apiKey ) && $apiKey != -1) {
            try {
                if (!empty($this))
                    $this->addPreset("Attachments", "files", array(
                        'module' => $this->getModuleName()
                    ));

                $additionalPath = $this->getAdditionalPath('c_printnode');

                \Workflow2\Autoload::register("PrintNode", $additionalPath);

                $credentials = new \PrintNode\ApiKeyCredentials(
                    trim($apiKey)
                );

                $this->request = new \PrintNode\Request($credentials);
            } catch (\Exception $exp) {
                $this->addConfigHint($exp->getMessage());
            }
        }
		
		/* Insert here source code to execute the task */

    }
    public function handleTask(&$context) {
        try {
            $computers = $this->request->getComputers();
            $printers = $this->request->getPrinters();
        } catch (\Exception $exp) {
            $this->addConfigHint($exp->getMessage());
        }

        $workingFiles = array();
        $files = json_decode($this->get("files"), true);
        if(is_array($files) && count($files) > 0) {
            // Module greifen auf Datenbank zurück. Daher vorher speichern!
            $context->save();

            foreach($files as $key => $value) {
                if(is_string($value)) { $value = array($value, false, array()); }

                if(strpos($key, 's#') === 0) {
                    $tmpParts = explode('#', $key, 2);

                    $specialAttachments = \Workflow\Attachment::getAttachments($tmpParts[1], $value, $context, \Workflow\Attachment::MODE_NOT_ADD_NEW_ATTACHMENTS);

                    foreach($specialAttachments as $attachment) {
                        if($attachment[0] === 'ID') {
                            $tmp = \Workflow\VtUtils::getFileDataFromAttachmentsId($attachment[1]);
                            $workingFiles[] = array('path' => $tmp['path'], 'filename' => $tmp['filename']);
                        } elseif($attachment[0] === 'PATH') {
                            $workingFiles[] = array('path' => $attachment[1], 'filename' => $attachment[2]['filename']);
                        }
                    }

                }
            }

            if(count($workingFiles) > 0) {
                $printersource = $this->get('printersource');
                switch($printersource) {
                    case 'dynamic':
                        $printerId = $this->get('printerdynamic', $context);
                        break;
                    case 'static':
                    default:
                        $printerId = $this->get('printer');
                        break;
                }

                $capabilities = $this->get('capability');
                $capabilityOption = array();

                foreach($workingFiles as $file) {
					//
					$printJob = new PrintNode\PrintJob();

					$printJob->printer = $printerId;
					$printJob->contentType = 'pdf_base64';
					$printJob->content = base64_encode(file_get_contents($file['path']));
					$printJob->source = 'VtigerCRM/1.2';
					$printJob->title = 'VtigerCRM Print '.$file['filename'];					
                    //$resarray = $this->gcp->sendPrintToPrinter($printer, 'VtigerCRM Print '.$file['filename'], $file['path'], "application/pdf", json_encode($capabilityOption));

					$response = $this->request->post($printJob);
					// The response returned from the post method is an instance of PrintNode\Response. 
					// It contains methods for retrieving the response headers, body and HTTP status-code and message.
					// Returns the HTTP status code.
					$statusCode = $response->getStatusCode();
					// Returns the HTTP status message.
					$statusMessage = $response->getStatusMessage();					
					// Returns an array of HTTP headers.
					$headers = $response->getHeaders();
					// Return the response body.
					$content = $response->getContent();					
                }
            }
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $apiKey = $this->get('apikey');
        try {
            if(!empty($apiKey) && $apiKey != -1) {
                $computers = $this->request->getComputers();
                $printers = $this->request->getPrinters();

                $printerList = array();
                foreach($computers as $computer) {
                    $printerList = array();
                    foreach($printers as $printer) {

                        if($printer->computer->id == $computer->id) {
                            $printerList[$printer->id] = $printer->name.' ('.$printer->state.')';
                        }
                    }

                    asort($printerList);
                    $printerResult[$computer->name] = $printerList;
                }

                $viewer->assign('printers', $printerResult);
            }
        } catch (\Exception $exp) {
            $this->addConfigHint($exp->getMessage());
        }


/*
        foreach($printers as $index => $printer) {
            if($printer['id'] == '__google__docs') {
                unset($printers[$index]);
            }
        }
        $viewer->assign('printer', $printers);

        $printerId = $this->get('printer');

        if($printerId != -1 && !empty($printerId)) {
        
        }
*/
		
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
