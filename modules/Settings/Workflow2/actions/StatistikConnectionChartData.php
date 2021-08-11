<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_StatistikConnectionChartData_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();

        try {
            $source = $request->get("source");
            $destination = $request->get("destination");

            $startDate = date("Y-m-d", strtotime($request->get("startDate")));
            $endDate = date("Y-m-d", strtotime($request->get("endDate")));

            $module_name = $request->get("module_name");

            $sourceParts = explode("__", $source);
            $destParts = explode("__", $destination);

            $instance = CRMEntity::getInstance($module_name);

            $sql = "SELECT COUNT(*) as num, DATE(timestamp) as `date`
                    FROM vtiger_wf_log
                WHERE lastBlockId = ".$sourceParts[1]." AND lastBlockOutput = '".$sourceParts[2]."' AND blockID = '".$destParts[1]."' AND timestamp >= '".$startDate." 00:00:00' AND timestamp <= '".$endDate." 23:59:00' GROUP BY DATE(timestamp)";
            $result = $adb->query($sql);

            $data = array();
            $min = 9999;
            $max = 0;

            $days = createDateRangeArray($startDate, $endDate);

            while($row = $adb->fetch_array($result)) {
                $data[$row["date"]] = $row["num"];

                if($row["num"] < $min) {
                    $min = $row["num"];
                }
                if($row["num"] > $max) {
                    $max = $row["num"];
                }
            }

            $result = array();
            foreach($days as $day) {
                if(isset($data[$day])) {
                    $result[] = array(date("d-M-Y", strtotime($day)), $data[$day]);
                } else {
                    $result[] = array(date("d-M-Y", strtotime($day)), 0);
                }
            }

            if(count($days) > 10) {
                $interval = 7;
            } else {
                $interval = 1;
            }
            if($max > 200) {
                $Xinterval = 50;
            } elseif($max > 100) {
                $Xinterval = 25;
            } elseif($max > 50) {
                $Xinterval = 10;
            } elseif($max >= 6) {
                $Xinterval = 2;
            } else {
                $Xinterval = 1;
            }

            $data = array("Yinterval" => $interval, "Xinterval" => $Xinterval, "min" => intval($min), "max" => intval($max), "data" => $result);

            $response->setResult(array("success" => true, "data" => $data));
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
function createDateRangeArray($strDateFrom,$strDateTo)
{
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script

    $aryRange=array();

    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($iDateTo>=$iDateFrom)
    {
        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange,date('Y-m-d',$iDateFrom));
        }
    }
    return $aryRange;
}
