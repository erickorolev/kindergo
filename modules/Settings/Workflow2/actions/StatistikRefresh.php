<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_StatistikRefresh_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();

        try {
            /**
             * Folgende Query aufrufen um Statistik von Fehlern zu beseitigen!

             DELETE FROM vtiger_wf_log WHERE execID IN (SELECT log1.execID
             FROM `vtiger_wf_log2` as log1
             LEFT JOIN vtiger_wf_log2 as log2 ON(log2.execID = log1.execID AND log2.lastBlockID = 1 AND log2.blockID > 1)
             WHERE log1.workflow_id = 1 AND log1.lastBlockId = 0 AND log1.timestamp >= '2012-08-01 00:00:00' AND log1.timestamp <= '2012-09-20 23:59:00' AND log2.blockID IS NULL
             GROUP BY log1.execID)

             */

            $workflowID = (int)$request->get('workflow_id');

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

            if(!$request->has("startDate")) {
                $startDate = date("Y-m-d", strtotime("-4 week"));
            } else {
                $startDate = $request->get("startDate");
            }
            if(!$request->has("endDate")) {
                $endDate = date("Y-m-d");
            } else {
                $endDate = $request->get("endDate");
            }

            $sql = "SELECT id FROM `vtiger_wfp_blocks` WHERE workflow_id = ".$workflowID." AND type = 'start'";
            $result = $adb->query($sql);
            $startBlockID = $adb->query_result($result, 0, "id");

            // integrate lastBlockId != blockID because the start Numbers was too high
            $sql = "SELECT COUNT(*) as num FROM `vtiger_wf_log` WHERE ".(!empty($crmid) ? 'crmid = '.$crmid.' AND ':'')." workflow_id = ".$workflowID." AND lastBlockId = ".$startBlockID." AND lastBlockId != blockID AND timestamp >= '".$startDate." 00:00:00' AND timestamp <= '".$endDate." 23:59:00'";
            $result = $adb->query($sql);
            $startedRecords = $adb->query_result($result, 0, "num");

            /*
            $sql = "SELECT blockID, COUNT(*) as num FROM `vtiger_wf_log` WHERE workflow_id = ".$workflowID." AND timestamp >= '".$startDate." 00:00:00' AND timestamp <= '".$endDate." 23:59:00' GROUP BY blockID";
            $result = $adb->query($sql);
            while($row = $adb->fetch_array($result)) {
                $blockCalls[$row["blockid"]] = $row["num"];
            }

            $sql = "SELECT source_id, COUNT(*) as num FROM `vtiger_wfp_connections` WHERE workflow_id = ".$workflowID." GROUP BY source_id";
            $result = $adb->query($sql);
            while($row = $adb->fetch_array($result)) {
                $nextBlocks[$row["source_id"]] = $row["num"];
            }
            */
            $sql = "SELECT
                        lastBlockID, lastBlockOutput, blockID, COUNT(*) as num, vtiger_wfp_blocks.type, vtiger_wfp_connections.deleted
                    FROM vtiger_wf_log
                        LEFT JOIN `vtiger_wfp_blocks` ON(vtiger_wfp_blocks.id = vtiger_wf_log.lastBlockID)
                        INNER JOIN `vtiger_wfp_connections` ON(
                            vtiger_wfp_connections.source_id = vtiger_wf_log.lastBlockID AND
                            vtiger_wfp_connections.source_key = vtiger_wf_log.lastBlockOutput AND
                            vtiger_wfp_connections.destination_id = vtiger_wf_log.blockID
                        )
                    WHERE vtiger_wf_log.workflow_id = ".$workflowID." AND lastBlockID != 0 ".(!empty($crmid) ? ' AND vtiger_wf_log.crmid = '.$crmid:'')."
                            AND timestamp >= '".$startDate." 00:00:00' AND timestamp <= '".$endDate." 23:59:00' AND blockID != lastBlockID
                    GROUP BY lastBlockID, lastBlockOutput, blockID ORDER BY lastBlockID, lastBlockOutput, COUNT(*) DESC";
            $result = $adb->query($sql);

            $statistics = array();
            $counter = 0;
            $lastCounterBlockId = 0;
            $lastCounterBlockOutput = "";
            $maxPoints = 0;

            $gradients["active"] = $this->create_gradient(11, "#aac6e2", "#aac6e2");
            $gradients["inactive"] = $this->create_gradient(11, "#aac6e2", "#aac6e2");
            $overlayNumber = 0;

            while($row = $adb->fetch_array($result)) {

                if($row["lastblockid"] != $lastCounterBlockId) {

                    $lastCounterBlockId = $row["lastblockid"];

                    $maxPoints = $lastValue = $row["num"];
                    $counter = 0;

                    $lineWidth = 7;
                } elseif($maxPoints > $row["num"]) {
                    $counter++;
                }

            //            if($maxPoints > $row["num"] && ($lastCounterBlockOutput != $row["lastblockoutput"] || $counter == 0)) {
            //                $counter++;
            //                $lastCounterBlockOutput = $row["lastblockoutput"];
            //            }


            //            var_dump("LastID: ".$row["lastblockid"]);
                $percent = round($row["num"] / $startedRecords, 2);

                if($percent > 0.8) {
                    $lineWidth = 15;
                } elseif($percent > 0.7) {
                    $lineWidth = 12;
                } elseif($percent > 0.6) {
                    $lineWidth = 10;
                } elseif($percent >= 0.5) {
                    $lineWidth = 8;
                } elseif($percent >= 0.4) {
                    $lineWidth = 6;
                } elseif($percent >= 0.1) {
                    $lineWidth = 4;
                } elseif($percent <= 0.1) {
                    $lineWidth = 2;
                }

                if($row["deleted"] == "0") {
                    $color = $gradients["active"][$percent > 1?10:intval($percent * 10)];
                } else {
                    $color = $gradients["inactive"][$percent > 1?10:intval($percent * 10)];
                }

                $statistics["block__".$row["lastblockid"]]["block__".$row["blockid"]] = array(
                    $row["lastblockoutput"],
                    $row["num"],
                    $percent,
                    $color,
                    $lineWidth,
                    $row["deleted"]=="1"?true:false,
                    $overlayNumber
                );

                $lastValue = $row["num"];
            }

            $sql = "SELECT
                        *
                    FROM vtiger_wfp_blocks
                    WHERE workflow_id = ".$workflowID." AND type IN ('delay')";
            $result = $adb->query($sql, true);

            /** Optional Overlay */
            while($row = $adb->fetchByAssoc($result)) {
                $sql = "SELECT COUNT(*) as num FROM vtiger_wf_queue WHERE block_id = ".$row["id"];
                $overlayResult = $adb->query($sql, true);
                $overlayNumber = $adb->query_result($overlayResult, 0, "num");

                $overlayInfo[$row["id"]] = $overlayNumber>0?$overlayNumber:"";
            }
            /** Optional Overlay */

            $response->setResult(array("success" => true, "startDate" => $startDate, "endDate" => $endDate, 'crmid' => $crmid, "displayRange" => DateTimeField::convertToUserFormat($startDate)." - ".DateTimeField::convertToUserFormat($endDate),"data" => $statistics, "overlay" => $overlayInfo));
        } catch(Exception $exp) {
            $response->setResult(array("success" => false, "error" => $exp->getMessage()));
        }

        $response->emit();
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