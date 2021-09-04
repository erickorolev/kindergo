<?php
class Potentials_Convert_Action extends Vtiger_Action_Controller 
{
	function __construct() {
		parent::__construct();
		 $this->exposeMethod('CreateQ');
		 $this->exposeMethod('sendRequestToYandexAPI');
		 $this->exposeMethod('CreateQFromPOT');
		 $this->exposeMethod('createCalendar');		
		 $this->exposeMethod('CreateInvoiceFromPOT');		
		 $this->exposeMethod('getCoord');	
		 $this->exposeMethod('CreateTripsFromPOT');	
		 $this->exposeMethod('checkTrip');	
		 $this->exposeMethod('Test');
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
	
	function checkTrip(Vtiger_Request $request)
	{
		global $adb;
		
		$find=true;
		$recordId=$_REQUEST[recordId];//$request->get("recordId");
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_crmentity,vtiger_timetable WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_timetable.timetableid AND vtiger_timetable.cf_potentials_id='".$recordId."' LIMIT 100");		
		$res_cnt = $adb->num_rows($relatedlistproj);		
			if($res_cnt > 0) 
			{
				for($i=0;$i<$res_cnt;$i++) 
				{
					//vtiger_timetable.cf_nrl_contacts759_id>'0' AND vtiger_timetable.parking_info!='' AND vtiger_timetable.parking_info!='' AND vtiger_crmentity.description!='' AND
					$cf_nrl_contacts759_id = $adb->query_result($relatedlistproj,$i,"cf_nrl_contacts759_id");
					$parking_info = $adb->query_result($relatedlistproj,$i,"parking_info");
					$description = $adb->query_result($relatedlistproj,$i,"description");
					if (($cf_nrl_contacts759_id>0)&&($parking_info!="")&&($description!=""))
					{
						
					}
					else
					{
						$find=false;
					}
				}
			}
		
		if ($find==false)
		{			
			print 2;
		}
		else
		{
			print 1;
		}
		exit;
		//recordId
	}
	
	function Test(Vtiger_Request $request)
	{
		global $adb;
		
	
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_crmentity,vtiger_trips WHERE vtiger_trips.trips_status='Completed' AND  vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_trips.tripsid  LIMIT 300");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) 
		{ 	print '111';
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
	}
	
	function getCoord(Vtiger_Request $request)
	{
		global $adb;
		$module=$request->get("mod");
		$recordid=$request->get("record");
		
		if ($module=="potential")
		{
			$relatedlistproj = $adb->pquery("SELECT name FROM  vtiger_crmentity,vtiger_timetable WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_timetable.timetableid AND vtiger_timetable.cf_potentials_id='".$recordid."' LIMIT 100");
			$res_cnt = $adb->num_rows($relatedlistproj);		
			if($res_cnt > 0) 
			{
				for($i=0;$i<$res_cnt;$i++) 
				{
					$name2 = $adb->query_result($relatedlistproj,$i,"name");
					$name.=$name2."##";
				}
			}
		}
		else
		{
			$relatedlistproj = $adb->pquery("SELECT name FROM  vtiger_crmentity,vtiger_trips WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_trips.tripsid AND vtiger_trips.tripsid='".$recordid."' LIMIT 1");
			$res_cnt = $adb->num_rows($relatedlistproj);		
			if($res_cnt > 0) 
			{
				for($i=0;$i<$res_cnt;$i++) 
				{
					$name2 = $adb->query_result($relatedlistproj,$i,"name");
					$name.=$name2."##";
				}
			}
		}
		
		$relatedlistproj = $adb->pquery("SELECT crmid,firstname,lastname,attendant_coordinates FROM  vtiger_crmentity,vtiger_contactdetails,vtiger_contactscf WHERE vtiger_contactdetails.contactid=vtiger_crmentity.crmid AND vtiger_contactdetails.contactid=vtiger_contactscf.contactid AND vtiger_contactdetails.attendant_coordinates!=''  AND vtiger_contactdetails.type='Attendant' AND vtiger_contactdetails.attendant_status='Active' LIMIt 200");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) {
			for($i=0;$i<$res_cnt;$i++) 
			{
				$crmid = $adb->query_result($relatedlistproj,$i,"crmid");
				$firstname = $adb->query_result($relatedlistproj,$i,"firstname");	
				$lastname = $adb->query_result($relatedlistproj,$i,"lastname");	
				$coordinat = $adb->query_result($relatedlistproj,$i,"attendant_coordinates");	
				$line.=$crmid."##".$firstname." ".$lastname."##".$coordinat."::";
			}
		}
		print $name."||".$line;
		exit;
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
	
	function getInvService($serviceid,$count=1)
	{
		global $adb; 
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_service WHERE serviceid='".$serviceid."' LIMIT 1");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) 
		{ 
			$unit_price = $adb->query_result($relatedlistproj,0,"unit_price");  
			$itogo=$unit_price*$count;	
			return $itogo; 
		}
		else
		{
			return 0;
		}	
	}
	
	function setInvService($quoteid,$serviceid,$no,$count,$description,$discount=0,$timetable_increase=0)
	{
		global $adb; 
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_service WHERE serviceid='".$serviceid."' LIMIt 100");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) 
		{
			$unit_price = $adb->query_result($relatedlistproj,0,"unit_price");
			$unit_price=number_format((float)$unit_price, 2, '.', ''); 
			
			if ($timetable_increase>0)
			{
				$unit_price=$unit_price+$timetable_increase*$unit_price/100;	
			}
			else
			{
				
			}
			
			$itogo=$unit_price*$count;	
			
			
			if ($discount>0){
				$itogo=$itogo-$itogo*$discount/100;
				$margin=$itogo;
			}
			$adb->pquery("insert into vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice,discount_percent,comment,margin)  VALUES ('".$quoteid."','".$serviceid."','".$no."','".$count."','".$unit_price."','".$discount."','".$description."','".$margin."')");  
			return $itogo;
		}
		else
		{
			return 0;
		}	
	}
			
	function gen_uuid2() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	function CreateTripsFromPOT(Vtiger_Request $request) 
	{
	
		global $adb;
		$recordid=$request->get("recordid");
		
		$recordModelPot = Vtiger_Record_Model::getInstanceById($recordid, "Potentials");	
		
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_crmentity,vtiger_timetable WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_timetable.timetableid AND vtiger_timetable.cf_potentials_id='".$recordid."' LIMIT 100");
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
				$time=$recordModel->get("time");
				$where_address=$recordModel->get("where_address");					
				$childrens_age=$recordModel->get("childrens_age");//возраст детей
				$duration=$recordModel->get("duration");//Длительность маршрута (мин)
				$scheduled_wait_where=$recordModel->get("scheduled_wait_where");//Запланированное ожидание в точке Куда (мин)  
				$scheduled_wait_from=$recordModel->get("scheduled_wait_from");//Запланированное ожидание в точке Откуда (мин) 
				$shedAll=$scheduled_wait_where+$scheduled_wait_from;
				$date=$recordModel->get("date");	
				$insurances=$recordModel->get("insurances");	
				$description="Маршрут: ".$name." - ".$where_address.". Количество поездок: $trips";
				$potentialid=$recordModel->get("cf_potentials_id");
				
				
				$cf_timetable_id=$recordModel->get("cf_timetable_id");//расписание
				$trips_contact=$recordModelPot->get("contact_id");//клиент из сделки
				$parking_info=$recordModel->get("parking_info");//Парковка
				$description=$recordModel->get("description");//Описание
				$cf_nrl_contacts759_id=$recordModel->get("cf_nrl_contacts759_id");//Ребенок 1
				$cf_nrl_contacts296_id=$recordModel->get("cf_nrl_contacts296_id");//Ребенок 2
				$cf_nrl_contacts85_id =$recordModel->get("cf_nrl_contacts85_id");//Ребенок 3
				$cf_nrl_contacts705_id =$recordModel->get("cf_nrl_contacts705_id");//Ребенок 1
				$distance =$recordModel->get("distance");
				 
				
				$insurances=$recordModel->get("insurances");//
				
				
				$listCalendar=explode(",",$date);
			
				foreach ($listCalendar as $dateRow)
				{
					if ($dateRow!="")
					{
						$moduleName="Trips";
						$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
						$recordModel->set('mode', '');
						$recordModel->set("name", $name);
						
						$recordModel->set("cf_timetable_id", $cf_timetable_id);
						$recordModel->set("trips_contact", $trips_contact);
						$recordModel->set("parking_info", $parking_info);
						$recordModel->set("description", $description);
						$recordModel->set("cf_nrl_contacts351_id", $cf_nrl_contacts759_id);
						$recordModel->set("cf_nrl_contacts774_id", $cf_nrl_contacts296_id);
						$recordModel->set("cf_nrl_contacts94_id", $cf_nrl_contacts85_id);
						$recordModel->set("cf_nrl_contacts616_id", $cf_nrl_contacts705_id);
		
						
						$recordModel->set("where_address", $where_address);
						$recordModel->set("time", $time);
						$recordModel->set("date", date("Y-m-d",strtotime($dateRow)));
						$recordModel->set("duration", $duration);	
						$recordModel->set("distance", $distance);	
						$recordModel->set("childrens", $childrens);			
						$recordModel->set("cf_timetable_id", $timetableid);	
						$recordModel->set("trips_status", "Appointed");			
						$recordModel->set("scheduled_wait_from", $scheduled_wait_from);
						$recordModel->set("not_scheduled_wait_from", $not_scheduled_wait_from);	
						$recordModel->set("scheduled_wait_where", $scheduled_wait_where);
						$recordModel->set("not_scheduled_wait_where", $not_scheduled_wait_where);
						$recordModel->set("cf_1220", $cf_1220);
						$recordModel->set("cf_1224", $potentialid);	
						$recordModel->set("attendant_income", 0);
						//$recordModel->set("trips_contact", $request->get("contact_id"));
						$recordModel->set("trips_status", "В ожидании"); 
						
						
						$recordModel->save();  
						$tripid=$recordModel->getId();
						$adb->pquery("insert into vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUES ('".$recordid."','Potentials','".$tripid."','Timetable')"); 
					}
				}
			}
		}
		//?module=Potentials&relatedModule=Trips&view=Detail&record=286&mode=showRelatedList&relationId=217&tab_label=Поездки&app=MARKETING
		header("location: ?module=Potentials&relatedModule=Trips&view=Detail&record=".$recordid."&mode=showRelatedList&tab_label=Поездки&app=MARKETING");
		exit;
	}
	
