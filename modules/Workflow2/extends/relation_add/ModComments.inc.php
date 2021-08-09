<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\RelationAddExtend;

class ModComments extends \Workflow\RelationAddExtend {
    private static $Cache = array();

    /*
     * Must be equal to classname and filename!!!
     */
    protected $_relatedModule = 'ModComments';
    protected $_title = 'Comments';

    public function addRelatedRecord($sourceRecordId, $targetRecordId) {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT createdtime, modifiedtime, smownerid, modifiedby FROM vtiger_crmentity WHERE crmid = ?';
        $result = $adb->pquery($sql, array(intval($sourceRecordId)));
        $data = $adb->fetchByAssoc($result);

        $sql = 'SELECT modcommentsid FROM vtiger_modcomments
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = modcommentsid)
                WHERE related_to = ? AND smownerid = ? AND createdtime = ? AND commentcontent = (SELECT commentcontent FROM vtiger_modcomments WHERE modcommentsid = ?)';

        $result = $adb->pquery($sql, array(
            $targetRecordId,
            $data['smownerid'],
            $data['createdtime'],
            intval($sourceRecordId)
        ));
        if($adb->num_rows($result) > 0) {
            return true;
        }

        $record = \ModComments_Record_Model::getInstanceById(intval($sourceRecordId));
        $recordModel = \Vtiger_Record_Model::getCleanInstance("ModComments");
        $recordModel->getData();
        $recordModel->set('mode', '');

        $recordModel->set("commentcontent", $record->get('commentcontent'));
        $recordModel->set("related_to", $targetRecordId);

        $recordModel->set("assigned_user_id", $record->get('assigned_user_id'));
        $recordModel->set("userid", $record->get('userid'));

        $recordModel->save();

        $sql = 'UPDATE vtiger_crmentity SET createdtime = ?, modifiedtime = ?, smownerid = ?, modifiedby = ? WHERE crmid = ?';
        $adb->pquery($sql, array(
            $data['createdtime'],
            $data['modifiedtime'],
            $data['smownerid'],
            $data['modifiedby'],
            $recordModel->getId(),
        ));

        return true;
    }

}

\Workflow\RelationAddExtend::register(str_replace('.inc.php', '', basename(__FILE__)), '\Workflow\\Plugins\\RelationAddExtend\\'.str_replace('.inc.php', '', basename(__FILE__)));