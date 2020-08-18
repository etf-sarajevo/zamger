<?

// LIB/WS - funkcije za pristup web servisima



function xml_request($url, $parameters, $method = "GET", $parse = true) 
{
	global $conf_verbosity;
	
	$disableSslCheck = array(
		'ssl' => array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
	);  

	$allowed_http_codes = array ("200"); // Only 200 is allowed

	$query = http_build_query($parameters);
	
	if ($method == "GET") 
		$http_result = @file_get_contents("$url?$query", false, stream_context_create($disableSslCheck));
	else {
		$params = array('http' => array(
			'method' => 'POST',
			'content' => $query,
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
				"Content-Length: " . strlen ( $query ) . "\r\n"
			),
			'ssl' => array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);
		$ctx = stream_context_create($params);
		$fp = fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			echo "HTTP request failed for $url (POST)\n";
			return FALSE;
		}
		$http_result = stream_get_contents($fp);
		fclose($fp);
	}
	if ($http_result===FALSE) {
		print "HTTP request failed for $url?$query ($method)\n";
		return FALSE;
	}
	$http_code = explode(" ", $http_response_header[0]);
	$http_code = $http_code[1];
	if ( !in_array($http_code, $allowed_http_codes) ) {
		print "HTTP request returned code $http_code for $url?$query ($method)\n";
		return FALSE;
	}
		
	/*$json_result = json_decode($http_result, true); // Retrieve json as associative array
	if ($json_result===NULL) {
		print "Failed to decode result as JSON\n$http_result\n";
		// Why does this happen!?
		if ($conf_verbosity>0) { print_r($http_result); print_r($parameters); }
		return FALSE;
	} 

	else if (array_key_exists("server_message", $json_result)) {
		print "Message from server: " . $json_result["server_message"]."\n";
	}*/
	if (strlen($http_result) < 40) return FALSE; // Prazno
	
	if (!$parse) return $http_result;

	$xmlparser = xml_parser_create("UTF-8");
	$xml_result = array();
	xml_parser_set_option($xmlparser, XML_OPTION_SKIP_WHITE, 1);
	$success = xml_parse_into_struct($xmlparser, $http_result, $xml_result);
	xml_parser_free($xmlparser);

	if ($success === 0) return FALSE;
	return $xml_result;
}


function bhfloat($str) {
	$str = str_replace(".", "", $str);
	$str = str_replace(",", ".", $str);
	return floatval($str);
}


function parsiraj_kartice($xml_data) {
	$result = array();
	if ($xml_data === FALSE) return FALSE;

	$u_kartici = false;
	$tekuca_kartica = array();
	foreach ($xml_data as $node) {
		if ($node['tag'] == "KARTICA") {
			if ($node['type'] == "open") { 
				if ($u_kartici) $result[] = $tekuca_kartica;
				$u_kartici=true;
				$tekuca_kartica = array();
			}
			if ($node['type'] == "closed") {
				$u_kartici=false;
				$result[] = $tekuca_kartica;
			}
			continue;
		}
		if (!$u_kartici) continue;
		if ($node['tag'] == "DATUM") $tekuca_kartica['datum'] = $node['value'];
		if ($node['tag'] == "VRSTAZADUZENJA") $tekuca_kartica['vrsta_zaduzenja'] = $node['value'];
		if ($node['tag'] == "ZADUZENJE") $tekuca_kartica['zaduzenje'] = bhfloat($node['value']);
		if ($node['tag'] == "RAZDUZENJE") $tekuca_kartica['razduzenje'] = bhfloat($node['value']);
	}
	if ($u_kartici) $result[] = $tekuca_kartica;

	return $result;
}