	function CreateInvoiceFromPOT(Vtiger_Request $request) 
	{
		global $adb;
		global $url_api;
		global $url_username;
		global $url_password;
		global $$url_back_pay;
		
		$record=$request->get("quoteid");

		$recordModel = Vtiger_Record_Model::getInstanceById($record, "Quotes");	
		$potential_id=$recordModel->get("potential_id");
		$contactid=$recordModel->get("contact_id");
		$hdnGrandTotal=$recordModel->get("hdnGrandTotal");
		
		$subject=$recordModel->get("subject");
		$adb->pquery("UPDATE vtiger_quotes SET quotestage='Accepted' WHERE quotesid = '".$record."' LIMIT 1");
		
		/* 
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_timetable WHERE cf_potentials_id='".$potential_id."' LIMIt 100");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) 
		{
			for($i=0;$i<$res_cnt;$i++) 
			{
				$timetableid = $adb->query_result($relatedlistproj,$i,"timetableid");
				$adb->pquery("UPDATE vtiger_crmentity SET deleted='1' WHERE crmid = '".$timetableid."' LIMIT 1");
			}
		} 
		/**/

		$url = $url_api;
		$username = $url_username;
		$password =  $url_password;
		
		$ch = curl_init($url);

		$data = array(
			'amount' => array(
				'value' => $hdnGrandTotal,
				'currency' => 'RUB',
			),
			'capture' => true,
			'confirmation' => array(
				'type' => 'redirect',
				'return_url' => 'http://ya.ru',//$url_back_pay,
			),
			'description' => 'Заказ drive №2',
			'metadata' => array(
				'order_id' => 1,
			)
		);
			
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Idempotence-Key: '.$this->gen_uuid2()));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 

		//Execute the cURL request.
		$response = curl_exec($ch);
		
		//Check for errors.
		if(curl_errno($ch)){
			//If an error occured, throw an Exception.
			throw new Exception(curl_error($ch));
		}
		
		$response2=json_decode($response);
		$url=$response2->confirmation->confirmation_url;
		$id=$response2->id;

		
		$moduleName="Invoice";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set('mode', '');
		$recordModel->set("subject", $subject);
		$recordModel->set("potential_id", $potential_id);
		$recordModel->set("contact_id", $contactid);
		$recordModel->set("invoicestatus", "Sent");
		$recordModel->set("hdnGrandTotal", $hdnGrandTotal);
		$recordModel->set("hdnSubTotal", $hdnGrandTotal);
		$recordModel->set("online_payment", $url);

		$recordModel->save();  
		$invoice=$recordModel->getId();
		
		$adb->pquery("UPDATE vtiger_invoice SET subtotal='".$hdnGrandTotal."',total='".$hdnGrandTotal."' WHERE invoiceid = '".$invoice."' LIMIT 1");			
		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_inventoryproductrel WHERE id='".$record."' LIMIt 100");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) {
			$onepay=true;
			for($i=0;$i<$res_cnt;$i++) 
			{
				$no++;
				$productid = $adb->query_result($relatedlistproj,$i,"productid");	
				$sequence_no = $adb->query_result($relatedlistproj,$i,"sequence_no");
				$quantity = $adb->query_result($relatedlistproj,$i,"quantity");
				$listprice = $adb->query_result($relatedlistproj,$i,"listprice");
				$comment = $adb->query_result($relatedlistproj,$i,"comment");
				$margin = $adb->query_result($relatedlistproj,$i,"margin");
				$adb->pquery("insert into vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice,comment,margin) VALUES ('".$invoice."','".$productid."','".$sequence_no."','".$quantity."','".$listprice."','".$comment."','".$margin."')");  
			}
		}

