<?


// DUMP_LOG.PHP - skripta za migraciju loga pristupa iz MySQL tabele "log" u novi format log datoteka

require("../www/lib/config.php");
require("../www/lib/dblayer.php");
require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");
require("../www/lib/manip.php");


db_connect($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

$logini = db_query_vassoc("SELECT id, login FROM auth");

$log_path = $conf_files_path . "/log";

$page_size = 10000;
$max_id = 30671796;
//$max_id = 0;
$start = 0;

$old_gmj = "";
$output = "";

do {
	$q10 = db_query("SELECT vrijeme, userid, dogadjaj, nivo FROM log where id>$start and id<".($start + $page_size) . " order by id");
	while ($r10 = db_fetch_row($q10)) {
		$gmj = substr($r10[0], 0, 7);
		if ($gmj != $old_gmj) {
			if ($old_gmj != "") fclose($handle);
			$godina = substr($gmj, 0, 4);
			if (!file_exists($log_path . "/$godina")) mkdir ($log_path . "/$godina");
			$handle = fopen($log_path . "/$godina/$gmj.log", "a");
			$old_gmj = $gmj;
		}
	
		$output = "";
		if ($r10[3] == 1) $output .=  "[---] ";
		if ($r10[3] == 2) $output .=  "[CCC] ";
		if ($r10[3] == 4) $output .=  "[AAA] ";
		if ($r10[3] == 3) $output .=  "[EEE] ";
		$output .= "127.0.0.1 - ";
		if ($logini[$r10[1]]) $output .= $logini[$r10[1]] . " ";
		$output .= "(" . $r10[1]. ") - ";
		$output .=  "[" . $r10[0] . "] ";
		$tekst = str_replace("\"", "\\\"", $r10[2]);
		$tekst = str_replace("\r\n", "\\n", $tekst);
		$tekst = str_replace("\n", "\\n", $tekst);
		$output .= "\"".$tekst."\"\n";
		fputs($handle, $output);
	}
	
	$start += $page_size;
	
} while ($start < $max_id);

db_disconnect();

?>
