<?

// LIB/HACK - stvari koje se (najčešće) ne puštaju u produkciji


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
	print "Time $time<br>\n";
	$rustart = $now;
}

?>
