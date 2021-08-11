<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.06.15 16:21
 * You must not use this file without permission.
 */
namespace Workflow\Plugin;

class DocumentsConditionOperator extends \Workflow\ConditionPlugin
{
    public function getOperators($moduleName) {
        if($moduleName != 'Documents') {
            return array();
        }

        $operators = array(
            'related_documents' => array (
                'config' => array (
                    'related_to' => array (
                        'type' => 'default',
                        'label' => 'Related to this ID ',
                        'default' => '$crmid',
                    ),
                ),
                'label' => 'is related to',
                'fieldtypes' => array ('crmid'),
            ),
        );

        return $operators;
    }

    public function generateSQLCondition($key, $columnName, $config, $not) {
        $adb = \PearDatabase::getInstance();

        if(is_string($config)) {
            $config = array('related_to' => $config);
        }

        // default calculations
        switch($key) {
            case 'related_documents':
                // Tested by swa 2016-01-28
                return "".$columnName." " . ($not ? "!" : "" ) . " IN (SELECT notesid FROM vtiger_senotesrel WHERE crmid = ".intval($config['related_to']).")";
                break;
        }

    }

    public function checkValue($context, $key, $fieldvalue, $config, $checkConfig)
    {
        // old check functions
        switch ($key) {
            case "related_documents":
                $adb = \PearDatabase::getInstance();
                $sql = 'SELECT crmid FROM vtiger_senotesrel WHERE crmid = ? AND notesid = ?';
                $result = $adb->pquery($sql, array($config['related_to'], $context->getId()));

                if($adb->num_rows($result) > 0) {
                    return true;
                } else {
                    return false;
                }
            break;
        }

        return false;
    }
}

\Workflow\ConditionPlugin::register('documents', '\\Workflow\\Plugin\\DocumentsConditionOperator');