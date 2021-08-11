<?php
/**
 * Created by Stefan Warnat
 * User: Stefan
 * Date: 28.06.2017
 * Time: 15:00
 */

namespace Workflow;

use Ddeboer\Imap\Message;
use Ddeboer\Imap\MessageIterator;
use Ddeboer\Imap\Search\Date\After;
use Ddeboer\Imap\Search\Date\Before;
use Ddeboer\Imap\Search\Email\FromAddress;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Flag\Answered;
use Ddeboer\Imap\Search\Flag\Seen;
use Ddeboer\Imap\Search\Flag\Unseen;
use Ddeboer\Imap\Search\Text\Body;
use Ddeboer\Imap\Search\Text\Keyword;
use Ddeboer\Imap\Search\Text\Subject;
use Ddeboer\Imap\SearchExpression;
use Workflow\Plugins\ConnectionProvider\IMAP;

class Mailscanner
{
    private $_id = null;
    /**
     * @var MessageIterator[]
     */
    private $_messageIds = array();
    private $_messageSeek = 0;
    private $_currentMailbox = '';

    private $_folderToCheck = array();
    private $_data = null;
    private $executedCounter = 0;
    private $ImportLimit = 1;

    private $_TestRun = false;

    private static $CurrentMail = null;
    private static $Instance = null;

    private $Counter = 0;

    private $executeOnlyId = null;

    /**
     * Mailscanner constructor.
     * @param $mailscannerId
     */
    public function __construct($mailscannerId) {
        $this->_id = intval($mailscannerId);

        $this->resetFoldercheck();

        self::$Instance = $this;

        set_time_limit(300);
    }

    public function executeAgain($processedId) {


        $sql = 'SELECT * FROM vtiger_wf_mailscanner_done WHERE mailscanner_id = ? AND id = ?';
        $row = VtUtils::fetchByAssoc($sql, array($this->_id, $processedId));
        $messageId = $row['messageid'];

        $this->executeOnlyId = $processedId;
        $this->removeProcessed($processedId);

        $this->execute(1);
    }
    public function removeProcessed($processedId) {
        $sql = 'DELETE FROM vtiger_wf_mailscanner_done WHERE mailscanner_id = ? AND id = ?';
        $result = VtUtils::pquery($sql, array($this->_id, $processedId));

    }
    /**
     *
     */
    public function resetFoldercheck() {
        $data = $this->getData();

        $this->_folderToCheck = array();

        foreach($data['folder'] as $folder) {
            $this->_folderToCheck[$folder] = true;
        }
    }

    /**
     * When function is called, mails are not marked as done
     */
    public function testRun($value = true) {
        $this->_TestRun = ($value == true);
    }

    /**
     * @param int $maxMails
     */
    public function execute($maxMails = 10) {
        $data = $this->getData();

        $this->executedCounter = 0;
        $this->ImportLimit = $maxMails;

        while ($this->executedCounter < $this->ImportLimit) {
            // Fetch one mail to process
            $mail = $this->getNextMail($this->_TestRun);

            // If no mails left, stop processing
            if(empty($mail)) {
                break;
            }

            self::$CurrentMail = $mail;

            if($this->_TestRun == false) {
                $this->markDone($mail);
            }

            $this->executedCounter++;

            $wfExecuter = new \Workflow\Execute();
            $wfExecuter->setUser(VtUtils::getAdminUser());

            $environment = $this->getEnvironment($mail);

            if(!empty($data['config']['emlfileid'])) {
                $tmpfname = tempnam(sys_get_temp_dir(), 'emplfile');
                unlink($tmpfname);
                $mail->saveFullBody($tmpfname);

                $wfExecuter->addFile('email.eml', $tmpfname, $data['config']['emlfileid']);
            }
            if(!empty($data['config']['attachmentfileid'])) {
                /**
                 * @var $attachments Message\Attachment[]
                 */
                $attachments = $mail->getAttachments();

                if(count($attachments) > 0) {
                    $counter = 1;
                    foreach($attachments as $attachment) {
                        $tmpfname = tempnam(sys_get_temp_dir(), 'attach');
                        unlink($tmpfname);

                        file_put_contents($tmpfname, $attachment->getDecodedContent());

                        $wfExecuter->addFile($attachment->getFilename(), $tmpfname, $data['config']['attachmentfileid'].'_'.$counter);
                        $counter++;
                    }
                }
            }

            $wfExecuter->setEnvironment($environment);

            $wfExecuter->runById($data['workflow_id']);


        }
    }

