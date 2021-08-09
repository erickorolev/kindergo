<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMailscanner_storemail extends \Workflow\Task
{
    protected $_envSettings = array("email_crmid");

    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;

    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    public function init() {
        $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
            //'module' => VtUtils::getModuleName($parts[1]),
            'default' => 'condition',
            'moduleselect' => true
        ));

        $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
            'fromModule' => '',
            'toModule' => 'Emails',
            'refFields' => false,
            'limitfields' => array('assigned_user_id', 'email_flag', )
        ));

    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

		$records = $this->RecordSources->getRecordIds($context);

		// If no matching record is found, return no
		if(empty($records)) {
		    return 'no';
        }

        $message = \Workflow\Mailscanner::getCurrentMessage();

		$receiver = $message->getTo();
		$to_email = array();
		foreach($receiver as $mail) {
            $to_email[] = $mail->getAddress();
        }
		$cc_email = array();
        $receiver = $message->getCc();
		foreach($receiver as $mail) {
            $cc_email[] = $mail->getAddress();
        }
		$bcc_email = array();
        $receiver = $message->getBcc();
		foreach($receiver as $mail) {
            $bcc_email[] = $mail->getAddress();
        }

        $tmpReceiver = array();
        foreach($records as $id) {
            $tmpReceiver[] = $id.'@9';
        }
        $tmpReceiver[] = '';

        require_once('modules/Emails/Emails.php');
        $focus = new Emails();

        $focus->column_fields["assigned_user_id"] = \Workflow\VTEntity::getUser()->id;
        $focus->column_fields["activitytype"] = "Emails";
        $focus->column_fields["date_start"] = $message->getDate()->format("Y-m-d");
        $focus->column_fields["time_start"] = $message->getDate()->format("H:i:s");
        $_REQUEST['parent_id'] = $focus->column_fields["parent_id"] = implode('|', $tmpReceiver);
        $focus->column_fields["email_flag"] = 'SENT';//$this->get('flag');

        $focus->column_fields["subject"] = $message->getSubject();
        $body = $message->getBodyHtml();

        if(empty($body)){
            $body = nl2br($message->getBodyText());

            if(empty($body)){
                $message->getContent();
                $body = $message->getBodyText() != '' ? $message->getBodyText() : $message->getBodyHtml();
            }
        }

        $focus->column_fields["description"] = $body;
        $focus->column_fields["from_email"] = $message->getFrom()->getAddress();
        $focus->column_fields["saved_toid"] = implode('","',$to_email);

        $focus->column_fields["ccmail"] = implode('","',$cc_email);
        $focus->column_fields["bccmail"] = implode('","',$bcc_email);

        $setterMap = $this->get('setter');
        $fields = $this->fieldSetter->getFieldValueArray(\Workflow\VTEntity::getDummy(), $setterMap);
        foreach($fields as $fieldname => $fieldvalue) {
            $focus->column_fields[$fieldname] = $fieldvalue;
        }

        $oldModule = $_REQUEST['module'];

        $_REQUEST['module'] = 'Emails';
        $focus->save("Emails");
        $_REQUEST['module'] = $oldModule;
        $this->_mailRecord = $focus;

        $users =
        /**
         * @var $attachments Ddeboer\Imap\Message\Attachment[]
         */
        $attachments = $message->getAttachments();

        if(count($attachments) > 0) {
            foreach($attachments as $attachment) {
                $tmpfname = tempnam(sys_get_temp_dir(), 'attach');
                unlink($tmpfname);

                file_put_contents($tmpfname, $attachment->getDecodedContent());

                $this->attachFile($tmpfname, $attachment->getFilename(), $attachment->getType());
            }
        }

        $context->setEnvironment("email_crmid", $this->_mailRecord->id, $this);

		return "yes";
    }

    public function attachFile($filePath, $filename, $filetype) {
        if(null === $this->_mailRecord) {
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

        \Workflow\VtUtils::pquery($sql1, $params1);

        $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
        $params2 = array($next_id, $filename, $this->_mailRecord->column_fields["description"], $filetype, $upload_file_path);
        \Workflow\VtUtils::pquery($sql2, $params2);

        $sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
        \Workflow\VtUtils::pquery($sql3, array($this->_mailRecord->id, $next_id));
    }

    public function beforeGetTaskform($viewer) {
        $viewer->assign('users', getAllUserName());
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
