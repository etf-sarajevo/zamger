<?

// ROUTE.PHP - router za REST web servis


header('Content-Type: application/json; charset=utf-8');


require_once("Config.php");
require_once("Session.php");

require_once(Config::$backend_path."lib/DB.php");
require_once(Config::$backend_path."lib/AccessControl.php");
require_once(Config::$backend_path."lib/UnresolvedClass.php");
require_once(Config::$backend_path."lib/Util.php");

// Data classes used
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/CourseUnitYear.php");
require_once(Config::$backend_path."core/Institution.php");
require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."core/Programme.php");
require_once(Config::$backend_path."core/ProgrammeType.php");

require_once(Config::$backend_path."sis/ExtendedPerson.php");


require_once("wiring.php");


// CORS
$add_vary = false;
foreach (Config::$api_allowed_uris as $uri) {
	header("Access-Control-Allow-Origin: $uri");
	if ($uri != '*') $add_vary = true;
}
if ($add_vary)
	header("Vary: Origin");


// Web service router
$route = Util::param('route');
// Strip get params
if (strpos($route, "?")) $route = substr($route, 0, strpos($route, "?"));
if (strpos($route, "&")) $route = substr($route, 0, strpos($route, "&"));
// Route not given?
if (!$route) {
	header("HTTP/1.0 404 Not Found");
	$result = array( 'success' => 'false', 'code' => 'ERR404', 'message' => 'Invalid path' );
	echo json_encode($result);
	return;
}


// Initialize database
DB::connect();


// Handle JSON encoded requests
if (isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/json") {
	$_REQUEST = json_decode(file_get_contents('php://input'),true);
	if ($_SERVER['REQUEST_METHOD'] == "GET")
		$_GET = json_decode(file_get_contents('php://input'),true);
	else if ($_SERVER['REQUEST_METHOD'] == "POST")
		$_POST = json_decode(file_get_contents('php://input'),true);
}


// Web service for authentication
if ($route == "auth") { 
	$login = DB::escape($_POST['login']);
	$pass = $_POST['pass'];
	$result = array();

	$status = Session::login($login, $pass);
	if ($status == 1 || $status == 2) { 
		$result['success'] = "false";
		//$result['code'] = $status;
		$result['message'] = "Unknown user or wrong password";
	} else {
		$result['success'] = "true";
		//$result['code'] = $status;
		$result['sid'] = Session::$id;
		$result['userid'] = Session::$userid;
	}

	print json_encode($result);

	DB::disconnect();
	return;
}


Session::verify();
Session::getCoarsePrivileges();
// After this, Session attributes are filled


// User has no privileges?
if (Session::$userid > 0 && empty(Session::$privileges)) {
	$result = array( 'success' => 'false', 'code' => '998', 'message' => 'You are logged in but you have no privileges.' );
	echo json_encode($result);
	DB::disconnect();
	return;
}

	
// Detect path and execute corresponding code

$result = array(); // This will contain the output data

