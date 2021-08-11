<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskCampaign_result extends \Workflow\Task
{
    protected $_internalConfiguration = true;
    protected $_configFields = array(
        "Configuration" => array(
            array(
                "key" => 'recordids',
                "label" => '$env Variable der RecordIDs',
                "type" => "templatefield"
            ),
        ),
        "OUTPUT" => array(
            array(
                "key" => "env_output",
                "label" => 'HTML Output $env Variable',
                "type" => "envvar"
            ),
        )
    );

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
        $available_status = array(
            '1' => 'verfügbar',
            '0' => 'unbrauchbar',
            '4' => 'kein Interesse',
            '3' => 'Information Versand',
            '5' => 'Info Versand KI',

            '2' => 'Termin vereinbart',
            '6' => 'Termin KI',

            '7' => 'Angebot',
        );

        // load values from request values popup
        $env = $context->getEnvironment('value');

        // security check to only query dates
        $date_from = date('Y-m-d', strtotime($env['date_from']));
        $date_to = date('Y-m-d', strtotime($env['date_to']));

        $envVar = $this->get('recordids');
        $recordids = $context->getEnvironment($envVar);
        if(empty($recordids)) {
            $recordids = $context->getId();
        }
        $recordids = preg_replace('/[^0-9,]/', '', $recordids);
        $recordids = explode(',', $recordids);

        $adb = \PearDatabase::getInstance();

        $tableName = 'vtiger_campaigncontrel';
        $sql = 'SELECT campaignid, '.$tableName.'.campaignrelstatusid, COUNT(*) as num
                FROM '.$tableName.'
                WHERE ((calldate >= ? AND calldate <= ?) OR campaignrelstatusid = 1) AND campaignid IN ('.generateQuestionMarks($recordids).') GROUP BY campaignid, '.$tableName.'.campaignrelstatusid';
        $params = $adb->flatten_array(array($date_from, $date_to, $recordids));

        $this->addStat($adb->convert2Sql($sql, $params));

        $result = $adb->pquery($sql, $params, true);

        $campaigns = array();
        while($row = $adb->fetchByAssoc($result)) {
            $campaigns[$row['campaignid']][$row['campaignrelstatusid']] = $row['num'];
        }

        $table = '<table border="0" cellpadding="5">';

        $table .= '<tr>';
        $table .= '<td>Kampagne</td>';

        foreach($available_status as $id => $label) {
            $ids[] = $id;
            $table .= '<td style="padding:5px 10px;"><strong>'.vtranslate($label, 'Campaigns').'</strong></td>';
        }
/*
        $sql = 'SELECT * FROM vtiger_campaignrelstatus ORDER BY sortorderid';
        $result = $adb->query($sql, true);

        $ids = array();
        while($row = $adb->fetchByAssoc($result)) {
            $ids[] = $row['campaignrelstatusid'];

        }
*/
        $table .= '</tr>';
        foreach($campaigns as $campaignid => $status) {
            $campaign = \Workflow\VTEntity::getForId($campaignid, 'Campaigns');

            $table .= '<tr>';
            $table .= '<td style="padding:5px 10px 5px 5px;">'.$campaign->get('campaignname').'</td>';

            foreach($ids as $statusid) {
                $table .= '<td align="right">'.(!isset($status[$statusid])?0:intval($status[$statusid])).'</td>';
            }

            $table .= '</tr>';
        }

        $table .= '</table>';

        $newEnvId = $this->get('env_output');

        $context->setEnvironment($newEnvId, $table);

		return "yes";
    }

}
