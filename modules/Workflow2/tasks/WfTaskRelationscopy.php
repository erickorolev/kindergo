<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskRelationscopy extends \Workflow\Task
{
    public function handleTask(&$context) {
        /* Insert here source code to execute the task */
        $adb = \PearDatabase::getInstance();

        $relations = $this->get('relations');

        if(!empty($relations)) {
            foreach($relations as $relationID) {
                $srcCRMID = $this->get('crmid_src', $context);
                if(empty($srcCRMID)) {
                    $srcCRMID = $context->getId();
                }

                $targetCRMID = $this->get('crmid_dest', $context);

                switch($relationID) {
                    case 'attachments':
                        $targetContext = \Workflow\VTEntity::getForId($targetCRMID);

                        if($targetContext->getModuleName() == 'Emails') {

                            $sql = 'SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?';
                            $result = $adb->pquery($sql, array($srcCRMID));

                            while($row = $adb->fetchByAssoc($result)) {
                                $sql = 'INSERT INTO vtiger_seattachmentsrel SET crmid = ?, attachmentsid = ?';
                                $adb->pquery($sql, array($srcCRMID, $targetCRMID));
                            }

                        } else {

                            $sql = 'SELECT * FROM vtiger_seattachmentsrel 
										INNER JOIN vtiger_attachments ON (vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid) 
										INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_seattachmentsrel.attachmentsid) 
										WHERE vtiger_seattachmentsrel.crmid = ?';
                            $result = $adb->pquery($sql, array($srcCRMID));

                            require_once('modules/Documents/Documents.php');

                            while($row = $adb->fetchByAssoc($result)) {
                                $focus = new \Documents();

                                $finfo = finfo_open(FILEINFO_MIME_TYPE); // gib den MIME-Typ nach Art der mimetype Extension zurück
                                $filepath = \VtigerConfig::get('root_directory') . $row['path'] . $row['attachmentsid'].'_'.$row['name'];
                                $mime = finfo_file($finfo, $filepath);

                                $focus->parentid = $context->getId();

                                $focus->column_fields['notes_title'] = 'Attachment '.$row['name'];
                                $focus->column_fields['assigned_user_id'] = $row['smownerid'];
                                $focus->column_fields['filename'] = $row['name'];
                                $focus->column_fields['notecontent'] = 'Attached by E-Mail';
                                $focus->column_fields['filetype'] = $mime;
                                $focus->column_fields['filesize'] = filesize($filepath);
                                $focus->column_fields['filelocationtype'] = 'I';
                                $focus->column_fields['fileversion'] = '';
                                $focus->column_fields['filestatus'] = 'on';
                                $focus->column_fields['folderid'] = $configuration["folderid"];

                                $focus->save('Documents');

                                $sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
                                $adb->pquery($sql3, array($focus->id, $row['attachmentsid']));
                            }

                        }

                        break;
                    case 'modcomments':
                        $sql = "SELECT commentcontent, userid, customer, first_name, last_name, createdtime, modifiedtime, vtiger_crmentity.smownerid
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE ( ((`vtiger_modcomments`.`related_to` = '".$srcCRMID."')) ) AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_crmentity.crmid  ORDER BY createdtime DESC";
                        $result = $adb->query($sql);
                        if($adb->num_rows($result) == 0) return '';

                        while($row = $adb->fetchByAssoc($result)) {
                            $recordModel = Vtiger_Record_Model::getCleanInstance("ModComments");
                            $recordModel->getData();
                            $recordModel->set('mode', '');

                            $recordModel->set("commentcontent", html_entity_decode($row['commentcontent']));
                            $recordModel->set("related_to", $targetCRMID);

                            $recordModel->set("assigned_user_id", $row['smownerid']);
                            $recordModel->set("userid", $row['userid']);

                            $recordModel->save();

                            $sql = 'UPDATE vtiger_crmentity SET createdtime = ?, modifiedtime = ? WHERE crmid = ?';
                            $adb->pquery($sql, array($row['createdtime'], $row['modifiedtime'], $recordModel->getId()));
                        }

                        break;
                    default:
                        $sql = 'SELECT * FROM vtiger_relatedlists WHERE relation_id = ?';
                        $result = $adb->pquery($sql, array($relationID));
                        $data = $adb->fetchByAssoc($result);

                        switch($data['name']) {
                            case 'get_attachments':
                                $relationTable = 'vtiger_senotesrel';
                                $crmIdColumn = 'crmid';
                                $destinationIdColumn = 'notesid';

                                $this->copyRelations($relationTable, $crmIdColumn, $destinationIdColumn, $srcCRMID, $targetCRMID);
                                break;
                            case 'get_activities':
                                $relationTable = 'vtiger_seactivityrel';
                                $crmIdColumn = 'crmid';
                                $destinationIdColumn = 'activityid';

                                $this->copyRelations($relationTable, $crmIdColumn, $destinationIdColumn, $srcCRMID, $targetCRMID);
                                break;
                            default:
                                $srcModule = \Workflow\VtUtils::getModuleName($this->get('search_module'));
                                $targetModule = \Workflow\VtUtils::getModuleName($data['related_tabid']);

                                $sql = 'SELECT * FROM vtiger_crmentityrel WHERE (crmid = ? AND module = ? and relmodule = ?) OR (relcrmid = ? AND module = ? and relmodule = ?)';
                                $result = $adb->pquery($sql, array($srcCRMID, $srcModule, $targetModule, $srcCRMID, $targetModule, $srcModule));

                                while ($row = $adb->fetchByAssoc($result)) {
                                    if ($row['crmid'] == $srcCRMID) {
                                        $newRelId = $row['relcrmid'];
                                    } else {
                                        $newRelId = $row['crmid'];
                                    }

                                    $sql = 'SELECT * FROM vtiger_crmentityrel WHERE crmid = ? AND module = ? AND relmodule = ? AND relcrmid = ?';
                                    $result2 = $adb->pquery($sql, array($targetCRMID, $srcModule, $targetModule, $newRelId));

                                    if($adb->num_rows($result2) == 0) {

                                        $sql = 'INSERT INTO vtiger_crmentityrel SET crmid = ?, module = ?, relmodule = ?, relcrmid = ?';
                                        $adb->pquery($sql, array($targetCRMID, $srcModule, $targetModule, $newRelId));

                                    }
                                }

                                break;
                        }
                }
            }
        }

        return "yes";
    }

    public function copyRelations($relationTable, $crmIdColumn, $destinationIdColumn, $srcCRMID, $targetCRMID) {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM '.$relationTable.' WHERE '.$crmIdColumn.' = ?';
        $result = $adb->pquery($sql, array($srcCRMID));

        while ($row = $adb->fetchByAssoc($result)) {

            $sql = 'SELECT * FROM '.$relationTable.' WHERE '.$crmIdColumn.' = ? AND '.$destinationIdColumn.' = ?';
            $resultCheck = $adb->pquery($sql, array($targetCRMID, $row[$destinationIdColumn]));

            if($adb->num_rows($resultCheck) == 0) {
                $sql = 'INSERT INTO '.$relationTable.' SET '.$crmIdColumn.' = ?, '.$destinationIdColumn.' = ?';
                $adb->pquery($sql, array($targetCRMID, $row[$destinationIdColumn]));

            }
        }
    }
    public function beforeGetTaskform($viewer) {
        $adb= \PearDatabase::getInstance();

        if($this->notEmpty('search_module')) {
            $searchModule = $this->get('search_module');

            $tabid = getTabId($searchModule);
            $sql = 'SELECT * FROM vtiger_relatedlists WHERE (tabid = ' . $tabid . ' OR related_tabid = ' . $tabid . ') AND name IN ("get_related_list","get_activities","get_attachments") ORDER BY tabid = ' . $tabid . ' DESC';
            $result = $adb->query($sql);

            $relatedLists = array();

            $already = array();

            while ($row = $adb->fetchByAssoc($result)) {
                if($row['tabid'] == $tabid) {
                    $targetTabId = $row['related_tabid'];
                } else {
                    $targetTabId = $row['tabid'];
                }

                $srcMod = $row['tabid'];

                if(isset($already[$targetTabId])) {
                    continue;
                }
                $already[$targetTabId] = true;
                $moduleName = \Workflow\VtUtils::getModuleName($targetTabId);
                $srcmoduleName = \Workflow\VtUtils::getModuleName($srcMod);

                $relatedLists[] = array(
                    "relation_id" => $row['relation_id'],
                    "related_tabid" => $targetTabId,
                    "module_name" => $moduleName,
                    //"action" => 'get_comments',
                    "label" => '['.getTranslatedString($moduleName, $moduleName).'] '.getTranslatedString($row['label'], $moduleName),
                );

            }

            $relatedLists[] = array(
                "relation_id" => 'modcomments',
                "related_tabid" => 46,
                "module_name" => 'ModComments',
                //"action" => 'get_comments',
                "label" => '['.getTranslatedString('ModComments', 'ModComments').'] '.getTranslatedString('LBL_RECORDS_LIST', 'ModComments'),
            );

            if($searchModule == getTabId('Emails')) {
                $relatedLists[] = array(
                    "relation_id" => 'attachments',
                    "related_tabid" => 46,
                    "module_name" => 'Attachments',
                    //"action" => 'get_comments',
                    "label" => '[Attachments] E-Mail Attachments',
                );
            }

            $viewer->assign("available_relations", $relatedLists);
        }

        $viewer->assign("related_modules", VtUtils::getEntityModules(true));

        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
