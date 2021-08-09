<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

/* vt6 compatible 2014/04/09 */
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskModcomment extends \Workflow\Task
{
    public function init() {
        $this->addPreset("Attachments", "files", array(
            'module' => $this->getModuleName()
        ));
    }

    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        global $current_user;

        $comment = $this->get("comment", $context);
        $relRecord = $this->get("relRecord");

        if(empty($relRecord)) {
            $targetID = array($context->getId());
        } elseif($relRecord === 'custom') {

            $customid = $this->get('customid', $context);
            if(strpos($customid, ',') !== false) {
                $targetID = explode(',', $customid);
            } else {
                $targetID = array($customid);
            }

            foreach($targetID as $index => $value) {
                $targetID[$index] = $context->getCrmId($value);
            }
        } else {
            $targetID = $context->get($relRecord);

            $targetID = array($context->getCrmId($targetID));
        }

        global $currentModule;
        $oldCurrentModule = $currentModule;
        $currentModule = "ModComments";

        if($this->notEmpty('authorType')) {
            $authorType = $this->get('authorType');
            $authorid = $this->get('authorid', $context);
        } else {
            $authorType = 'currentuser';
        }

        global $oldCurrentUser;
        $currentUserModel = $current_user;
        if(!empty($oldCurrentUser)) {
            $currentUserModel = $oldCurrentUser;
        }

        $commentIds = array();
        foreach($targetID as $id) {
            $recordModel = Vtiger_Record_Model::getCleanInstance("ModComments");
            $recordModel->getData();
            $recordModel->set('mode', '');

            $recordModel->set("commentcontent", $comment);
            $recordModel->set("related_to", $id);
            $recordModel->set("is_private", $this->get('private') == '1' ? '1' : '0');

            if($authorType == 'currentuser') {
                $recordModel->set("assigned_user_id", $currentUserModel->id);
                $recordModel->set("userid", $currentUserModel->id);
            } elseif($authorType == 'userid') {
                $recordModel->set("userid", $authorid);
                $recordModel->set("assigned_user_id", $authorid);
            } elseif($authorType == 'contactid') {
                $recordModel->set("customer", $authorid);
            }

            $recordModel->save();
            $commentIds[] = $recordModel->getId();

            //var_dump(\Workflow\VTEntity::getForId($recordModel->getId())->getData());
        }

        // When files are attached, then add to every comment
        if($this->notEmpty('files')) {
            $workingFiles = array();
            $files = json_decode($this->get("files"), true);
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
            $fileIds = array();

            $firstComment = CRMEntity::getInstance('ModComments');
            foreach ($workingFiles as $file) {
                $fileDetails = array(
                    'name' => $file['filename'],
                    'tmp_name' => $file['path'],
                    'type' => Vtiger_Functions::mime_content_type($file['filepath']),
                    'size' => filesize($file['filepath']),
                    'error' => '',
                );
                $fileIds[] = $firstComment->uploadAndSaveFile($commentIds[0], 'ModComments', $fileDetails);
            }

            if (count($commentIds) > 1) {
                $adb = \PearDatabase::getInstance();
                for ($i = 1; $i < count($commentIds); $i++) {

                    foreach ($fileIds as $fileId) {
                        $adb->pquery('INSERT INTO vtiger_seattachmentsrel(crmid,attachmentsid) VALUES(?,?)', array($commentIds[$i], $fileId));
                    }
                }
            }
        }

        $currentModule = $oldCurrentModule;

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb, $app_strings;

        $fields = \Workflow\VtUtils::getFieldsForModule($this->getModuleName(), array(51,57,58,59,73,75,81,76,78,80,68,10));

        $references = array();

        foreach($fields as $field) {
            switch ($field->uitype) {
                case "51":
                   $module = "Accounts";
                break;
                case "57":
                   $module = "Contacts";
                   break;
                case "58":
                    $module = "Campaigns";
                   break;
                case "59":
                    $module = "Products";
                   break;
                case "73":
                    $module = "Accounts";
                   break;
                case "75":
                    $module = "Vendors";
                   break;
                case "81":
                    $module = "Vendors";
                   break;
                case "76":
                   $module = "Potentials";
                   break;
                case "78":
                    $module = "Quotes";
                   break;
                case "80":
                    $module = "SalesOrder";
                   break;
                case "68":
                    $module = "Accounts";
                       break;
                case "10": # Possibly multiple relations
                        $result = $adb->pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', array($field->id));
                        while ($data = $adb->fetch_array($result)) {
                            $module = $data["relmodule"];
                        }
                    break;
            }
            $field->targetModule = !empty($app_strings[$module])?$app_strings[$module]:$module;
            $references[] = $field;
        }

        $viewer->assign("references", $references);
    }
}