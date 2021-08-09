<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\FileActions;

use Workflow\VTTemplate;

class Documents extends \Workflow\FileAction {

    public function getActions($moduleName) {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_attachmentsfolder ORDER BY foldername';
        $result = $adb->query($sql);

        $folders = array();
        while($row = $adb->fetchByAssoc($result)) {
            $folders[$row['folderid']] = $row['foldername'];
        }

        $tmpWorkflows = \Workflow2::getWorkflowsForModule("Documents", 1);

        $workflows = array('' => '--- choose Workflow ---');
        foreach($tmpWorkflows as $id => $workflow) {
            $workflows[$id] = $workflow['title'];
        }

        $return = array(
            'id' => 'documents',
            'title' => 'Store in Documents Module',
            'options' => array(
                'title' => array(
                    'type' => 'templatefield',
                    'label' => 'LBL_DOCUMENT_TITLE',
                    'placeholder' => 'The title of the Documents Record',
                ),
                'description' => array(
                    'type' => 'templatearea',
                    'label' => 'LBL_DOCUMENT_DESCR',
                    'placeholder' => 'Optionally a description, stored in the record',
                ),
                'folderid' => array(
                    'type' => 'picklist',
                    'label' => 'LBL_FOLDER',
                    'options' => $folders
                ),
                'workflowid' => array(
                    'type' => 'picklist',
                    'label' => 'execute this workflow<br>with the new Document',
                    'options' => $workflows
                ),
                'relation' => array(
                    'type' => 'checkbox',
                    'label' => 'create relationship to record',
                    'value' => '1'
                ),
                'relationcrmid' => array(
                    'type' => 'templatefield',
                    'label' => 'use this Record IDs for Relationship',
                    'description' => '(Default current Records)',
                ),
                'envId' => array(
                    'type' => 'envname',
                    'label' => 'store Document ID in this $env Variable',
                    'value' => '1'
                ),
            )
        );

        return $return;
    }

    /**
     * @param $key
     * @param $value
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function doAction($configuration, $filepath, $filename, $context, $targetRecordIds = array()) {
        $adb = \PearDatabase::getInstance();

        require_once('modules/Documents/Documents.php');
        $focus = new \Documents();

        $focus->parentid = $context->getId();

        $docTitle = $configuration["title"];
        $docDescr = nl2br($configuration["description"]);

        $docTitle = \Workflow\VTTemplate::parse($docTitle, $context);
        $docDescr = \Workflow\VTTemplate::parse($docDescr, $context);

        $focus->column_fields['notes_title'] = $docTitle;
        $focus->column_fields['assigned_user_id'] = $context->get('assigned_user_id');
        $focus->column_fields['filename'] = $filename;
        $focus->column_fields['notecontent'] = $docDescr;
        $focus->column_fields['filetype'] = 'application/pdf';
        $focus->column_fields['filesize'] = filesize($filepath);
        $focus->column_fields['filelocationtype'] = 'I';
        $focus->column_fields['fileversion'] = '';
        $focus->column_fields['filestatus'] = 'on';
        $focus->column_fields['folderid'] = $configuration["folderid"];

      	$focus->save('Documents');

        $upload_file_path = decideFilePath();

        $date_var = date("Y-m-d H:i:s");
        $next_id = $adb->getUniqueID("vtiger_crmentity");

        copy($filepath, $upload_file_path . $next_id . "_" . $filename);

        $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
        $params1 = array($next_id, $context->get('assigned_user_id'), $context->get('assigned_user_id'), "Documents Attachment",'Documents Attachment', date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));

        $adb->pquery($sql1, $params1);
        $filetype = "application/octet-stream";

        $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
        $params2 = array($next_id, $filename, $docDescr, $filetype, $upload_file_path);

        $adb->pquery($sql2, $params2, true);

        $sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
        $adb->pquery($sql3, array($focus->id, $next_id));

        if($configuration["relation"] === "1") {
            if(!empty($configuration['relationcrmid'])) {
                $relationcrmids = VTTemplate::parse($configuration['relationcrmid'], $context);
                $targetRecordIds = explode(',', $relationcrmids);
            }
            foreach($targetRecordIds as $id) {
                $sql = "INSERT INTO vtiger_senotesrel SET crmid = ".$id.", notesid = ".$focus->id;
                $adb->query($sql);
            }
        } else {
            $sql = "DELETE FROM vtiger_senotesrel WHERE crmid = ".$context->getId()." AND notesid = ".$focus->id;
            $adb->query($sql);
        }

        $newContext = \Workflow\VTEntity::getForId($focus->id, "Documents");

        if(!empty($configuration["envId"])) {
            $context->setEnvironment($configuration["envId"], $newContext->getId());
        }

        if(!empty($configuration['workflowid'])) {
            $objWorkflow = new \Workflow\Main($configuration['workflowid'], false, $context->getUser());

            $objWorkflow->setContext($newContext);
            $objWorkflow->isSubWorkflow(true);

            $objWorkflow->start();
        }

    }

}

\Workflow\FileAction::register('documents', '\Workflow\Plugins\FileActions\Documents');