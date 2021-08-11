<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskGoogleCloudPrint extends \Workflow\Task
{
    private $gcp = null;

    public function init() {
        $this->addPreset("Attachments", "files", array(
            'module' => $this->getModuleName()
        ));

		/* Insert here source code to execute the task */
        $additionalPath = $this->getAdditionalPath('c_googlecloudprint');

        global $currentBlockID;
        $currentBlockID = $this->getBlockId();

        $urlconfig = $redirectConfig = $offlineAccessConfig = array();

        require_once($additionalPath."/Config.php");
        require_once($additionalPath."/GoogleCloudPrint.php");

        $tokenFilename = $additionalPath."token/tokenSession3_".$currentBlockID;

        $this->gcp = new GoogleCloudPrint();

        if(!empty($_REQUEST["code"])) {
            $code = $_REQUEST['code'];
            $authConfig['code'] = $code;
            $responseObj = $this->gcp->getAccessToken($urlconfig['accesstoken_url'], $authConfig);

            file_put_contents($tokenFilename, serialize(array('accessToken' => $responseObj)));
            echo '<script type="text/javascript">window.location.href="index.php?module=Workflow2&parent=Settings&view=TaskConfig&taskid='.$this->getBlockId().'&done=1";</script>';
            exit();
        }

        if(file_exists($tokenFilename)) {
            $sessionToken = unserialize(file_get_contents($tokenFilename));

            if(!empty($sessionToken['accessToken']->refresh_token)) {
                $refreshTokenConfig['refresh_token'] = $sessionToken['accessToken']->refresh_token;

                $accessToken = $this->gcp->getAccessTokenByRefreshToken($urlconfig['refreshtoken_url'], http_build_query($refreshTokenConfig));

                if(empty($accessToken)) {
                    $accessToken = $sessionToken['accessToken']->access_token;
                } else {
                    $sessionToken['accessToken']->access_token = $accessToken;
                    file_put_contents($tokenFilename, serialize($sessionToken));
                }

                $this->gcp->setAuthToken($accessToken);
            } else {
                $this->gcp->setAuthToken($sessionToken['accessToken']->access_token);
            }
        } else {
            $sessionToken = "";
        }

        if(empty($sessionToken)) {
            if(function_exists('csrf_get_tokens')) {
                $csrf = "<input type='hidden' name='".$GLOBALS['csrf']['input-name']."' value='".csrf_get_tokens()."' />";
            } else {
                $csrf = '';
            }

            echo '<script type="text/javascript">window.open("'.$urlconfig['authorization_url']."?".http_build_query(array_merge($redirectConfig,$offlineAccessConfig)).'");</script>';
            echo '<div style="text-align:center;margin:40px 0;">',getTranslatedString('Because of Login Restrictions, you need to do the Login and Authorization within the PopUp and copy the Code you get in this Textfield.', 'Settings:Workflow2');
            echo '<form method="POST" action="#">'.$csrf.'<br/><input type="text" style="width:400px;" name="code"><br/><input type="submit" class="btn  btn-primary" name="submit" value="Submit the Code & Unlock Google Calendar Access" /> </form>';
            exit();

        }
    }
    public function handleTask(&$context) {
		$printer = $this->get('printer');

        if(empty($printer) || $printer == -1) {
            $this->addStat('You must configure printer before you could use this block!');
            return "yes";
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
                $capabilities = $this->get('capability');
                $capabilityOption = array();

                foreach($capabilities as $capability => $value) {
                    switch($capability) {
                        case 'page_orientation':
                            $capabilityOption['page_orientation']['type'] = $value;
                            break;
                        case 'duplex':
                            $capabilityOption['duplex']['type'] = $value;
                            break;
                    }
                }

                foreach($workingFiles as $file) {

                    $resarray = $this->gcp->sendPrintToPrinter($printer, 'VtigerCRM Print '.$file['filename'], $file['path'], "application/pdf", json_encode($capabilityOption));

                }
            }
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $printers = $this->gcp->getPrinters();

        foreach($printers as $index => $printer) {
            if($printer['id'] == '__google__docs') {
                unset($printers[$index]);
            }
        }
        $viewer->assign('printer', $printers);

        $printerId = $this->get('printer');

        if($printerId != -1 && !empty($printerId)) {
            $printerData = $this->gcp->getPrinterInfo($printerId);

            $capabilities = $printerData['printers'][0]['capabilities']['printer'];

            $capabilityOptions = array();

            if(!empty($capabilities['page_orientation'])) {
                $capabilityOptions['page_orientation'] = array();
                foreach($capabilities['page_orientation']['option'] as $option) {
                    $capabilityOptions['page_orientation'][$option['type']] = ucfirst(strtolower($option['type']));

                    if($option['is_default'] == true) {
                        $capabilityDefault['page_orientation'] = $option['type'];
                    }
                }
            }

            if(!empty($capabilities['duplex'])) {
                $capabilityOptions['duplex'] = array();

                foreach($capabilities['duplex']['option'] as $option) {
                    if($option['type'] == 'NO_DUPLEX') {
                        $capabilityOptions['duplex'][$option['type']] = 'no Duplex';
                    } elseif($option['type'] == 'LONG_EDGE') {
                        $capabilityOptions['duplex'][$option['type']] = 'Duplex';
                    } else {
                        continue;
                    }

                    if($option['is_default'] == true) {
                        $capabilityDefault['duplex'] = $option['type'];
                    }
                }
            }


            $viewer->assign('printerCapabilityDefaults', $capabilityDefault);
            $viewer->assign('printerCapabilities', $capabilityOptions);
        }
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
