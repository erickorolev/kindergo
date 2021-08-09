<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\RelationAddExtend;

class Documents extends \Workflow\RelationAddExtend {
    private static $Cache = array();

    protected $_relatedModule = 'Documents';
    protected $_title = 'Documents';

    public function addRelatedRecord($sourceRecordId, $targetRecordId) {
        $adb = \PearDatabase::getInstance();

        $sql = "INSERT IGNORE INTO vtiger_senotesrel SET crmid = ?, notesid = ?";
        $adb->pquery($sql, array(intval($targetRecordId), intval($sourceRecordId)));

        return true;
    }

}

\Workflow\RelationAddExtend::register(str_replace('.inc.php', '', basename(__FILE__)), '\Workflow\Plugins\RelationAddExtend\Documents');