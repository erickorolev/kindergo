<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMailscanner_mailmove extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;

    public function init()
    {
        $this->_SC = $this->addPreset("SimpleConfig", "details", array(
            'templatename' => 'mainconfig',
        ));

        if($this->isConfiguration()) {
            $this->_SC->setColumnCount(1);

            $sql = 'SELECT * FROM vtiger_wf_mailscanner ORDER BY title';
            $result = \Workflow\VtUtils::query($sql);
            $options = array();

            while($row = \Workflow\VtUtils::fetchByAssoc($result)) {
                $options[$row['id']] = $row['title'];
            }

            $this->_SC->addFields('mailscanner', 'Mailscanner configuration', 'select', array(
                'options' => $options,
                'description' => 'Select Mailscanner to load folderlist',
            ));

            if($this->_SC->has('mailscanner')) {
                $mailscannerId = $this->_SC->get('mailscanner');

                if(!empty($mailscannerId)) {
                    $obj = new \Workflow\Mailscanner($mailscannerId);
                    $folders = $obj->getImapFolders();
                    $folderList = array();
                    foreach($folders as $folder) {
                        $folderList[$folder['name']] = $folder['name'];
                    }

                    $this->_SC->addFields('folder', 'Select new folder', 'select', array(
                        'options' => $folderList,
                        'description' => 'MOve eMail to this folder',
                    ));

                }
            }
        }
    }

    public function handleTask(&$context) {
        /* Insert here source code to execute the task */

        $mail = \Workflow\Mailscanner::getCurrentMessage();
        $mailscanner = \Workflow\Mailscanner::getCurrentMailscanner();

        if($this->_SC->has('folder') == false) {
            return 'yes';

        }

        $this->addStat('Move mail to folder '.$this->_SC->get('folder'));

        $mailbox = $mailscanner->getFolderObject($this->_SC->get('folder'));

        $mail->move($mailbox);

        $mailbox->expunge();

        return "yes";
    }

    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
