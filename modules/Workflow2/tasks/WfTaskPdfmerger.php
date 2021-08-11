<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskPdfmerger extends \Workflow\Task
{
    public function init() {
        $this->addPreset("Attachments", "files", array(
            'module' => $this->getModuleName()
        ));

        $this->addPreset("FileActions", "resultaction", array(
            'module' => $this->getModuleName(),
        ));

    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        $files = json_decode($this->get("files"), true);
        if(is_array($files) && count($files) > 0) {
            // Module greifen auf Datenbank zurück. Daher vorher speichern!
            $context->save();

            foreach ($files as $key => $value) {
                if (is_string($value)) {
                    $value = array($value, false, array());
                }

                if (strpos($key, 's#') === 0) {
                    $tmpParts = explode('#', $key, 2);

                    $specialAttachments = \Workflow\Attachment::getAttachments($tmpParts[1], $value, $context, \Workflow\Attachment::MODE_NOT_ADD_NEW_ATTACHMENTS);

                    foreach ($specialAttachments as $attachment) {
                        if ($attachment[0] === 'ID') {
                            $tmp = \Workflow\VtUtils::getFileDataFromAttachmentsId($attachment[1]);
                            $workingFiles[] = array('path' => $tmp['path'], 'filename' => $tmp['filename']);
                        } elseif ($attachment[0] === 'PATH') {
                            $workingFiles[] = array('path' => $attachment[1], 'filename' => $attachment[2]['filename']);
                        }
                    }

                }
            }

            if(class_exists('\\setasign\\Fpdi\\Fpdi', false) === false) {
                $additionalDir = \Workflow\VtUtils::getAdditionalPath('pdfmerge');
                require_once($additionalDir . DS . 'vendor' . DS . 'autoload.php');
            }

            $pdf = new \setasign\Fpdi\Fpdi();

            $tmpfile = tempnam(sys_get_temp_dir(), 'WfTmp');
            @unlink($tmpfile);


            foreach($workingFiles as $file) {
                $pageCount = $pdf->setSourceFile($file['path']);
                for ($i = 0; $i < $pageCount; $i++) {
                    $tpl = $pdf->importPage($i + 1, '/MediaBox');

                    $size = $pdf->getTemplateSize($tpl);

                    if ($size['width'] > $size['height']) {
                        $pdf->AddPage('L', array($size['width'], $size['height']));
                    } else {
                        $pdf->AddPage('P', array($size['width'], $size['height']));
                    }

//                    $pdf->addPage();
                    $pdf->useTemplate($tpl);
                }
            }

            $pdf->Output('F', $tmpfile);
            if($this->notEmpty('filename')) {
                $filename = $this->get('filename');
                if(strpos($filename, '.pdf') === false) {
                    $filename .= '.pdf';
                }
            } else {
                $filename = 'merged.pdf';
            }

            \Workflow\FileAction::doActions($this->get('resultaction'), $tmpfile, $filename, $context, array(), $this->getWorkflow());

        }

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