    /**
     * @param Message $message
     * @return array
     */
    private function getEnvironment(Message $message) {
        $data = $this->getData();

        $environment = array();

        foreach($data['environment'] as $envvar) {
            $value = '';

            switch($envvar['type']) {
                case 'to_array':
                    $cc = $message->getTo();
                    $value = array();
                    foreach($cc as $tmp) {
                        $value[] = array(
                            'mailbox' => trim($tmp->getMailbox()),
                            'hostname' => trim($tmp->getHostname()),
                            'full' => trim($tmp->getFullAddress()),
                            'mail' => trim($tmp->getAddress()),
                            'name' => trim($tmp->getName()),
                        );
                    }
                    break;
                case 'cc_array':
                    $cc = $message->getCc();
                    $value = array();
                    foreach($cc as $tmp) {
                        $value[] = array(
                            'mailbox' => trim($tmp->getMailbox()),
                            'hostname' => trim($tmp->getHostname()),
                            'full' => trim($tmp->getFullAddress()),
                            'mail' => trim($tmp->getAddress()),
                            'name' => trim($tmp->getName()),
                        );
                    }
                    break;
                case 'bcc_array':
                    $cc = $message->getBcc();
                    $value = array();
                    foreach($cc as $tmp) {
                        $value[] = array(
                            'mailbox' => trim($tmp->getMailbox()),
                            'hostname' => trim($tmp->getHostname()),
                            'full' => trim($tmp->getFullAddress()),
                            'mail' => trim($tmp->getAddress()),
                            'name' => trim($tmp->getName()),
                        );
                    }
                    break;
                case 'from_full':
                    $value = trim($message->getFrom()->getFullAddress());
                    break;
                case 'from_mail':
                    $value = trim($message->getFrom()->getAddress());
                    break;
                case 'from_hostname':
                    $value = trim($message->getFrom()->getHostname());
                    break;
                case 'from_mailbox':
                    $value = trim($message->getFrom()->getMailbox());
                    break;
                case 'from_name':
                    $value = trim($message->getFrom()->getName());
                    break;
                case 'subject':
                    $value = trim($message->getSubject());
                    break;
                case 'body_html':
                    $value = $message->getBodyHtml();
                    break;
                case 'body_text':
                    $value = $message->getBodyText();
                    break;
                case 'body_text_convert':
                    $value = $message->getBodyHtml();

                    if(empty($value)) {
                        $value = $message->getBodyText();
                    }
                    $value = preg_replace('/<br ?\/?>/', '-#-NEWLINE-#-', $value);
                    $value = trim(strip_tags($value));
                    $value = str_replace('&nbsp;', ' ', $value);

                    $value = preg_replace("/\s{5,}/", " ", $value);
                    $value = trim(str_replace("-#-NEWLINE-#-", PHP_EOL, $value));

                    break;
                case 'attachment_count':
                    $attachments = $message->getAttachments();
                    $value = count($attachments);
                    break;
            }

            $environment[$envvar['envvar']] = $value;
        }

        return $environment;
    }

    /**
     * @return int
     */
    public function getCounterExecutedMails() {
        return $this->executedCounter;
    }

    /**
     * @return array|mixed|null
     */
    public function getData() {
        if($this->_data === null) {
            $adb = \PearDatabase::getInstance();

            $sql = 'SELECT * FROM vtiger_wf_mailscanner WHERE id = ?';
            $result = $adb->pquery($sql, array($this->_id));
            $data = $adb->fetchByAssoc($result);

            if (!empty($data['condition'])) {
                $data['condition'] = \Workflow\VtUtils::json_decode(html_entity_decode($data['condition']));
            } else {
                $data['condition'] = array();
            }
            if (!empty($data['config'])) {
                $data['config'] = \Workflow\VtUtils::json_decode(html_entity_decode($data['config']));
            } else {
                $data['config'] = array();
            }
            if (!empty($data['environment'])) {
                $data['environment'] = \Workflow\VtUtils::json_decode(html_entity_decode($data['environment']));
            } else {
                $data['environment'] = array();
            }

            $sql = 'SELECT folder FROM vtiger_wf_mailscanner_folder WHERE mailscanner_id = ?';
            $result = $adb->pquery($sql, array($this->_id));
            $data['folder'] = array();

            while ($row = $adb->fetchByAssoc($result)) {
                $data['folder'][] = $row['folder'];
            }

            if (!empty($data['available_folder'])) {
                $data['available_folder'] = \Workflow\VtUtils::json_decode(html_entity_decode($data['available_folder']));
            } else {
                $data['available_folder'] = array();
            }

            $this->_data = $data;
        }

        return $this->_data;

    }

