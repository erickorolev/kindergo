﻿<?php
class Potentials_Convert_Action extends Vtiger_Action_Controller 
{
	function __construct() {
		parent::__construct();
		 $this->exposeMethod('CreateQ');
		 $this->exposeMethod('sendRequestToYandexAPI');
		 $this->exposeMethod('CreateQFromPOT');
		 $this->exposeMethod('createCalendar');		 
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
		return true;
	}
	
	function createCalendar(Vtiger_Request $request)
	{ 
		$monthArr=array(
	   'Январь',
	   'Февраль',
	   'Март',
	   'Апрель',
	   'Май',
	   'Июнь',
	   'Июль',
	   'Август',
	   'Сентябрь',
	   'Октябрь',
	   'Ноябрь',
	   'Декабрь',
		);

		$count=$request->get("count")-1;
		$year=$request->get("year");
		$month=$request->get("month");
		$value=$request->get("value");

		$monthCurrent=(int)$month;
		if ($monthCurrent==12)
		{
			$year=$year+1;
			$monthCurrent=1;
		}
		if ($month!="" && $year!="")
		{
			$calendar=$this->getCalendar($month,$year,$value);
			$blockCalendar="<div class='blockCalendar'><div><span year='".$year."' class='currentYear'>".$year."</span><input type='hidden' class='monthNum' value='".$month."' /> ".$monthArr[$month-1]."</div>".$calendar."</div>";
		}
		else
		{
			$blockCalendar="select month or year";
		}
		print $blockCalendar;
		exit;
	}
	
	function getCalendar($month, $year, $value)
	{
		$date=explode(",",$value);
		$calendar = '<table cellpadding="0" cellspacing="0" class="b-calendar__tb">';
		$headings = array('Пн','Вт','Ср','Чт','Пт','Сб','Вс');
		$calendar.= '<tr class="b-calendar__row">';
		for($head_day = 0; $head_day <= 6; $head_day++) {
			$calendar.= '<th class="b-calendar__head';
			if ($head_day != 0) {
				if (($head_day % 5 == 0) || ($head_day % 6 == 0)) {
					$calendar .= ' b-calendar__weekend';
				}
			}
			$calendar .= '">';
			$calendar.= '<div class="b-calendar__number">'.$headings[$head_day].'</div>';
			$calendar.= '</th>';
		}
		$calendar.= '</tr>';

		$running_day = date('w',mktime(0,0,0,$month,1,$year));
		$running_day = $running_day - 1;
		if ($running_day == -1) {
			$running_day = 6;
		}
		
		$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
		$day_counter = 0;
		$days_in_this_week = 1;
		$dates_array = array();
		
		$calendar.= '<tr class="b-calendar__row">';
		
		for ($x = 0; $x < $running_day; $x++) {
			$calendar.= '<td class="b-calendar__np"></td>';
			$days_in_this_week++;
		}

		for($list_day = 1; $list_day <= $days_in_month; $list_day++) {
			$calendar.= '<td class="b-calendar__day';

			if ($running_day != 0) {
				if (($running_day % 5 == 0) || ($running_day % 6 == 0)) {
					$calendar .= ' b-calendar__weekend';
				}
			}
			$calendar .= '">';

			$find=false;
			$nowday="";
			
			for ($ij=0;$ij<=count($date);$ij++)
			{
				$dateRow=explode("-",$date[$ij]);
				if (((int)$dateRow[2]==(int)$year)&&((int)$dateRow[1]==(int)$month)&&((int)$dateRow[0]==(int)$list_day))
				{
					$find=true;
				}
			}
			
			if (((int)date("Y")==(int)$year)&&((int)date("m")==(int)$month)&&((int)date("d")==(int)$list_day))
			{
				$nowday="nowday";
			}
			
			if ($find==true)
			{
				$calendar.= '<div class="b-calendar__number selectDay '.$nowday.'">'.$list_day.'</div>';
			}
			else
			{
				$calendar.= '<div class="b-calendar__number '.$nowday.'">'.$list_day.'</div>';
			}
			
			$calendar.= '</td>';
			
			if ($running_day == 6) {
				$calendar.= '</tr>';
				if (($day_counter + 1) != $days_in_month) {
					$calendar.= '<tr class="b-calendar__row">';
				}
				$running_day = -1;
				$days_in_this_week = 0;
			}

			$days_in_this_week++; 
			$running_day++; 
			$day_counter++;
		}

		if ($days_in_this_week < 8) {
			for($x = 1; $x <= (8 - $days_in_this_week); $x++) {
				$calendar.= '<td class="b-calendar__np"> </td>';
			}
		}
		$calendar.= '</tr>';
		$calendar.= '</table>';

		return $calendar;
	}

