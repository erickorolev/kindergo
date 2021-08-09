<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_StatistikDetails_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $recordsPerPage = 10;

        $page = $request->get('page');
        if(empty($page)) {
            $page = 1;
        }

        try {
            $source = $request->get("source");
            $destination = $request->get("destination");

            $startDate = date("Y-m-d", strtotime($request->get("startDate")));
            $endDate = date("Y-m-d", strtotime($request->get("endDate")));

            $module_name = $request->get("module_name");
            $crmid = $request->get('crmid');
            if(empty($crmid) || is_numeric($crmid)) {
                $crmid = intval($crmid);
            } else {
                $result = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE label = ?', array($crmid));
                if($adb->num_rows($result)) {
                    $crmid = $adb->query_result($result, 0, 'crmid');
                } else {
                    $crmid = 0;
                }
            }

            $sourceParts = explode("__", $source);
            $destParts = explode("__", $destination);

            $sql = "SELECT SQL_CALC_FOUND_ROWS vtiger_crmentity.label as link_field, vtiger_wf_log.crmid, vtiger_wf_log.timestamp, vtiger_wf_log.execID
                    FROM vtiger_wf_log
                    LEFT JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_wf_log.crmid)
                WHERE lastBlockId = '".$sourceParts[1]."' AND lastBlockOutput = '".$sourceParts[2]."' AND blockID = '".$destParts[1]."' AND timestamp >= '".$startDate." 00:00:00' AND timestamp <= '".$endDate." 23:59:00' ".(!empty($crmid) ? ' AND vtiger_wf_log.crmid = '.$crmid:'')." ORDER BY timestamp DESC LIMIT ".(($page - 1) * $recordsPerPage).', '.$recordsPerPage;
            $result = $adb->query($sql, true);

            $sql2 = 'SELECT FOUND_ROWS() as num';
            $resultLimit = $adb->query($sql2);
            $numRows = $adb->query_result($resultLimit, 0, 'num');

            $data = array();
            while($row = $adb->fetch_array($result)) {
                if(empty($row["link_field"])) {
                    $row["link_field"] = 'no Record';
                }

                if(function_exists('mb_substr')) {
                    $data[] = array("title" => mb_substr($row["link_field"], 0, 25), "execID" => $row["execid"], "timestamp" => \Workflow\VtUtils::formatUserDate($row["timestamp"]), "crmid" => $row["crmid"], "url" => "index.php?module=".$module_name."&view=Detail&record=".$row["crmid"]);
                } else {
                    $data[] = array("title" => substr($row["link_field"], 0, 25), "execID" => $row["execid"], "timestamp" => \Workflow\VtUtils::formatUserDate($row["timestamp"]), "crmid" => $row["crmid"], "url" => "index.php?module=".$module_name."&view=Detail&record=".$row["crmid"]);
                }
            }

            echo \Workflow\VtUtils::json_encode(array("success" => true, "data" => $data, 'page' => $page, 'totalpages' => ceil($numRows / $recordsPerPage)));
            //$response->setResult();
        } catch(Exception $exp) {
            echo \Workflow\VtUtils::json_encode(array("success" => false, "error" => $exp->getMessage()));
        }

        //$response->emit();
    }

    # Source: http://sansiba.com/tut_farbverlauf.htm
    private function create_gradient($steps, $color1, $color2)
    {
        if($steps == 1) {
            return array($color1);
        }
        if($steps == 2) {
            return array($color1, $color2);
        }
        $r1=hexdec(substr($color1,1,2));
        $g1=hexdec(substr($color1,3,2));
        $b1=hexdec(substr($color1,5,2));

        $r2=hexdec(substr($color2,1,2));
        $g2=hexdec(substr($color2,3,2));
        $b2=hexdec(substr($color2,5,2));

        $diff_r=$r2-$r1;
        $diff_g=$g2-$g1;
        $diff_b=$b2-$b1;

        $colors = array();
        for ($i=0; $i<$steps; $i++)
        {
            $factor=$i / $steps;

            $r=round($r1 + $diff_r * $factor);
            $g=round($g1 + $diff_g * $factor);
            $b=round($b1 + $diff_b * $factor);

            $color="#" . sprintf("%02X",$r) . sprintf("%02X",$g) . sprintf("%02X",$b);
            $colors[] = $color;
        }

        return $colors;
    }

}