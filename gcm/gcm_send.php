<?php

function gcm_send($deviceRegistrationId, $msgType, $msgTitle, $messageText) {

    $url = 'https://android.googleapis.com/gcm/send';
	$serverApiKey = "AIzaSyCJxiMMjMhekJLpQORuqOyhRKZ7Iz7ACr8";
	
	$headers = array(
	'Content-Type:application/json; charset=UTF-8',
	'Authorization:key=' . $serverApiKey
	);

	$data = array(
	'registration_ids' => array($deviceRegistrationId)
	,'data' => array(
	'type' => utf8_encode($msgType)
	,'title' => utf8_encode($msgTitle)
	,'msg' => utf8_encode($messageText)
	)
 );
 
 
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 if ($headers)
 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 curl_setopt($ch, CURLOPT_POST, true);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

 $response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

?>