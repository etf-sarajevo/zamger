<?

$keywords=array(
"int","float","double","bool","char","void","const","static","true","false", # data types
"for","if","else","while","do","try","catch","throw","system","delete","new","struct","class","template","typename","enum","public","private", # language
"cout","cin","setw","endl","getline","ignore","peek","fill","width", # iostream
"printf","scanf","getchar","getch", # cstdio
"pow","sqrt", # cmath
"strlen","strcpy","strncpy", # cstring
"count","count_if","max_element","min_element","sort","copy", # algorithm
"string","vector","deque","begin","end","iterator","size","length", # string+vector+deque
"return","main"
);

require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");
require("../www/lib/config.php");

$aktuelna_zadaca = 1320;
$trazeni_zadatak=1;
$predmet = "42-7";

dbconnect();


// Jezik zadace
$q2 = myquery("select pj.naziv,pj.ekstenzija from programskijezik as pj,zadaca where zadaca.id=$aktuelna_zadaca and zadaca.programskijezik=pj.id");
if (mysql_num_rows($q2)>0) {
	$jezik = mysql_result($q2,0,0);
	$ekst = mysql_result($q2,0,1);
} else {
	// Default jezik: C++
	$jezik = "C++";
	$ekst = ".cpp";
}



$kk=0;
$fcn = 0;
$dir1 = opendir("$conf_files_path/zadace/$predmet");
$maxzad=0;
while ($student = readdir($dir1)) {
	if ($student == "." || $student == "..") continue;
	if (is_dir("$conf_files_path/zadace/$predmet/$student")) {
		$zadaca = $aktuelna_zadaca;

		if (!is_dir("$conf_files_path/zadace/$predmet/$student/$zadaca"))
			continue;

		$dir3 = opendir("$conf_files_path/zadace/$predmet/$student/$zadaca");

		while ($file3 = readdir($dir3)) {
			if ($file3 == "." || $file3 == "..") continue;

			$filename = "$conf_files_path/zadace/$predmet/$student/$zadaca/$file3";

			$zadatak = substr($file3,0,strrpos($file3,"."));
			if ($zadatak == "") continue;
			if ($trazeni_zadatak != 0 && $trazeni_zadatak!=$zadatak) continue;

			if ($zadatak>$maxzad) $maxzad=$zadatak;
			// Treba li testirati zadacu?
			$q2 = myquery("select status, izvjestaj_skripte from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$student order by id desc limit 1");
			$pregledati=0;
			if (mysql_num_rows($q2)>0 && mysql_result($q2,0,0)==4) {
				$izvjestaj = mysql_result($q2,0,1);
				print "Markiram ($student,$zadaca,$zadatak)\n";
				$q3 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=1, izvjestaj_skripte='".mysql_real_escape_string($izvjestaj)."', vrijeme=NOW(), filename='".mysql_real_escape_string($file3)."'");
			}

		}
	}
}

?>

Kraj.
