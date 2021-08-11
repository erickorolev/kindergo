<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
require_once('modules/Emails/mail.php');

/* vt6 ready 2014/04/09 */
class WfTaskSendmail extends \Workflow\Task {
    protected $_envSettings = array("sendmail_result", 'email_crm_id');
    private $_mailRecord = null;

    public function init() {
        $this->addPreset("Attachments", "attachments", array(
            'module' => $this->getModuleName()
        ));
    }

    public function beforeGetTaskform($viewer) {
        global $adb;
        $connected = $this->getConnectedObjects("Absender");

        if(count($connected) > 0) {
            $viewer->assign("from", array(
                "from_mail" => $connected[0]->get("email1"),
                "from_name" => trim($connected[0]->get("first_name")." ".$connected[0]->get("last_name")),
                "from_readonly" => true,
            ));
        }

        $smtpServer = \Workflow\ConnectionProvider::getAvailableConfigurations('smtp');
        $viewer->assign('SMTPSERVER', $smtpServer);

        $connected = $this->getConnectedObjects("BCC");

        $bccs = $connected->get("email1");
        $viewer->assign("bccs", $bccs);

        $from_email = $this->get("from_mail");

        if($from_email === -1) {
            global $current_user;

            $from_email = $current_user->column_fields["email1"];

            $from_name = $current_user->column_fields["first_name"]." ".$current_user->column_fields["last_name"];
            $this->set("from_mail", $from_email);
            $this->set("from_name", $from_name);
        }

        if($this->notEmpty('mailtemplate')) {
            $mailtemplate = $this->get("mailtemplate");

            if(is_numeric($mailtemplate)) {
                $this->set('mailtemplate', 's#V2#\\Workflow\\Plugins\\EmailTemplate\\Core-'.$mailtemplate);
            } else {
                $parts = explode('#', $mailtemplate);

                switch($parts[1]) {
                    case 'emailmaker':
                        $this->set('mailtemplate', 's#V2#\\Workflow\\Plugins\\EmailTemplate\\Emailmaker-'.$parts[2]);
                        break;
                }
            }
        }
/*        if(vtlib_isModuleActive('Emails')) {
            $availableAttachments = \Workflow\InterfaceFiles::getAvailableFiles($this->getModuleName());
        } else {
            throw new \Exception('To use this task, you need to activate the "Emails" module.');
        }

        $jsList = array();
        foreach($availableAttachments as $title => $group) {
            foreach($group as $index => $value) {
                $jsList[$index] = $value;
            }
        }
        $viewer->assign("jsAttachmentsList", $jsList);
        $viewer->assign("available_attachments", $availableAttachments);

        if($this->get("attachments") == -1) {
            $this->set("attachments", '{}');
        }
        if($this->get("attachments") == "") {
            $this->set("attachments", '{}');
        }
*/
        $attachments = $this->get("attachments");

        if($this->notEmpty('provider')) {
            /**
             * @var $smtpServer \Workflow\Plugins\ConnectionProvider\SMTP
             */
            $smtpServer = \Workflow\ConnectionProvider::getConnection($this->get('provider'));
            if(empty($smtpServer)) {
                $query = "select from_email_field from vtiger_systems where server_type=?";
                $params = array('email');
                $result = $adb->pquery($query,$params);
                $from_email_field = $adb->query_result($result,0,'from_email_field');

                if(!empty($from_email_field)) {
                    $viewer->assign('show_emailfrom_checkbox', true);
                } else {
                    $viewer->assign('show_emailfrom_checkbox', false);
                }
            } else {
                $senderMail = $smtpServer->get('sender_mail');

                if (!empty($senderMail)) {
                    $viewer->assign('show_emailfrom_checkbox', true);
                } else {
                    $viewer->assign('show_emailfrom_checkbox', false);
                }
            }
        } else {
            $query = "select from_email_field from vtiger_systems where server_type=?";
            $params = array('email');
            $result = $adb->pquery($query,$params);
            $from_email_field = $adb->query_result($result,0,'from_email_field');

            if(!empty($from_email_field)) {
                $viewer->assign('show_emailfrom_checkbox', true);
            } else {
                $viewer->assign('show_emailfrom_checkbox', false);
            }
        }



        if(strpos($attachments, '"pdfmaker') !== false) {
            $attachments = json_decode($this->get("attachments"), true);
            if(is_array($attachments) && count($attachments) > 0) {

                \Vtiger_Loader::autoLoad('PDFMaker_PDFMaker_Model');
                $PDFMaker = new \PDFMaker_PDFMaker_Model();

                if (method_exists($PDFMaker, "GetAvailableTemplates")) {
                    $templates = $PDFMaker->GetAvailableTemplates($this->getModuleName());
                    foreach ($templates as $index => $value) {
                        $pdfmaker[$index] = 'PDFMaker - ' . $value["templatename"];
                    }

                    foreach($attachments as $id => $attachment) {
                        //{"s#external#pdfmaker#1":["PDFMaker - Invoice","",{"val":"pdfmaker#1"}]
                        $parts = explode('#', $id);
                        if($parts[0] == 'pdfmaker') {
                            unset($attachments[$id]);
                            $attachments['s#external#pdfmaker#'.$parts[1]] = array($pdfmaker[$parts[1]], "", array("val" => "pdfmaker#".$parts[1]));
                        }
                    }
                    $this->set('attachments', json_encode($attachments));
                }
            }

        }


        $obj = new \Workflow\Emailtemplates();
        $mailtemplates = $obj->getAllTemplates($this->getModuleName());

        /*$sql = "SELECT * FROM vtiger_emailtemplates WHERE deleted = 0";
        $result = $adb->query($sql);
        $mailtemplates = array();
        while($row = $adb->fetchByAssoc($result)) {
            $mailtemplates['Email Templates'][$row["templateid"]] = $row["templatename"];
        }*/
/*
        if(vtlib_isModuleActive('EMAILMaker') && class_exists('EMAILMaker_Module_Model')) {

        }
*/
        /*
        if(vtlib_isModuleActive('SWBeeFree') && class_exists('SWBeeFree_Module_Model')) {
            $beefree = new \SWBeeFree_Module_Model();
            $templates = $beefree->getTemplatesForModule($this->getModuleName());
            foreach($templates as $template) {
                $mailtemplates['BeeFree']['s#beefree#'.$template['id']] = $template['name'];
            }

        }
        */

        $viewer->assign("MAIL_TEMPLATES", $mailtemplates);
        $viewer->assign("fields", \VtUtils::getFieldsWithBlocksForModule($this->getModuleName(), true));

        if(defined("WF_DEMO_MODE") && constant("WF_DEMO_MODE") == true) {
            echo "<p style='text-align:center;margin:0;padding:5px 0;background-color:#fbcb09;font-weight:bold;'>The sendmail Task won't work on demo.stefanwarnat.de</p>";
        }
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return mixed
     */
    public function handleTask(&$context) {
        global $adb, $current_user;
        global $current_language;

        if(defined("WF_DEMO_MODE") && constant("WF_DEMO_MODE") == true) {
            return "yes";
        }

       /* if(!class_exists("Workflow_PHPMailer")) {
            require_once("modules/Workflow2/phpmailer/class.phpmailer.php");
        }*/

        #$result = $adb->query("select user_name, email1, email2 from vtiger_users where id=1");
        #$from_email = "swarnat@praktika.de";
        #$from_name  = "Stefan Warnat";

        $module = $context->getModuleName();


        if($this->notEmpty('interactivemail')) {

            $blockReqKey = '_sendmail_' . $this->getBlockId();
//            var_dump($context);
            if ($this->getWorkflow()->hasRequestValues($blockReqKey)) {
                $data = $context->getEnvironment($blockReqKey);

                //                $this->getWorkflow()->
                if(!empty($data['emailid'])) {
                    $record = \Vtiger_Record_Model::getInstanceById($data['emailid']);
                    $record->delete();
                }

                if(strpos($data['recepient'], ';#;') !== false) {
                    $data['recepient'] = explode(';#;', $data['recepient']);
                } else {
                    $data['recepient'] = array($data['recepient']);
                }

                $this->set('from_mail', $data['from_mail']);
                $this->set('from_name', $data['from_name']);
                $this->set('recepient', implode(',', $data['recepient']));

                $this->set('subject', $data['subject']);
                $this->set('content', $data['content']);
            }
        }

        $recepient = $this->get('recepient', $context);

        if(strpos($recepient, ';#;') !== false) {
            $recepient = str_replace(';#;', ',', $recepient);
        }

        if(strpos($recepient, ',') !== false) {
            $recepients = explode(',', $recepient);
        } else {
            $recepients = array($recepient);
        }

        if($this->notEmpty('storeid')) {
            $storeid = explode(',', $this->get('storeid'));
        } else {
            $storeid = array();
        }

        $recepient = array();

        foreach($recepients as $receiver) {
            if(substr($receiver, 0, 5) == 'crm##') {
                $recieverParts = explode('##', $receiver);
                $recepient[] = $recieverParts[2];
                $storeid[] = $recieverParts[1];
            } elseif(substr($receiver, 0, 5) == 'raw##') {
                $recieverParts = explode('##', $receiver);
                $recepient[] = $recieverParts[2];
            } else {
                $recepient[] = $receiver;
            }
        }
//        var_dump($recepient);
        if(!empty($storeid)) {
            $this->set('storeid', '$crmid,'.implode(',',$storeid));
        }
        $this->set('recepient', implode(',', $recepient));

        if(!$this->notEmpty('recepient')) {
            return 'yes';
        }

        $et = new \Workflow\VTTemplate($context);
        $to_email = $et->render(trim($this->get("recepient")),","); #

        $connected = $this->getConnectedObjects("Absender");
        if(count($connected) > 0) {
            $from_name = trim($connected[0]->get("first_name")." ".$connected[0]->get("last_name"));
            $from_email = $connected[0]->get("email1");
        } else {
            $from_name = $et->render(trim($this->get("from_name")),","); #
            $from_email = $et->render(trim($this->get("from_mail")),","); #
        }

        $cc = $et->render(trim($this->get("emailcc")),","); #
        $bcc = $et->render(trim($this->get("emailbcc")),","); #

        /*
        if($this->get('use_mailserver_from') == '1') {
            $query = "select from_email_field from vtiger_systems where server_type=?";
            $params = array('email');
            $result = $adb->pquery($query, $params);
            $from_email_field = $adb->query_result($result, 0, 'from_email_field');

            if (!empty($from_email_field)) {
                $from_email = $from_email_field;
            }
        }
        */

        /**
         * Connected BCC Objects
         * @var $connected
         */
        $connected = $this->getConnectedObjects("BCC");
        $bccs = $connected->get("email1");
        if(count($bccs) > 0) {
            $bcc = array($bcc);

            foreach($bccs as $bccTMP) {
                $bcc[] = $bccTMP;
            }
            $bcc = trim(implode(",",$bcc),",");
        }

        if(strlen(trim($to_email, " \t\n,")) == 0 && strlen(trim($cc, " \t\n,")) == 0 && strlen(trim($bcc, " \t\n,")) == 0) {
            return "yes";
        }
        $storeid = trim($this->get("storeid", $context));
        if(empty($storeid) || $storeid == -1 || (!is_numeric($storeid) && strpos($storeid, ',') === false)) {
            $storeid = $context->getId();
        }

        $embeddedImages = null;
        $unlinkAfterSending = array();

        $content = $this->get("content");
        $subject = $this->get("subject");

        $content = preg_replace( '/[\x{200B}-\x{200D}]/u', '', $content );
        $subject = preg_replace( '/[\x{200B}-\x{200D}]/u', '', $subject );

        #$subject = utf8_decode($subject);
        #$content = utf8_encode($content);

        $content = html_entity_decode(str_replace("&nbsp;", " ", $content), ENT_QUOTES, "UTF-8");
        #$subject = html_entity_decode(str_replace("&nbsp;", " ", $subject), ENT_QUOTES, "UTF-8");

        $mailtemplate = $this->get("mailtemplate");
        if(!empty($mailtemplate) && $mailtemplate != -1) {
            if(is_numeric($mailtemplate)) {
                $obj = new \Workflow\Emailtemplates();
                $mailtemplate = $obj->getTemplate('\\Workflow\\Plugins\\EmailTemplate\\Core-'.$mailtemplate, $context);

                $content = str_replace('$mailtext', $content, $mailtemplate["content"]);

                if(empty($subject)) {
                    $subject = $mailtemplate["subject"];
                }
                if(!empty($mailtemplate['images'])) {
                    $embeddedImages = $mailtemplate['images'];
                }
            } else {
                $parts = explode('#', $mailtemplate);

                switch($parts[1]) {
                    case 'emailmaker':
                        $obj = new \Workflow\Emailtemplates();
                        $mailtemplate = $obj->getTemplate('\\Workflow\\Plugins\\EmailTemplate\\Emailmaker-'.$parts[2], $context);

                        $content = str_replace('$mailtext', $content, $mailtemplate["content"]);

                        if(empty($subject)) {
                            $subject = $mailtemplate["subject"];
                        }
                        if(!empty($mailtemplate['images'])) {
                            $embeddedImages = $mailtemplate['images'];
                        }

                        break;
                    case 'beefree':
                        $obj = new \Workflow\Emailtemplates();
                        $mailtemplate = $obj->getTemplate('\\Workflow\\Plugins\\EmailTemplate\\Beefree-'.$parts[2], $context);

                        $content = str_replace('$mailtext', $content, $mailtemplate["content"]);

                        if(!empty($mailtemplate['images'])) {
                            $embeddedImages = $mailtemplate['images'];
                        }

                        break;
                    case 'V2':
                        $obj = new \Workflow\Emailtemplates();
                        $mailtemplate = $obj->getTemplate($parts[2], $context);

                        $content = str_replace('$mailtext', $content, $mailtemplate["content"]);

                        if(empty($subject)) {
                            $subject = $mailtemplate["subject"];
                        }
                        if(!empty($mailtemplate['images'])) {
                            $embeddedImages = $mailtemplate['images'];
                        }
                        break;
                }
            }
        }

        $subject = $et->render(trim($subject));
        $content = $et->render(trim($content));

        #$content = htmlentities($content, ENT_NOQUOTES, "UTF-8");

        if(DEMO_MODE == false) {
            if ($this->notEmpty('provider')) {
                /**
                 * @var $smtpServer \Workflow\Plugins\ConnectionProvider\SMTP
                 */
                $smtpServer = \Workflow\ConnectionProvider::getConnection($this->get('provider'));
                $mail = $smtpServer->getPHPMailer();
            } else {
                $mail = \Workflow\Plugins\ConnectionProvider\SMTP::getDefaultMailer();
            }

            if($this->get('use_mailserver_from') == '1') {
                $from_email = $mail->From;
            }
        }

        if(getTabid('Emails') && vtlib_isModuleActive('Emails')) {
            $storeIdParts = array();
            if(strpos($storeid, ',') !== false) {
                $storeIdParts = explode(',', $storeid);
                $storeid = array_shift($storeIdParts);
            }

            require_once('modules/Emails/Emails.php');
            $focus = new Emails();

            $focus->column_fields["assigned_user_id"] = \Workflow\VTEntity::getUser()->id;
            $focus->column_fields["activitytype"] = "Emails";
            $focus->column_fields["date_start"] = date("Y-m-d");
            $focus->column_fields["time_start"] = date("H:i:s");
            $focus->column_fields["parent_id"] = $storeid;
            $focus->column_fields["email_flag"] = "SAVED";

            $focus->column_fields["subject"] = $subject;
            $focus->column_fields["description"] = $content;
            $focus->column_fields["from_email"] = $from_email;
            $focus->column_fields["saved_toid"] = '["'.str_replace(',','","',trim($to_email,",")).'"]';

            $focus->column_fields["ccmail"] = $cc;
            $focus->column_fields["bccmail"] = $bcc;

            $focus->save("Emails");
            $this->_mailRecord = $focus;

            #error_log("eMail:".$emailID);

            $emailID = $focus->id;

            if(!empty($storeIdParts)) {
                foreach($storeIdParts as $id) {
                    $sql = 'INSERT INTO vtiger_seactivityrel SET crmid = ?, activityid = ?';
                    $adb->pquery($sql, array($id, $emailID));
                }
            }
        } else {
            $emailID = "";
        }

        $attachments = json_decode($this->get("attachments"), true);
        if(is_array($attachments) && count($attachments) > 0) {
            // Module greifen auf Datenbank zurück. Daher vorher speichern!
            $context->save();
            //$this->addStat($attachments);
            foreach($attachments as $key => $value) {
                if($value == false) {
                    continue;
                }

                if(is_string($value)) { $value = array($value, false, array()); }

                // legacy check
                if(strpos($key, 'document#') === 0) {
                    $key = 's#'.$key;
                }

                if(strpos($key, 's#') === 0) {
                    $tmpParts = explode('#', $key, 2);

                    $specialAttachments = \Workflow\Attachment::getAttachments($tmpParts[1], $value, $context, \Workflow\Attachment::MODE_NOT_ADD_NEW_ATTACHMENTS);

                    //$this->addStat('Add Attachment 1');
                    //$this->addStat($specialAttachments);

                    foreach($specialAttachments as $attachment) {
                        $this->addStat($attachment[2]);
                        $this->addStat(filesize($attachment[1]).' Bytes');

                        if($attachment[0] === 'ID') {
                            $this->attachByAttachmentId($attachment[1]);
                        } elseif($attachment[0] === 'PATH') {
                            $this->attachFile($attachment[1], $attachment[2], $attachment[3]);
                        }
                    }

                } else {
                    $file = \Workflow\InterfaceFiles::getFile($key, $this->getModuleName(), $context->getId());

                    //$this->addStat('Add Attachment 2');
                    //$this->addStat($file['path']);
                    //$this->addStat(filesize($file));

                    $this->attachFile($file['path'], $value[1] != false ? $value[1] : $file['name'], $file['type']);
                }
            }

        }
        if($_SERVER['REMOTE_ADDR'] == '87.134.26.231') {
            ini_set('display_errors', 1);
            error_reporting(-1);
        }
        if($embeddedImages === null && $this->get('attachImages') == '1') {
            if(!function_exists('str_get_html')) {
                require_once('include/simplehtmldom/simple_html_dom.php');
            }

            $html = str_get_html($content);
            if(count($html->find('img')) > 0) {
                $tmppath = realpath(vglobal('root_directory') . '/modules/Workflow2/tmp');

                $embeddedImages = array();

                $done = array();
                $index = 50;
                foreach ($html->find('img') as $img) {
                    $src = $img->src;
                    if(isset($done[$src])) {
                        $newCID = $done[$src];
                    } else {
                        $done[$src] = 'image'.$index;

                        $parts = explode('.', $src);
                        $fileSuffix = end($parts);

                        $tmpFile = tempnam($tmppath,'Embedd');
                        $unlinkAfterSending[] = $tmpFile;

                        try {
                            $content = \Workflow\VtUtils::getContentFromUrl($src);
                        } catch (\Exception $exp) {
                            $this->addStat('[SKIP] Error during download Image '.$src.': Code '.$exp->getCode());
                            $this->addStat(substr(htmlentities($exp->getMessage()), 0, 200));
                            continue;
                        };

                        if(empty($content)) {
                            continue;
                        }

                        file_put_contents($tmpFile, $content);

                        $embeddedImages['image'.$index] = array(
                            "name" => 'image'.$index.'.'.$fileSuffix,
                            "path" => $tmpFile
                        );
                        $newCID = 'image'.$index;

                        $index++;
                    }

                    $img->src = 'cid:'.$newCID;
                }
            }
            $content = $html->save();
        }

        $receiver = explode(",", $to_email);
        foreach($receiver as $to_email) {
            $to_email = trim($to_email);

            if(empty($to_email))
                continue;

            $mail->ClearAddresses();

            if(DEMO_MODE == false) {
                if(is_array($embeddedImages)) {
                    foreach ($embeddedImages AS $cid => $cdata) {
                        $mail->AddEmbeddedImage($cdata["path"], $cid, $cdata["name"]);
                    }
                }

                $to_email = trim($to_email,",");

                $mail->FromName = $from_name;

                if($this->get('use_mailserver_from') != '1') {
                    $mail->From = $from_email;
                }

                $this->addStat("From: ".$from_name." &lt;".$from_email."&gt;");

                if($this->get('trackAccess') == '1') {
                    //Including email tracking details
                    global $site_URL, $application_unique_key;
                    $counterUrl = $site_URL.'/modules/Emails/actions/TrackAccess.php?parentId='.$storeid.'&record='.$focus->id.'&applicationKey='.$application_unique_key;
                    $counterHeight = 1;
                    $counterWidth = 1;
                    if(defined('TRACKING_IMG_HEIGHT')) {
                        $counterHeight = TRACKING_IMG_HEIGHT;
                    }
                    if(defined('TRACKING_IMG_WIDTH')) {
                        $counterWidth = TRACKING_IMG_WIDTH;
                    }
                    $content = $content."<img src='".$counterUrl."' alt='' width='".$counterWidth."' height='".$counterHeight."'>";
                }

                $mail->Subject = $subject;
                $this->addStat("Subject: ".$subject);
                $mail->MsgHTML($content);

                $mail->SMTPDebug = 2;

                if($this->notEmpty('multiplereceiver')) {
                    foreach($receiver as $to) {
                        $to = trim($to);
                        if(empty($to))
                            continue;

                        $mail->addAddress($to);
                    }
                } else {
                    $mail->addAddress($to_email);
                }

                $this->addStat("To: ".$to_email);

                setCCAddress($mail,'cc',$cc);
               	setCCAddress($mail,'bcc',$bcc);

                if($this->get('replyto') != -1 && $this->get('replyto') != '') {
                    $replyto = $et->render(trim($this->get('replyto')));
                    $split = explode(',', $replyto);

                    foreach($split as $email) {
                        $email = trim($email);
                        if(empty($email)) continue;

                        $mail->addReplyTo($email);
                    }

                }
                if($this->get('confirmreading') != -1 && $this->get('confirmreading') != '') {
                    $mail->ConfirmReadingTo = $this->get('confirmreading');
                }

                #$mail->IsHTML(true);

                if(!empty($emailID)) {
                    addAllAttachments($mail, $emailID);
                }

                $mail->Debugoutput=0;

                $this->addStat('PHPMailer Attachments');
                $this->addStat($mail->GetAttachments());
                $this->addStat($emailID);
                $sql = "select vtiger_attachments.* from vtiger_attachments inner join vtiger_seattachmentsrel on vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_attachments.attachmentsid where vtiger_crmentity.deleted=0 and vtiger_seattachmentsrel.crmid=?";
                $res = $adb->pquery($sql, array($emailID));
                $count = $adb->num_rows($res);
                global $root_directory;
                for($i=0;$i<$count;$i++)
                {
                    $fileid = $adb->query_result($res,$i,'attachmentsid');
                    $filename = decode_html($adb->query_result($res,$i,'name'));
                    $filepath = $adb->query_result($res,$i,'path');
                    $filewithpath = $root_directory.$filepath.$fileid."_".$filename;

                    $this->addStat($filewithpath);
                    $this->addStat(is_file($filewithpath) ? 'exist' : 'not exist');
                    //if the file is exist in test/upload directory then we will add directly
                    //else get the contents of the file and write it as a file and then attach (this will occur when we unlink the file)
                }


                if($this->get('interactivemail') === '1') {
                    $blockReqKey = '_sendmail_'.$this->getBlockId();
                    if(!$this->getWorkflow()->hasRequestValues($blockReqKey)) {
                        $req = new \Workflow\RequestValuesForm($blockReqKey);
                        $req->setSettings('width', '700px');
                        $req->setTargetScope($blockReqKey);
                        $row1 = $req->addRow();

                        $fieldSender = $row1->addField();
                        $fieldSender->setType('hidden');
                        $fieldSender->setFieldname('emailid');
                        $fieldSender->setValue($emailID);

                        $fieldSender = $row1->addField();
                        $fieldSender->setType('email-text');
                        $fieldSender->setFieldname('from_mail');
                        $fieldSender->setValue($mail->From);
                        $fieldSender->setLabel('From');

                        $fieldSenderName = $row1->addField();
                        $fieldSenderName->setType('text');
                        $fieldSenderName->setFieldname('from_name');
                        $fieldSenderName->setLabel('From Name');
                        $fieldSenderName->setValue($mail->FromName);

                        $row2 = $req->addRow();

                        $fieldTo = $row2->addField();
                        $fieldTo->setType('email');
                        $fieldTo->setFieldname('recepient');
                        $fieldTo->setConfigValue('multiple', '1');
                        $fieldTo->setValue(implode(',', $receiver));
                        $fieldTo->setLabel('Recipient');

                        $row3 = $req->addRow();

                        $fieldSubject = $row3->addField();
                        $fieldSubject->setType('text');
                        $fieldSubject->setFieldname('subject');
                        $fieldSubject->setValue($mail->Subject);
                        $fieldSubject->setLabel('Subject');

                        $row4 = $req->addRow();

                        $fieldContent = $row4->addField();
                        $fieldContent->setType('htmleditor');
                        $fieldContent->setFieldname('content');
                        $fieldContent->setValue($mail->Body);
                        $fieldContent->setLabel('E-Mail Body');

                        $req->startRequestValues($this, $context);
                        return false;
                    }
                }

                try {
                    ob_start();
                    $mail_return = MailSend($mail);

                    $debug = ob_get_clean();
                    $debug = preg_replace("/\s(.{60,})\s/", " ", $debug);
                    $debug = preg_replace("/CLIENT -> SMTP:(\s+)\n/", "", $debug);
                    $this->addStat($debug);
                } catch(Workflow_phpmailerException $exp) {
                    $debug = ob_get_clean();
                    $this->addStat('SMTP Error');
                    $this->addStat($exp->getMessage());
                    $debug = preg_replace("/\s(.{60,})\s/", " ", $debug);
                    $debug = preg_replace("/CLIENT -> SMTP:(\s+)\n/", "", $debug);
                    $this->addStat($debug);
                }

                #$mail_return = send_mail($module, $to_email,$from_name,$from_email,$subject,$content, $cc, $bcc,'all',$emailID);
            } else {
                $mail_return = 1;
            }

            $this->addStat("Send eMail with following Result:");
            $this->addStat($mail_return);

            foreach($unlinkAfterSending as $filename) {
                @unlink($filename);
            }

            if($mail_return != 1) {
                if (empty($mail->ErrorInfo) && empty($mail_return)) {
                    $mail_return = 1;
                }
            }

            $context->setEnvironment("email_crm_id", $emailID, $this);
            $context->setEnvironment("sendmail_result", $mail_return, $this);

            if($mail_return != 1) {
                $retry = $context->getEnvironment('_blockRetry'.$this->getBlockId());
                if(empty($retry)) {
                    // 1h später
                    $retryTimer = 60;
                    $retry = 1;
                } else {
                    switch ($retry) {
                        case 2:
                        case 3:
                            // 3 h später
                            $retryTimer = 180;
                            break;
                        case 4:
                            // 4h später
                            $retryTimer = 240;
                            break;
                            break;
                        case 5:
                            // 24 h später
                            $retryTimer = 1440;
                            break;
                        default:
                            $unlimited = $this->notEmpty('unlimitedretry');
                            if($unlimited) {
                                $retryTimer = 1440;
                            } else {
                                return 'yes';
                            }
                    }
                }

                $context->setEnvironment('_blockRetry'.$this->getBlockId(), $retry + 1);

                Workflow2::send_error("Sendmail Task couldn't send an email to ".$to_email."<br>Error: ".var_export($mail->ErrorInfo, true)."<br><br>The Task will be rerun after ".$retryTimer." minutes.", __FILE__, __LINE__);
                Workflow2::error_handler(E_NONBREAK_ERROR, "Sendmail Task couldn't send an email to ".$to_email. "<br>Error: ".var_export($mail->ErrorInfo, true)."<br><br>The Task will be rerun after ".$retryTimer." minutes.", __FILE__, __LINE__);

                return array("delay" => time() + ($retryTimer * 60), "checkmode" => "static");
            }

            if($this->notEmpty('multiplereceiver')) {
                break;
            }

        }

        // Set Mails as Send
        $sql = "UPDATE vtiger_emaildetails SET email_flag = 'SENT' WHERE emailid = '".$emailID."'";
        $adb->query($sql);

        return "yes";
    }

    public function attachByAttachmentId($attachmentID) {
        if(null === $this->_mailRecord) {
            return;
        }

        $adb = \PearDatabase::getInstance();
        $sql = 'select crmid from vtiger_seattachmentsrel WHERE crmid = ? AND attachmentsid = ?';
        $result = $adb->pquery($sql, array($this->_mailRecord->id,  $attachmentID));
        if($adb->num_rows($result) == 0) {

            $sql3='insert into vtiger_seattachmentsrel values(?,?)';
            $adb->pquery($sql3, array($this->_mailRecord->id,  $attachmentID));

        }
    }
    public function attachFile($filePath, $filename, $filetype) {
        if(null === $this->_mailRecord) {
            return;
        }
        if(empty($filePath)) {
            return;
        }

        $adb = \PearDatabase::getInstance();
        $current_user = \Users_Record_Model::getCurrentUserModel();

        $upload_file_path = decideFilePath();

        $date_var = date("Y-m-d H:i:s");
        $next_id = $adb->getUniqueID("vtiger_crmentity");

        if(is_array($filename)) {
            if(!empty($filename['filename'])) {
                $filename = $filename['filename'];
            } else {
                $filename = 'unknown-filename.txt';
            }
        }

        rename($filePath, $upload_file_path . $next_id . "_" . $filename);

        $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
        $params1 = array($next_id, $current_user->id, $current_user->id, "Documents Attachment",'Documents Attachment', date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));

        $adb->pquery($sql1, $params1);

        $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
        $params2 = array($next_id, $filename, $this->_mailRecord->column_fields["description"], $filetype, $upload_file_path);
        $adb->pquery($sql2, $params2, true);

        $sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
        $adb->pquery($sql3, array($this->_mailRecord->id, $next_id));
    }

}