function json_request($url, $parameters, $method = "GET", $encoding = "url", $debug = false)
{
	global $conf_verbosity;
	
	$disableSslCheck = array(
		'ssl' => array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
	);
	
	$allowed_http_codes = array ("200"); // Only 200 is allowed
	
	$query = http_build_query($parameters);
	
	if ($method == "GET")
		$http_result = @file_get_contents("$url?$query", false, stream_context_create($disableSslCheck));
	else {
		$params = array('http' => array(
			'method' => 'POST',
			'content' => $query,
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
				"Content-Length: " . strlen ( $query ) . "\r\n"
		),
			'ssl' => array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);
		print_r($params);
		$ctx = stream_context_create($params);
		$fp = fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			if ($debug) print "HTTP request failed for $url (POST)\n";
			return FALSE;
		}
		$http_result = @stream_get_contents($fp);
		fclose($fp);
	}
	if ($http_result===FALSE) {
		if ($debug) print "HTTP request failed for $url?$query ($method)\n";
		return FALSE;
	}
	$http_code = explode(" ", $http_response_header[0]);
	$http_code = $http_code[1];
	if ( !in_array($http_code, $allowed_http_codes) ) {
		if ($debug) print "HTTP request returned code $http_code for $url?$query ($method)\n";
		return FALSE;
	}
	
	$json_result = json_decode($http_result, true); // Retrieve json as associative array
	if ($json_result===NULL) {
		if ($debug) print "Failed to decode result as JSON\n$http_result\n";
		// Why does this happen!?
		if ($conf_verbosity>0) { print_r($http_result); print_r($parameters); }
		return FALSE;
	}
	
	else if (array_key_exists("server_message", $json_result)) {
		if ($debug) print "Message from server: " . $json_result["server_message"]."\n";
	}
	
	return $json_result;
}

/**
 * Perform a RPC on the REST API given in $conf_backend_url
 *
 * Session data is discovered from global variables populated by check_cookie()
 * Global variable $_api_http_code will be set with server HTTP response code (e.g. 200, 404 etc.)
 * Global array $debug_data is populated with all requests and timings for the duration of script.
 * @param string $route Desired route on backend
 * @param array $params Associative array of parameters (encoding depends on $method)
 * @param string $method HTTP request method
 * @param bool $debug Display debugging messages
 * @param bool $json Decode response from JSON format (if false, response is returned as-is)
 * @param bool $associative Decode response into associative array (if false, response is decoded into object)
 * @return mixed Server response as string (if $json=false), array (if $json=true, $associative=true) or object (if $associative=false)
 */
function api_call($route, $params = [], $method = "GET", $debug = true, $json = true, $associative = true) { // set to false when finished
	global $conf_backend_url, $debug_data, $conf_files_path, $conf_keycloak, $conf_backend_has_rewrite, $login, $_api_http_code;
	
	$http_request_params = array('http' => array(
		'header' => "",
		'method' => $method,
		'ssl' => array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
		'ignore_errors' => true
	));
	
	// mod_rewrite doesn't work on localhost (!?)... add route to request params
	$url = $conf_backend_url;
	if ($method == "GET" || $method == "PUT" || $method == "DELETE") {
		$content = $mimetype = "";
		if (is_object($params)) {
			$content = json_encode($params);
			$mimetype = "application/json";
			$params = [];
		}
		// For GET method, add query data to url
		if ($conf_backend_has_rewrite)
			$url = $url . $route;
		else
			$params["route"] = $route;
		if (!$conf_keycloak)
			$params["SESSION_ID"] = $_SESSION['api_session'];
		$query = http_build_query($params);
		$url = "$url?$query";
	} else {
		$query_params = [];
		if ($conf_backend_has_rewrite)
			$url = $url . $route;
		else
			$query_params["route"] = $route;
		if (!$conf_keycloak)
			$query_params["SESSION_ID"] = $_SESSION['api_session'];
		
		// add route and session id to url
		$url = "$url?" . http_build_query( $query_params );
		
		// Send objects as JSON
		if (is_object($params)) {
			$content = json_encode($params);
			$mimetype = "application/json";
		} else {
			// Otherwise, send urlencoded
			$content = http_build_query($params);
			$mimetype = "application/x-www-form-urlencoded";
		}
	}
	
	if ($conf_keycloak) {
		$token_file = $conf_files_path . "/keycloak_token/$login";
		$token = unserialize(file_get_contents($token_file));
		if (!$token) {
			// We lost the token somehow
			logout();
		}
		$http_request_params['http']['header'] .= "Authorization: Bearer " .  $token->getToken() . "\r\n";
	}
	
	if ($content != "") {
		$http_request_params['http']['content'] = $content;
		$http_request_params['http']['header'] .= "Content-Type: $mimetype\r\n" .
		"Content-Length: " . strlen ( $content ) . "\r\n";
	}
	
	start_time();
	$ctx = stream_context_create($http_request_params);
	$fp = fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		if ($debug) print "HTTP request failed for $url (fopen)\n";
		return FALSE;
	}
	if ($debug)
		$http_result = stream_get_contents($fp);
	else
		$http_result = @stream_get_contents($fp);
	fclose($fp);
	$time = time_elapsed();
	$debug_data[] = [ "route" => $route, "time" => $time];
	
	if ($http_result===FALSE) {
		if ($debug) print "HTTP request failed for $url (returned false)\n";
		return FALSE;
	}
	
	$http_code = explode(" ", $http_response_header[0]);
	$_api_http_code = $http_code[1];
	
	if (!$json) return $http_result;
	
	// DELETE requests don't return a body
	if ($method == "DELETE") {
		$json_result = [];
	} else {
		$json_result = json_decode($http_result, $associative); // Retrieve json as associative array
		if ($json_result === NULL) {
			if ($debug) print "Failed to decode result as JSON for $url\n$http_result\n";
			return FALSE;
		}
	}
	
	if ($associative)
		$json_result['code'] = $_api_http_code;
	else
		$json_result->code = $_api_http_code;

	return $json_result;
}