foreach ($wiring as $wire) {
	$path = $wire['path'];
	
	// Extract variables from path and convert it to regex
	$variables = array();
	while (preg_match("/\{(.*?)\}/", $path, $matches)) {
		$variables[] = $matches[1];
		// Only integer path components are supported!
		$path = str_replace("{".$matches[1]."}", "(\d+)", $path);
	}
	
	// Does route match given?
	$path = str_replace("/", "\/", $path);
	//print "matching $path to $route\n";
	if (!preg_match("/^$path$/", $route, $matches) || $_SERVER['REQUEST_METHOD'] != $wire['method']) continue;
	
	// Insert remaining path components into global scope
	$code = $wire['code'];
	for ($i=1; $i<count($matches); $i++) {
		$varname = $variables[$i-1];
		$varvalue = intval($matches[$i]);
		$$varname = $varvalue;
	}
		
	// Inject request params into global scope
	if (array_key_exists('params', $wire))
	foreach($wire['params'] as $name => $type) {
		if ($type == "int")
			$$name = Util::int_param($name);
		if ($type == "float")
			$$name = floatval(Util::param($name));
		if ($type == "string")
			$$name = Util::param($name);
		if ($type == "object")
			// Force JSON format... ?
			$$name = json_decode(file_get_contents('php://input'));
	}
	
	// Check privileges - may depend on params
	if (strstr($wire['acl'], "||")) 
		$wire['acl'] = preg_replace("/\|\|\s?/", "|| AccessControl::", $wire['acl']);
	if (!eval("return AccessControl::" . $wire['acl'] . ";")) {
		header("HTTP/1.0 403 Forbidden");
		$result = array( 'success' => 'false', 'code' => '403', 'message' => 'Permission denied' );
		break;
	}
	
	//print "code is: $code\n";
	
	try {
		$result = eval($code);
		
		// Resolve subclasses if required
		$resolve = array();
		if (array_key_exists('autoresolve', $wire)) $resolve = $wire['autoresolve'];
		if (isset($_REQUEST['resolve'])) $resolve += $_REQUEST['resolve'];
		foreach ($resolve as $className)
			UnresolvedClass::resolveAll($result, $className);

		// PHP MySQL driver returns all numbers as strings, which is no problem in PHP
		// but in other langs it may be easier if numbers are not quoted
		Util::fix_data_types($result);


		// Convert array into object representation for JSON
		// (most tools can't handle API that returns array)
		if (is_array($result)) {
			$result_array = $result;
			$result = array();
			$result['results'] = $result_array;

			// Also do paging
			if (isset($_REQUEST['pageSize'])) {
				$size = abs(Util::int_param('pageSize'));
				$page = abs(Util::int_param('page'));
				if (count($result['results']) > $size) {
					$results['totalResults'] = count($result['results']);
					$results['page'] = $page;

					$start_pos = $size*($page-1);
					$end_pos = $size*$page;

					array_splice($result['results'], $end_pos);
					if ($page > 1) array_splice($result['results'], 0, $start_pos);

					// Add HATEOAS links for prev/next page
					$link = $wire['path'];
					if (strpos($link, "?")) $link .= "&"; else $link .= "?";
					$link .= "pageSize=$size&page=";
					if ($end_pos < count($result_array))
						$wire['hateoas_links']['next'] = array("href" => $link . ($page+1));
					if ($page > 1)
						$wire['hateoas_links']['previous'] = array("href" => $link . ($page-1));
				}
			}
		}
		
	} catch(Exception $e) {
		if ($e->getCode() == "404")
			header("HTTP/1.0 404 Not Found");
		if ($e->getCode() == "403")
			header("HTTP/1.0 403 Forbidden");
		$result = array( 'success' => 'false', 'code' => $e->getCode(), 'message' => $e->getMessage() );
		
		// Add some more database debugging
		if ($e->getCode() == "800" && Config::$database_debug)
			$result['db_error'] = DB::$error;
	}
	
	// Decorate with HATEOAS HAL (http://stateless.co/hal_specification.html)
	if (array_key_exists("hateoas_links", $wire)) {
		// Assumption: $result contains 'id'
		foreach($wire['hateoas_links'] as $name => &$link) {
			$link['href'] = Config::$api_url . "/" . $link['href'];
			if ($name == "self" && is_object($result))
				$link['href'] = str_replace("[id]", $result->id, $link['href']);
		}
		if (is_object($result))
			$result->_links = $wire['hateoas_links'];
		else
			$result['_links'] = $wire['hateoas_links'];
	}
}

// Path not found
if (empty($result)) {
	header("HTTP/1.0 404 Not Found");
	$result = array( 'success' => 'false', 'code' => '404', 'message' => 'Invalid path' );
}

//echo json_encode($result, Config::$json_options);
echo json_encode($result);
DB::disconnect();
return;



?>
