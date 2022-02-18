<?

// LIB/HACK - stvari koje se (najčešće) ne puštaju u produkciji

$debug_data = [];

// Funkcije za mjerenje vremena izvršenja PHP koda
function start_time() {
	global $rustart;
	$rustart = microtime();
}
function time_elapsed() {
	global $rustart;
	$now = microtime();

	list($sus, $ss) = explode(" ", $rustart);
	list($nus, $ns) = explode(" ", $now);
	$time = $ns-$ss;
	$time = $time + ($nus-$sus)/1000000;
	$rustart = $now;
	return $time;
}
function debug_data_dump() {
	global $debug_data;
	print "Debug data:<br>\n";
	foreach($debug_data as $data) {
		foreach ($data as $key => $value)
			print "$key => $value; ";
		print "<br>\n";
	}
}
function api_debug($data, $textarea=false) {
	if ($textarea) {
		print "<textarea cols=50>";
		print_r($data);
		print "</textarea>";
	} else {
		print "<pre>";
		print_r($data);
		print "</pre>";
	}
}

?>