// Upload file using multipart/form-data
// AFAIK doesn't work with HTTP methods other than POST
function api_file_upload($route, $fieldName, $filename, $mimetype = "application/zip", $params = [], $debug = true) {
	global $conf_backend_url, $_api_http_code, $debug_data, $conf_files_path, $conf_keycloak, $conf_backend_has_rewrite, $login;
	
	$url = $conf_backend_url;
	$query_params = [];
	if ($conf_backend_has_rewrite)
		$url = $url . $route;
	else
		$query_params["route"] = $route;
	if (!$conf_keycloak)
		$query_params["SESSION_ID"] = $_SESSION['api_session'];
	
	// add route and session id to url
	$url = "$url?" . http_build_query( $query_params );
	
	// Otherwise, send urlencoded
	define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
	$header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
	
	if ($conf_keycloak) {
		$token_file = $conf_files_path . "/keycloak_token/$login";
		$token = unserialize(file_get_contents($token_file));
		if (!$token) {
			// We lost the token somehow
			logout();
		}
		$header .= "\r\nAuthorization: Bearer " .  $token->getToken() . "\r\n";
	}
	
	$file_contents = file_get_contents($filename);
	$content =  "--".MULTIPART_BOUNDARY."\r\n".
		"Content-Disposition: form-data; name=\"$fieldName\"; filename=\"".basename($filename)."\"\r\n".
		"Content-Type: $mimetype\r\n\r\n".
		$file_contents."\r\n";

	// add some POST fields to the request too: $_POST['foo'] = 'bar'
	foreach($params as $key => $value) {
		$content .= "--".MULTIPART_BOUNDARY."\r\n".
					"Content-Disposition: form-data; name=\"$key\"\r\n\r\n".
					"$value\r\n";
	}
	
	// signal end of request (note the trailing "--")
	$content .= "--".MULTIPART_BOUNDARY."--\r\n";
	
	$http_request_params = array('http' => array(
		'header' => $header,
		'content' => $content,
		'method' => "POST",
		'ssl' => array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
		'ignore_errors' => true
	));
	
	start_time();
	$ctx = stream_context_create($http_request_params);
	$fp = fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		echo "HTTP request failed for $url (POST)\n";
		return FALSE;
	}
	$http_result = stream_get_contents($fp);
	fclose($fp);
	$time = time_elapsed();
	$debug_data[] = [ "upload route" => $route, "time" => $time];
	
	if ($http_result===FALSE) {
		if ($debug) print "HTTP request failed for $url (file upload)\n";
		return FALSE;
	}
	$http_code = explode(" ", $http_response_header[0]);
	$_api_http_code = $http_code[1];
	
	$json_result = json_decode($http_result, true); // Retrieve json as associative array
	if ($json_result===NULL) {
		if ($debug) print "Failed to decode result as JSON\n$http_result\n";
		return FALSE;
	}
	$json_result['code'] = $_api_http_code;
	
	return $json_result;
}


// Construct ok/error messages
function json_error($code, $msg) {
	$result = array();
	$result['success'] = "false";
	$result['code'] = $code;
	$result['message'] = $msg;
	return $result;
}


// Convert a hierarhical array to a hierarchical stdClass object
function array_to_object($arr) {
	$obj = new stdClass;
	foreach($arr as $key => $value) {
		if (is_array($value))
			$obj->{$key} = array_to_object($value);
		else
			$obj->{$key} = $value;
	}
	return $obj;
}