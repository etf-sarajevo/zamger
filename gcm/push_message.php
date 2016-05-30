<?php
 
if (!function_exists('json_encode')) {
     function json_encode($data) {
         switch ($type = gettype($data)) {
             case 'NULL':
                 return 'null';
             case 'boolean':
                 return ($data ? 'true' : 'false');
             case 'integer':
             case 'double':
             case 'float':
                 return $data;
             case 'string':
                 return '"' . addslashes($data) . '"';
             case 'object':
                 $data = get_object_vars($data);
             case 'array':
                 $output_index_count = 0;
                 $output_indexed = array();
                 $output_associative = array();
                 foreach ($data as $key => $value) {
                     $output_indexed[] = json_encode($value);
                     $output_associative[] = json_encode($key) . ':' . json_encode($value);
                     if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                         $output_index_count = NULL;
                     }
                 }
                 if ($output_index_count !== NULL) {
                     return '[' . implode(',', $output_indexed) . ']';
                 } else {
                     return '{' . implode(',', $output_associative) . '}';
                 }
             default:
                 return ''; // Not supported
         }
     }
 }


function gcm_send($deviceRegistrationId, $msgType, $msgTitle) {

	$url = 'https://android.googleapis.com/gcm/send';
	$serverApiKey = "AIzaSyCJxiMMjMhekJLpQORuqOyhRKZ7Iz7ACr8";
	
	$headers = array(
	'Content-Type:application/json;charset=UTF-8',
	'Authorization:key=' . $serverApiKey
	);

	$data = array(
		'registration_ids' => array($deviceRegistrationId)
		,'data' => array(
		'type' => $msgType
		,'title' => $msgTitle
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

function push_message($auth_ids, $type, $title) {
	$ids = join(',',$auth_ids); 
	
	$q0 = myquery("SELECT reg_key FROM notifikacije_gcm WHERE auth IN ($ids)");

	for($i=0;$i<mysql_num_rows($q0);$i++) {
		$reg_key = mysql_result($q0,$i,0);
		gcm_send($reg_key, $type, $title);
		
	}
}

?>
