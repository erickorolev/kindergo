<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

namespace Workflow\Plugin;


use Workflow\VtUtils;

class CoreVT7ConditionOperator extends \Workflow\ConditionPlugin
{

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>'))
     * @param $moduleName
     * @return mixed
     */
    public function getOperators($moduleName) {
        $result = VtUtils::query('SELECT id, tag FROM vtiger_freetags ORDER BY tag');
        $tags = array();

        while($tag = VtUtils::fetchByAssoc($result)) {
            $tags[$tag['id']] = $tag['tag'];
        }


        $operators = array(
            'hastag' => array(
                'config' => array(
                    'value' => array(
                        'type' => 'multipicklist',
                        'width' => '70%',
                        'options' => $tags
                    ),
                    'all' => array(
                        'type' => 'picklist',
                        'width' => '30%',
                        'options' => array(
                            'onetag' => 'at least one match',
                            'alltag' => 'all tags',
                        )
                    )
                ),
                'label' => 'has Tag',
                'fieldtypes' => array('crmid'),
            ),
        );

        return $operators;
    }

    public function generateSQLCondition($key, $columnName, $config, $not) {
        switch($key) {
            case 'hastag':
                if($config['alltag'] == 'onetag') {
                    $minNumMatches = '>= 1';
                } else {
                    $minNumMatches = '= '.count($config['value']);
                }

                $sqlQuery = 'SELECT object_id FROM (SELECT object_id, COUNT(*) as num FROM vtiger_freetagged_objects WHERE tag_id IN ('.implode(',', $config['value']).') GROUP BY object_id) as t2 WHERE t2.num '.$minNumMatches;
                return "".$columnName." " . ($not ? "NOT" : "" ) . " IN (".$sqlQuery.")";
            break;
        }
    }

    public function checkValue($context, $key, $fieldValue, $config, $checkConfig)
    {
        switch($key) {
            case 'hastag':
                if(empty($config['value'])) {
                    return false;
                }

                $crmid = $fieldValue;
                $result = VtUtils::query('SELECT COUNT(*) as num FROM vtiger_freetagged_objects WHERE tag_id IN ('.implode(',', $config['value']).') AND object_id = "'.$crmid.'"');
                $row = VtUtils::fetchByAssoc($result);

                if($config['all'] == 'alltag') {
                    if($row['num'] >= count($config['value'])) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if($row['num'] > 0) {
                        return true;
                    } else {
                        return false;
                    }

                }
                break;
        }
    }
}

\Workflow\ConditionPlugin::register('corevt7', '\\Workflow\\Plugin\\CoreVT7ConditionOperator');