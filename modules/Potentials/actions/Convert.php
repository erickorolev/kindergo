<?php
/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/**/

class Potentials_Convert_Action extends Vtiger_Action_Controller 
{
	function __construct() {
		parent::__construct();
		 $this->exposeMethod('CreateQ');
		 $this->exposeMethod('sendRequestToYandexAPI');
		 $this->exposeMethod('CreateQFromPOT');
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

	function sendRequestToYandexAPI(Vtiger_Request $request) 
	{
		// https://geocode-maps.yandex.ru/1.x/?apikey=57e07453-d5ea-4c0c-8414-af55ec871863&lang=ru_RU&geocode=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0,%D0%A2%D0%B2%D0%B5%D1%80%D1%81%D0%BA%D0%B0%D1%8F
		
		print '1';
		exit;
	}
	
	function setInvService($quoteid,$serviceid,$no,$count,$description)
	{
		global $adb; 

		$relatedlistproj = $adb->pquery("SELECT * FROM  vtiger_service WHERE serviceid='".$serviceid."' LIMIt 100");
		$res_cnt = $adb->num_rows($relatedlistproj);		
		if($res_cnt > 0) {

			$unit_price = $adb->query_result($relatedlistproj,0,"unit_price");
			$itogo=$unit_price*$count;	
				
			//print "insert into vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice)  VALUES ('".$quoteid."','".$serviceid."','".$no."','".$count."','".$unit_price."')<Br>";
		
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
		
		global $adb;
		
	//	print "SELECT * FROM  vtiger_timetable WHERE cf_potentials_id='".$record."' LIMIt 100";
//		exit;
		if ($record>0)
		{
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
					
					$adb->pquery("UPDATE vtiger_timetable SET cf_nrl_contacts580_id='".$contactid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_potentials_id='".$potid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					$adb->pquery("UPDATE vtiger_timetable SET cf_quotes_id='".$quoteid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
					
					$onepay=false;
					$countGO++;
				}
				$no++;
				
				if ($childrens>0)
				{
					$itogoPrice2=$this->setInvService($quoteid,26,$no,$childrens,""); //страховка ребенка
					$itogoPriceALL=$itogoPriceALL+$itogoPrice2;
				}
				else
				{
					$itogoPrice2=$this->setInvService($quoteid,26,$no,$trips*$countGO,"");
					$itogoPriceALL=$itogoPriceALL+$itogoPrice2;
				}
			
				$no++;
				$this->setInvService($quoteid,24,$no,0,"");
				
				$no++;
				$itogoPrice1=$this->setInvService($quoteid,25,$no,$trips*$count,""); //сервисный сбор
				$itogoPriceALL=$itogoPriceALL+$itogoPrice1;

			}
			$adb->pquery("UPDATE vtiger_quotes SET subtotal='".$itogoPriceALL."',total='".$itogoPriceALL."',pre_tax_total='".$itogoPriceALL."' WHERE quoteid = '".$quoteid."' LIMIT 1");
		}
		
		header("location: ?module=Quotes&view=Detail&record=".$quoteid);
		
		exit;
	}
	
	function CreateQ(Vtiger_Request $request) 
	{
		global $adb; 
		
		/*
		1.Создать Контакт
		2.Создаьб POT, скопировать crmel
		Получить ИД 
		*/
		
		/*
		exit;
	
		$moduleName="Contacts";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$modelData = $recordModel->getData();
		$recordModel->set('mode', '');
		$recordModel->set("firstname", "firstname");
		$recordModel->set("lastname", "lastname");
		$recordModel->set("type", "type");
		$recordModel->save();  
		$contactid=$recordModel->getId();

		$moduleName="Potentials";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$modelData = $recordModel->getData();
		$recordModel->set('mode', '');
		$recordModel->set("potentialname", "potentialname");
		$recordModel->set("contact_id", $contactid);
		$recordModel->save();  
		$potid=$recordModel->getId();
		
		$moduleName="Quotes";
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$modelData = $recordModel->getData();
		$recordModel->set('mode', '');
		$recordModel->set("subject", "subject");
		$recordModel->set("potential_id", $potid);
		$recordModel->set("contact_id", $contactid);
		$recordModel->set("quotestage", "Created");
		/**/
		
		
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
		$recordModel->set("potential_id", $potid);
		$recordModel->set("contact_id", $contactid);
		$recordModel->set("quotestage", "Created");
		$recordModel->save();  
		$quoteid=$recordModel->getId();
		
		print "contactid===>".$contactid."<====<br>";
		print "pot===>".$potid."<====<br>";
		print "quote===>".$quoteid."<====<br>";

		$leadid=$request->get("leadid");
		global $adb;
		
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
				
				print $itogoPriceALL."<Br>";
				
				$adb->pquery("UPDATE vtiger_timetable SET cf_nrl_contacts580_id='".$contactid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
				$adb->pquery("UPDATE vtiger_timetable SET cf_potentials_id='".$potid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
				$adb->pquery("UPDATE vtiger_timetable SET cf_quotes_id='".$quoteid."' WHERE timetableid = '".$timetableid."' LIMIT 1");
				
				$onepay=false;
				$countGO++;
			}
			$no++;
			
			if ($childrens>0)
			{
				$itogoPrice2=$this->setInvService($quoteid,26,$no,$childrens,""); //страховка ребенка
				$itogoPriceALL=$itogoPriceALL+$itogoPrice2;
			}
			else
			{
				$itogoPrice2=$this->setInvService($quoteid,26,$no,$trips*$countGO,"");
				$itogoPriceALL=$itogoPriceALL+$itogoPrice2;
			}
		
			$no++;
			$this->setInvService($quoteid,24,$no,0,"");
			
			$no++;
			$itogoPrice1=$this->setInvService($quoteid,25,$no,$trips*$count,""); //сервисный сбор
			$itogoPriceALL=$itogoPriceALL+$itogoPrice1;

		}
		$adb->pquery("UPDATE vtiger_quotes SET subtotal='".$itogoPriceALL."',total='".$itogoPriceALL."',pre_tax_total='".$itogoPriceALL."' WHERE quoteid = '".$quoteid."' LIMIT 1");
		
		header("location: ?module=Quotes&view=Detail&record=".$quoteid);
		exit;
	}
}
?>