<?php

function gen_uuid2() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}

function createPay($entity)
{
	//exit;
	global $adb;
	global $url_api;
	global $url_username;
	global $url_password;
	global $$url_back_pay;
	
	$record=explode("x",$entity->getId());
	
	$url = $url_api;
	$username = $url_username;
	$password =  $url_password;


	$hdnGrandTotal=$entity->get("amount");
	
	
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
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Idempotence-Key: '.gen_uuid2()));
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
	
	$adb->pquery("UPDATE sp_payments SET pay_details='".$id."' WHERE payid = '".$record[1]."' LIMIT 1");
	$adb->pquery("UPDATE vtiger_crmentity SET description='".$url."' WHERE crmid = '".$record[1]."' LIMIT 1");

}
?>