    /**
     * @return Message|bool
     */
    private function getMailFromQueue() {
        $adb = \PearDatabase::getInstance();
        $return = false;

        if($this->Counter++ > 10) {
            return false;
        }

        do {
            if($this->_messageSeek >= count($this->_messageIds)) {
                break;
            }

            $this->_messageIds->seek($this->_messageSeek);

            /**
             * @var $return Message
             */
            $return = $this->_messageIds->current();

            $this->_messageSeek++;

            $sql = 'SELECT id FROM vtiger_wf_mailscanner_done WHERE mailscanner_id = ? AND messageid = ?';
            $result = $adb->pquery($sql, array($this->_id, $return->getId()));

            $continue = false;
            if($adb->num_rows($result) > 0 || ($this->executeOnlyId !== null && $this->executeOnlyId != $return->getId())) {
                $continue = true;
                $return = false;
            } else {
                $continue = false;
            }

        } while($continue);

        return $return;
    }

    /**
     * @param bool $previewOnly
     * @return \Ddeboer\Imap\Message|bool
     */
    public function getNextMail($previewOnly = false) {
        $return = $this->getMailFromQueue();
        if($return !== false) {
            if($previewOnly == true) {
                $return->keepUnseen(true);
            }
            return $return;
        }

        $adb = \PearDatabase::getInstance();
        $data = $this->getData();

        if(empty($data['provider_id'])) {
            return false;
        }

        /**
         * @var $provider IMAP
         */
        $provider = ConnectionProvider::getConnection($data['provider_id']);

        $connection = $provider->getImapConnection();

        foreach($this->_folderToCheck as $folder => $dmy) {
            $mailbox = $connection->getMailbox($folder);

            $sql = 'SELECT * FROM vtiger_wf_mailscanner_folder WHERE mailscanner_id = ? AND folder = ?';
            $result = $adb->pquery($sql, array($this->_id, $folder));
            $since = $adb->query_result($result, 0, 'lastscan');

            if(!empty($data['condition'])) {
                if ($since != '0000-00-00 00:00:00') {
                    $data['condition'][] = array(
                        'field' => 'since',
                        'parameter' => $since,
                    );
                }
                $search = $this->getSearchExpression($data['condition']);
            } else {
                $search = null;
            }

            if(empty($search)) $search = null;

            $this->_messageIds = $mailbox->getMessages($search);
            $this->_messageSeek = 0;
            $this->_currentMailbox = $mailbox;

            unset($this->_folderToCheck[$folder]);

            $return = $this->getMailFromQueue();

            if($return !== false) {
                if($previewOnly == true) {
                    $return->keepUnseen(true);
                }
                return $return;
            }
        }

        return false;
    }

    /**
     * @param Message $message
     */
    private function markDone(Message $message) {
        $adb = \PearDatabase::getInstance();

        $sql = 'INSERT INTO vtiger_wf_mailscanner_done SET mailscanner_id = ?, messageid = ?, done = "'.date('Y-m-d H:i:s').'"';
        $adb->pquery($sql, array($this->_id, $message->getId()));
    }

    /**
     * @param $conditions
     * @return SearchExpression
     */
    private function getSearchExpression($conditions) {
        $search = new SearchExpression();
        /*            <option value="subject" data-type="text">{vtranslate('Subject contains', 'Settings:Workflow2')}</option>
                    <option value="body" data-type="text">{vtranslate('Body contains', 'Settings:Workflow2')}</option>
                    <option value="keywords" data-type="text">{vtranslate('Mails with Keyword', 'Settings:Workflow2')}</option>

                    <option value="before" data-type="date">{vtranslate('Sent before', 'Settings:Workflow2')}</option>
                    <option value="since" data-type="date">{vtranslate('Sent after', 'Settings:Workflow2')}</option>
                    <option value="new_message">{vtranslate('Message is new', 'Settings:Workflow2')}</option>
                    <option value="answered_messages">{vtranslate('Message was answered', 'Settings:Workflow2')}</option>

                    <option value="unseen_messages">{vtranslate('Message is unread', 'Settings:Workflow2')}</option>
                    <option value="seen_messages">{vtranslate('Message was read', 'Settings:Workflow2')}</option>
        */
        foreach($conditions as $condition) {
            switch($condition['field']) {
                case 'to':
                    $search->addCondition(new To($condition['parameter']));
                    break;
                case 'from':
                    $search->addCondition(new FromAddress($condition['parameter']));
                    break;
                case 'since':
                    $search->addCondition(new After(new \DateTime($condition['parameter'])));
                    break;
                case 'before':
                    $search->addCondition(new Before(new \DateTime($condition['parameter'])));
                    break;
                case 'answered_messages':
                    $search->addCondition(new Answered());
                    break;
                case 'unseen_messages':
                    $search->addCondition(new Unseen());
                    break;
                case 'seen_messages':
                    $search->addCondition(new Seen());
                    break;
                case 'subject':
                    $search->addCondition(new Subject($condition['parameter']));
                    break;
                case 'body':
                    $search->addCondition(new Body($condition['parameter']));
                    break;
                case 'keywords':
                    $search->addCondition(new Keyword($condition['parameter']));
                    break;
            }
        }

        return $search;
    }

