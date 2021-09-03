<?php
	global $adb;
	
	//print "SELECT * FROM  vtiger_trips.trips_status='Completed' AND vtiger_crmentity,vtiger_trips WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_trips.tripsid  LIMIT 100";
	
	$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_crmentity,vtiger_trips WHERE vtiger_trips.trips_status='Completed' AND  vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_trips.tripsid  LIMIT 100");
	$res_cnt = $adb->num_rows($relatedlistproj);		
	if($res_cnt > 0) 
	{
		for($i=0;$i<$res_cnt;$i++) 
		{
			$no++;
			$tripsid = $adb->query_result($relatedlistproj,$i,"tripsid");		
			$cf_nrl_contacts59_id = $adb->query_result($relatedlistproj,$i,"cf_nrl_contacts59_id");	
			$attendant_income = $adb->query_result($relatedlistproj,$i,"attendant_income");	
			$mas[$cf_nrl_contacts59_id][$tripsid]=$attendant_income;
		}
	}


foreach ($mas as $a=>$key)
{
	$summ=0;
	foreach ($key as $id=>$value)
	{
		$summ=$summ+$value;
	}
	$moduleName="SPPayments";
	$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
	$recordModel->set('mode', '');
	$recordModel->set("pay_date", date("Y-m-d"));
	$recordModel->set("pay_type", "Expense");
	$recordModel->set("payer", $a);
	$recordModel->set("amount", $summ);
	$recordModel->set("type_payment", "Зарплата");
	$recordModel->set("spstatus", "Scheduled");		
	$recordModel->set("description", "Периуд оплаты");	
	$recordModel->save();  
	$tripid=$recordModel->getId();
	
	
		
	foreach ($key as $id=>$value)
	{
		$adb->pquery("insert into vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUES ('".$id."','SPPayments','".$tripid."','Trips')");  
	}
}
exit;
?>