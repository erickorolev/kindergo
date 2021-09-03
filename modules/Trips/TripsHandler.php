<?php
function calculateTripPay($entity)
{
	global $adb;
	require_once("modules/Potentials/actions/Convert.php"); 
	$Cl= new Potentials_Convert_Action;
	
	$priceMonth=$Cl->getInvService(65);//5000; //выгрузить значения из базы-услуги
	$priceOneTrip=$Cl->getInvService(25);;//250;
	
	$record=explode("x",$entity->getId());

		$onepay=true;
			$no++;	
			$childrens=$entity->get("childrens");//количество
			$trips=$entity->get("trips");//количество поездок
			$name=$entity->get("name");
			$where_address=$entity->get("where_address");					
			$childrens_age=$entity->get("childrens_age");//возраст детей
			$duration=$entity->get("duration");//Длительность маршрута (мин)
			$scheduled_wait_where=$entity->get("scheduled_wait_where");//Запланированное ожидание в точке Куда (мин)  
			$scheduled_wait_from=$entity->get("scheduled_wait_from");//Запланированное ожидание в точке Откуда (мин) 		

			$not_scheduled_wait_where=$entity->get("not_scheduled_wait_where");//НЕ Запланированное ожидание в точке Куда (мин)  
			$not_scheduled_wait_from=$entity->get("not_scheduled_wait_from");//НЕ  Запланированное ожидание в точке Откуда (мин) 	

			
			$shedAll=$scheduled_wait_where+$scheduled_wait_from;
			$noTshedAll=$not_scheduled_wait_where+$not_scheduled_wait_from;
			
			$date=$entity->get("date");	
			$insurances=$entity->get("insurances");	
			$description="Маршрут: ".$name." - ".$where_address.". Количество поездок: $trips";
			$tripsAll=$tripsAll+$trips;
			$parking_cost=$entity->get("parking_cost");//стоимость парковки

			$count=1;
			if ($childrens==1)
			{
				$itogoPrice3=$Cl->getInvService(14,$duration);  //сопровождение 1 ребенка
				$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
			}
			elseif ($childrens==2)
			{
				$itogoPrice3=$Cl->getInvService(20,$duration);  //сопровождение 2 ребенка
				$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
			}
			elseif ($childrens==3)
			{
				$itogoPrice3=$Cl->getInvService(21,$duration);  //сопровождение 3 ребенка
				$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
			}
			elseif ($childrens==4)
			{
				$itogoPrice3=$Cl->getInvService(22,$duration);  //сопровождение 4 ребенка
				$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
			}
			else
			{
				$itogoPrice3=$Cl->getInvService(14,$duration);  //сопровождение 1 ребенка
				$itogoPriceALL=$itogoPriceALL+$itogoPrice3;
			}
			
			
			$shedPay=$Cl->getInvService(23,$shedAll); 
			
			$notShedPay=$Cl->getInvService(24,$noTshedAll); 
			
			$itogoPay=$itogoPriceALL-$itogoPriceALL*0.35+$shedPay*0.56+$notShedPay*0.42+$parking_cost;		
			$adb->pquery("UPDATE vtiger_trips SET attendant_income='".$itogoPay."' WHERE tripsid = '".$record[1]."' LIMIT 1");
}
?>