		$moduleName="SPPayments";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set('mode', '');
		$recordModel->set("pay_date", date("Y-m-d"));
		$recordModel->set("pay_type", "Receipt");
		$recordModel->set("payer", $contactid);
		$recordModel->set("related_to", $invoice);
		$recordModel->set("type_payment", "Online payment");
		$recordModel->set("amount", $hdnGrandTotal);
		$recordModel->set("spstatus", "Scheduled");
		$recordModel->set("description", $url);
		$recordModel->set("pay_details", $id);	
		$recordModel->save();  
		$pay=$recordModel->getId();

		$adb->pquery("UPDATE vtiger_potential SET sales_stage='Proposal or Price Quote' WHERE potentialid = '".$potential_id."' LIMIT 1");
		header("location:?module=Invoice&view=Detail&record=".$invoice."");
		exit;	
	}
	
	function CreateQFromPOT(Vtiger_Request $request) 
	{
		global $adb; 
	
		$record=$request->get("leadid");			
		$recordModelPot = Vtiger_Record_Model::getInstanceById($record, "Potentials");	
		$contactid=$recordModelPot->get("contact_id");
		$recordModelContact = Vtiger_Record_Model::getInstanceById($contactid, "Contacts");	
		$contactFIO=$recordModelContact->get("firstname")." ".$recordModelContact->get("lastname");
		
		$moduleName="Quotes";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$modelData = $recordModel->getData();
		$recordModel->set('mode', '');
		$recordModel->set("subject", $contactFIO);
		$recordModel->set("potential_id", $record);
		$recordModel->set("contact_id", $contactid);
		$recordModel->set("quotestage", "Created");
		$recordModel->save();  
		$quoteid=$recordModel->getId();

		if ($record>0)
		{
			$priceMonth=$this->getInvService(65);//5000; //выгрузить значения из базы-услуги
			$priceOneTrip=$this->getInvService(25);;//250;
					
			$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_crmentity,vtiger_timetable WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_timetable.timetableid AND vtiger_timetable.cf_potentials_id='".$record."' LIMIt 100");
			$res_cnt = $adb->num_rows($relatedlistproj);		
			if($res_cnt > 0) 
			{
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
					$timetable_discount=$recordModel->get("timetable_discount");
					$timetable_increase=$recordModel->get("timetable_increase");
					
					$description="Маршрут: ".$name." - ".$where_address.". Количество поездок: $trips";
					$tripsAll=$tripsAll+$trips;
					
					$count=1;
					if ($childrens==1)
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==2)
					{
						$itogoPrice3=$this->setInvService($quoteid,20,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==3)
					{
						$itogoPrice3=$this->setInvService($quoteid,21,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==4)
					{
						$itogoPrice3=$this->setInvService($quoteid,22,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					else
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}

					if ($shedAll>0)
					{
						$itogoPrice4=$this->setInvService($quoteid,23,$no,$trips*$shedAll,$description,0,0);
						$itogoPriceALL=$itogoPriceALL+$itogoPrice4;
					}

					$onepay=false;
					$countGO++;
				
					$no++;
					$this->setInvService($quoteid,24,$no,0,"",0,0);

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
						$itogoPrice1=$this->setInvService($quoteid,26,$no,$insurances,$description,0,0); //Страховка $insurances
						$itogoPriceALL=$itogoPriceALL+$itogoPrice1;
						$no++;
					}	

					$adb->pquery("UPDATE vtiger_timetable SET cf_nrl_contacts580_id='".$contactid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
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
							$itogoPrice1=$this->setInvService($quoteid,65,$no,1,$descriptionSO,0,0); //сервисный сбор
						}
						else
						{
							$itogoPrice1=$this->setInvService($quoteid,25,$no,$k2,$descriptionSO,0,0); //сервисный сбор
						}
						$no++;
						$itogoPriceALL=$itogoPriceALL+$itogoPrice1;
					}
				}
			}
		}
		
		$adb->pquery("UPDATE vtiger_potential SET sales_stage='Value Proposition' WHERE potentialid = '".$record."' LIMIT 1");
		$adb->pquery("UPDATE vtiger_quotes SET subject='".$contactFIO." - ".$tripsAll."', subtotal='".$itogoPriceALL."',total='".$itogoPriceALL."',pre_tax_total='".$itogoPriceALL."' WHERE quoteid = '".$quoteid."' LIMIT 1");
		
		header("location: ?module=Quotes&view=Detail&record=".$quoteid);
		exit;
	}
	
	function CreateQ(Vtiger_Request $request) 
	{
		global $adb; 

		$priceMonth=$this->getInvService(65);//5000; //выгрузить значения из базы-услуги
		$priceOneTrip=$this->getInvService(25);;//250;

		$monthStr=array("Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");
		
		$leadid=$request->get("leadid");
		$record=$request->get("record");

		$recordModelPot = Vtiger_Record_Model::getInstanceById($record, "Potentials");	
		$contactid=$recordModelPot->get("contact_id");
		
		$recordModelContact = Vtiger_Record_Model::getInstanceById($contactid, "Contacts");	
		$contactFIO=$recordModelContact->get("firstname")." ".$recordModelContact->get("lastname");
		
		
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
			$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_crmentity,vtiger_timetable WHERE vtiger_crmentity.deleted!='1' AND vtiger_crmentity.crmid=vtiger_timetable.timetableid AND vtiger_timetable.cf_leads_id='".$leadid."' LIMIt 100");
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
					$timetable_discount=$recordModel->get("timetable_discount");
					$description="Маршрут: ".$name." - ".$where_address.". Количество поездок: $trips";
					$timetable_increase=$recordModel->get("timetable_increase");
					
					$tripsAll=$tripsAll+$trips;
					
					$count=1;
					if ($childrens==1)
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==2)
					{
						$itogoPrice3=$this->setInvService($quoteid,20,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==3)
					{
						$itogoPrice3=$this->setInvService($quoteid,21,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					elseif ($childrens==4)
					{
						$itogoPrice3=$this->setInvService($quoteid,22,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}
					else
					{
						$itogoPrice3=$this->setInvService($quoteid,14,$no,$trips*$duration,$description,$timetable_discount,$timetable_increase);  //сопровождение одного ребенка
						$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
					}

					if ($shedAll>0)
					{
						$itogoPrice4=$this->setInvService($quoteid,23,$no,$trips*$shedAll,$description,0,0);
						$itogoPriceALL=$itogoPriceALL+$itogoPrice4;
					}

					$onepay=false;
					$countGO++;
				
					$no++;
					$this->setInvService($quoteid,24,$no,0,"",0,0);

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
						$itogoPrice1=$this->setInvService($quoteid,26,$no,$insurances,$description,0,0); //Страховка $insurances
						$no++;
					}	
		
					$adb->pquery("UPDATE vtiger_timetable SET cf_nrl_contacts580_id='".$contactid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_potentials_id='".$record."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_quotes_id='".$quoteid."' WHERE timetableid = '".$timetableid."' LIMIT 1");	
					
					$adb->pquery("insert into vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUES ('".$record."','Potentials','".$timetableid."','Timetable')"); 
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
							$itogoPrice1=$this->setInvService($quoteid,65,$no,1,$descriptionSO,0,0); //сервисный сбор
						}
						else
						{
							$itogoPrice1=$this->setInvService($quoteid,25,$no,$k2,$descriptionSO,0,0); //сервисный сбор
						}
						$no++;
						$itogoPriceALL=$itogoPriceALL+$itogoPrice1;
					}
				}
			}
			$adb->pquery("UPDATE vtiger_quotes SET  subject='".$contactFIO." - ".$tripsAll."', subtotal='".$itogoPriceALL."',total='".$itogoPriceALL."',pre_tax_total='".$itogoPriceALL."' WHERE quoteid = '".$quoteid."' LIMIT 1");
		}
		
		$adb->pquery("UPDATE vtiger_potential SET potentialname='".$contactFIO." - ".$tripsAll."', sales_stage='Value Proposition' WHERE potentialid = '".$record."' LIMIT 1");
		
		header("location: ?module=Quotes&view=Detail&record=".$quoteid); 
		exit;
	}
}
?>