	function sendRequestToYandexAPI(Vtiger_Request $request) 
	{
	}
	
	function setInvService($quoteid,$serviceid,$no,$count,$description)
	{
		global $adb; 
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_service WHERE serviceid='".$serviceid."' LIMIt 100");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) {

			$unit_price = $adb->query_result($relatedlistproj,0,"unit_price");
			$itogo=$unit_price*$count;	
			$adb->pquery("insert into vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice,comment)  VALUES ('".$quoteid."','".$serviceid."','".$no."','".$count."','".$unit_price."','".$description."')");  
			return $itogo;
		}
		else
		{
			return 0;
		}	
	}
	
	function CreateQFromPOT(Vtiger_Request $request) 
	{
		global $adb; 
		$record=$request->get("leadid");
		$moduleName="Quotes";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$modelData = $recordModel->getData();
		$recordModel->set('mode', '');
		$recordModel->set("subject", "subject");
		$recordModel->set("potential_id", $record);
		$recordModel->set("contact_id", $contactid);
		$recordModel->set("quotestage", "Created");
		$recordModel->save();  
		$quoteid=$recordModel->getId();

		if ($record>0)
		{
			$priceMonth=5000; //выгрузить значения из базы-услуги
			$priceOneTrip=250;
					
			$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_timetable WHERE cf_potentials_id='".$record."' LIMIt 100");
			$res_cnt = $adb->num_rows($relatedlistproj);		
			if($res_cnt > 0) {
				
				$onepay=true;
				for($i=0;$i<$res_cnt;$i++) 
				{
					$no++;
					$timetableid = $adb->query_result($relatedlistproj,$i,"timetableid");		
					$recordModel = Vtiger_Record_Model::getInstanceById($timetableid, "Timetable");	
					$childrens=$recordModel->get("childrens");//количество
					$trips=$recordModel->get("trips");//количество поездок
					$name=$recordModel->get("name");
					$where_address=$recordModel->get("where_address");					
					$childrens_age=$recordModel->get("childrens_age");//возраст детей
					$duration=$recordModel->get("duration");//Длительность маршрута (мин)
					$scheduled_wait_where=$recordModel->get("scheduled_wait_where");//Запланированное ожидание в точке Куда (мин)  
					$scheduled_wait_from=$recordModel->get("scheduled_wait_from");//Запланированное ожидание в точке Откуда (мин) 				
					$shedAll=$scheduled_wait_where+$scheduled_wait_from;
					$date=$recordModel->get("date");	
					$insurances=$recordModel->get("insurances");	
					
					$description="Маршрут: ".$name." - ".$where_address.". Количество поездок: $trips";
					
					$count=1;
					if ($childrens==1)
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==2)
					{
						$itogoPrice3=$this->setInvService($quoteid,20,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==3)
					{
						$itogoPrice3=$this->setInvService($quoteid,21,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==4)
					{
						$itogoPrice3=$this->setInvService($quoteid,22,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					else
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}

					if ($shedAll>0)
					{
						$itogoPrice4=$this->setInvService($quoteid,23,$no,$trips*$shedAll,$description);
						$itogoPriceALL=$itogoPriceALL+$itogoPrice4;
					}

					$onepay=false;
					$countGO++;
				
					$no++;
					$this->setInvService($quoteid,24,$no,0,"");

					$no++;
					$datestrarr=explode(",",$date);

					foreach ($datestrarr as $dateRow)
					{
						$dateRowExp=explode("-",$dateRow);
						if (($dateRowExp[2]!="")&&($dateRowExp[1]!=""))
						{
							$mas[(int)$dateRowExp[2]][(int)$dateRowExp[1]]=$mas[(int)$dateRowExp[2]][(int)$dateRowExp[1]]+1;
						}
					}
					
					if ($insurances>0)
					{
						$itogoPrice1=$this->setInvService($quoteid,26,$no,$insurances,$description); //Страховка $insurances
						$no++;
					}	

					$adb->pquery("UPDATE vtiger_timetable SET cf_nrl_contacts580_id='".$contactid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					//$adb->pquery("UPDATE vtiger_timetable SET cf_potentials_id='".$potid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_quotes_id='".$quoteid."' WHERE timetableid = '".$timetableid."' LIMIT 1");	
				}
				
				$monthStr=array("Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");
				
				foreach ($mas as $a=>$k)
				{
					foreach ($k as $a2=>$k2)
					{
						$priceMonth=$k2*$priceOneTrip;
						if ($priceMonth>5000){ $priceMonth=5000; $rceLimitMonth="Да";
							$descriptionSO="Месяц: ".$monthStr[$a2-1]." ".$a.", Количество поедок: ".$k2.", Лимит поедок: ".$rceLimitMonth.""; 
						} else { $rceLimitMonth="Нет";  $descriptionSO="Месяц: ".$monthStr[$a2-1]." ".$a; }
						
						if ($rceLimitMonth=="Да")
						{
							$itogoPrice1=$this->setInvService($quoteid,65,$no,1,$descriptionSO); //сервисный сбор
						}
						else
						{
							$itogoPrice1=$this->setInvService($quoteid,25,$no,$k2,$descriptionSO); //сервисный сбор
						}
						$no++;
						$itogoPriceALL=$itogoPriceALL+$itogoPrice1;
					}
				}
			}
			$adb->pquery("UPDATE vtiger_quotes SET subtotal='".$itogoPriceALL."',total='".$itogoPriceALL."',pre_tax_total='".$itogoPriceALL."' WHERE quoteid = '".$quoteid."' LIMIT 1");
		}
		header("location: ?module=Quotes&view=Detail&record=".$quoteid);
		exit;
	}
	
	function CreateQ(Vtiger_Request $request) 
	{
		global $adb; 
		
		$priceMonth=5000;//сделать выгрузку из бд-услуги
		$priceOneTrip=250;
		
		$monthStr=array("Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");
		
		$leadid=$request->get("leadid");
		$record=$request->get("record");
		$moduleName="Quotes";
		
		$termconditionsql = $adb->pquery("SELECT tandc FROM  vtiger_inventory_tandc WHERE type='Quotes'  LIMIT 1");
		$terms_conditions = $adb->query_result($termconditionsql,0,"tandc"); 
		
		$potsql = $adb->pquery("SELECT contact_id FROM  vtiger_potential WHERE potentialid='".$record."'  LIMIT 1");
		$contactid = $adb->query_result($potsql,0,"contact_id"); 
		
		$contactsql = $adb->pquery("SELECT firstname,lastname FROM  vtiger_contactdetails WHERE contactid='".$contactid."' LIMIT 1");
		$firstname = $adb->query_result($contactsql,0,"firstname");
		$lastname = $adb->query_result($contactsql,0,"lastname");
		
		
		$moduleName="Quotes";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$modelData = $recordModel->getData();
		$recordModel->set('mode', '');
		$recordModel->set("subject", "subject");
		$recordModel->set("potential_id", $record);
		$recordModel->set("contact_id", $contactid);
		$recordModel->set("quotestage", "Created");
		$recordModel->save();  
		$quoteid=$recordModel->getId();	
		$leadid=$request->get("leadid");

		if ($record>0)
		{
			$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_timetable WHERE cf_leads_id='".$leadid."' LIMIt 100");
			$res_cnt = $adb->num_rows($relatedlistproj);		
			if($res_cnt > 0) {
				
				$onepay=true;
				for($i=0;$i<$res_cnt;$i++) 
				{
					$no++;
					$timetableid = $adb->query_result($relatedlistproj,$i,"timetableid");
					$recordModel = Vtiger_Record_Model::getInstanceById($timetableid, "Timetable");	
					$childrens=$recordModel->get("childrens");//количество
					$trips=$recordModel->get("trips");//количество поездок
					$name=$recordModel->get("name");
					$where_address=$recordModel->get("where_address");					
					$childrens_age=$recordModel->get("childrens_age");//возраст детей
					$duration=$recordModel->get("duration");//Длительность маршрута (мин)
					$scheduled_wait_where=$recordModel->get("scheduled_wait_where");//Запланированное ожидание в точке Куда (мин)  
					$scheduled_wait_from=$recordModel->get("scheduled_wait_from");//Запланированное ожидание в точке Откуда (мин) 				
					$shedAll=$scheduled_wait_where+$scheduled_wait_from;
					$date=$recordModel->get("date");	
					$insurances=$recordModel->get("insurances");	
					
					$description="Маршрут: ".$name." - ".$where_address.". Количество поездок: $trips";
					
					$count=1;
					if ($childrens==1)
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==2)
					{
						$itogoPrice3=$this->setInvService($quoteid,20,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==3)
					{
						$itogoPrice3=$this->setInvService($quoteid,21,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==4)
					{
						$itogoPrice3=$this->setInvService($quoteid,22,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					else
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}

					if ($shedAll>0)
					{
						$itogoPrice4=$this->setInvService($quoteid,23,$no,$trips*$shedAll,$description);
						$itogoPriceALL=$itogoPriceALL+$itogoPrice4;
					}

					$onepay=false;
					$countGO++;
				
					$no++;
					$this->setInvService($quoteid,24,$no,0,"");

					$no++;
					$datestrarr=explode(",",$date);

					foreach ($datestrarr as $dateRow)
					{
						$dateRowExp=explode("-",$dateRow);
						if (($dateRowExp[2]!="")&&($dateRowExp[1]!=""))
						{
							$mas[(int)$dateRowExp[2]][(int)$dateRowExp[1]]=$mas[(int)$dateRowExp[2]][(int)$dateRowExp[1]]+1;
						}
					}
					
					if ($insurances>0)
					{
						$itogoPrice1=$this->setInvService($quoteid,26,$no,$insurances,$description); //Страховка $insurances
						$no++;
					}	

					$adb->pquery("UPDATE vtiger_timetable SET cf_nrl_contacts580_id='".$contactid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_potentials_id='".$record."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_quotes_id='".$quoteid."' WHERE timetableid = '".$timetableid."' LIMIT 1");	
				}

				foreach ($mas as $a=>$k)
				{
					foreach ($k as $a2=>$k2)
					{
						$priceMonth=$k2*$priceOneTrip;
						if ($priceMonth>5000){ $priceMonth=5000; $rceLimitMonth="Да";
							$descriptionSO="Месяц: ".$monthStr[$a2-1]." ".$a.", Количество поедок: ".$k2.", Лимит поедок: ".$rceLimitMonth.""; 
						} else { $rceLimitMonth="Нет";  $descriptionSO="Месяц: ".$monthStr[$a2-1]." ".$a; }
						
						if ($rceLimitMonth=="Да")
						{
							$itogoPrice1=$this->setInvService($quoteid,65,$no,1,$descriptionSO); //сервисный сбор
						}
						else
						{
							$itogoPrice1=$this->setInvService($quoteid,25,$no,$k2,$descriptionSO); //сервисный сбор
						}
						$no++;
						$itogoPriceALL=$itogoPriceALL+$itogoPrice1;
					}
				}
			}
			$adb->pquery("UPDATE vtiger_quotes SET subtotal='".$itogoPriceALL."',total='".$itogoPriceALL."',pre_tax_total='".$itogoPriceALL."' WHERE quoteid = '".$quoteid."' LIMIT 1");
		}
		header("location: ?module=Quotes&view=Detail&record=".$quoteid);
		exit;
	}
}
?>