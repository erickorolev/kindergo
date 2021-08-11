<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\RelationAddExtend;

use Workflow\VtUtils;

class EditableCoreRelation extends \Workflow\RelationAddExtend {
    protected $_hasSupport = array('add');

    private $_relationId = null;
    public function setRelatedModule($moduleName, $relation_id, $title) {
        $this->_relatedModule = $moduleName;
        $this->_title = $title;
        $this->_relationId = $relation_id;
    }

    public function isActive($moduleName) {
        return true;
    }

    public function addRelatedRecord($sourceRecordId, $targetRecordId) {
        $sourceModuleModel = \Vtiger_Module_Model::getInstance(\Workflow\VtUtils::getModuleNameForCRMID($targetRecordId));
        $relatedModuleModel = \Vtiger_Module_Model::getInstance($this->getRelatedModule());

        $relationModel = \Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

        $relationModel->addRelation($targetRecordId, $sourceRecordId);

        return true;
    }

    // getQuery is not required, because CoreRelation will do this part
    // EditableCoreRelation only must handle CoreRElation modifications

    /**
     * @param $moduleName
     * @return array
     */
    public static function getAvailableRelatedLists($moduleName) {
        $supported = array(
            'global' => array('get_contacts', 'get_accounts', 'get_campaigns', 'get_leads'),
           // 'Potentials' => array('get_contacts'),
        );

        if(!isset($supported[$moduleName])) {
            $supported[$moduleName] = array();
        }

        $relations = array_merge($supported['global'], $supported[$moduleName]);
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT relation_id, tabid, related_tabid, label FROM vtiger_relatedlists WHERE tabid = '.getTabId($moduleName).' AND name IN ('.generateQuestionMarks($relations).')';
        $result = $adb->pquery($sql, $relations, true);

        $items = array();
        while($row = $adb->fetchByAssoc($result)) {
            $relatedModule = VtUtils::getModuleName($row['related_tabid']);

            /**
             * @var RelatedLists $obj
             */
            $obj = new self('EditableCoreList@'.$row['relation_id'].'@'.$relatedModule);
            $obj->setRelatedModule('EditableCoreList@'.$row['relation_id'].'@'.$relatedModule, $row['relation_id'], vtranslate($row['label'], $moduleName));

            $items[] = $obj;
        }

        return $items;
    }

}