    public function setData($data) {
        $this->_data = null;
        $fields = array('provider_id', 'condition', 'workflow_id', 'active', 'title', 'available_folder','environment', 'config');

        $adb = \PearDatabase::getInstance();

        $sqlSet = array();
        $params = array();

        if(!empty($data['folder'])) {
            $sql = 'UPDATE vtiger_wf_mailscanner_folder SET dirty = 1 WHERE mailscanner_id = ?';
            $adb->pquery($sql, array($this->_id));

            foreach($data['folder'] as $folder) {
                $sql = 'SELECT id FROM vtiger_wf_mailscanner_folder WHERE folder = ? AND mailscanner_id = ?';
                $result = $adb->pquery($sql, array($folder, $this->_id));

                if($adb->num_rows($result) > 0) {
                    $sql = 'UPDATE vtiger_wf_mailscanner_folder SET dirty = 0 WHERE folder = ? AND mailscanner_id = ?';
                    $adb->pquery($sql, array($folder, $this->_id));
                } else {
                    $sql = 'INSERT INTO vtiger_wf_mailscanner_folder SET dirty = 0, folder = ?, mailscanner_id = ?, lastscan = "0000-00-00 00:00:00"';
                    $adb->pquery($sql, array($folder, $this->_id));
                }
            }

            $sql = 'DELETE FROM vtiger_wf_mailscanner_folder WHERE mailscanner_id = ? and dirty = 1';
            $adb->pquery($sql, array($this->_id));

            unset($data['folder']);
        }

        if(!empty($data['provider_id'])) {
            $data['available_folder'] = array();
        }
        foreach($data as $key => $value) {
            if(!in_array($key, $fields)) continue;

            if(is_array($value)) {
                $value = VtUtils::json_encode($value);
            }

            $sqlSet[] = '`'.$key.'` = ?';
            $params[] = $value;
        }

        $params[] = $this->_id;

        if(!empty($sqlSet)) {
            $sql = 'UPDATE vtiger_wf_mailscanner SET ' . implode(',', $sqlSet) . ' WHERE id = ?';
            $adb->pquery($sql, $params);
        }
    }

    public function getImapFolders() {
        $data = $this->getData();

        if(empty($data['provider_id'])) {
            return array();
        }

        if(!empty($data['available_folder'])) {
            return $data['available_folder'];
        }

        /**
         * @var $provider IMAP
         */
        $provider = ConnectionProvider::getConnection($data['provider_id']);

        $folders = $provider->getFolder();

        $this->setData(array('available_folder' => $folders));

        return $folders;
    }

    public function getFolderObject($folder) {
        $data = $this->getData();

        /**
         * @var $provider IMAP
         */
        $provider = ConnectionProvider::getConnection($data['provider_id']);

        return $provider->getFolderObject($folder);
    }

    /**
     * @return Message
     */
    public static function getCurrentMessage() {
        return self::$CurrentMail;
    }

    /**
     * @return Mailscanner
     */
    public static function getCurrentMailscanner() {
        return self::$Instance;
    }

    public static function checkCron() {
        $adb = \PearDatabase::getInstance();

        $cron = array(
            'name' => 'Workflow2 MailScanner',
            'handler_file' => 'modules/Workflow2/MailScannerCron.php',
            'frequency' => '900',
            'module' => 'Workflow2',
            'desc' => 'Check every 15 minutes if Workflow Mailscanner must done something',
        );

        $sql = 'SELECT * FROM vtiger_cron_task WHERE name = ?';
        $result = $adb->pquery($sql, array($cron['name']));
        if($adb->num_rows($result) > 0) {
            $sql = 'UPDATE vtiger_cron_task SET status = 1, handler_file = "'.$cron['handler_file'].'" WHERE id = '.$adb->query_result($result, 0, 'id');
            $adb->query($sql);
        } else {
            \Vtiger_Cron::register($cron['name'], $cron['handler_file'],$cron['frequency'], $cron['module'], 1, \Vtiger_Cron::nextSequence(), $cron['desc']);
        }
    }

}