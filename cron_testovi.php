<?

$keywords=array(
"int","float","double","bool","char","void","const","static", # data types
"for","if","else","while","do","try","catch","throw","system","delete","new","struct","class","template","typename", # language
"cout","cin","setw","endl","getline","ignore","peek","fill","width", # iostream
"printf","scanf","getchar","getch", # cstdio
"pow","sqrt", # cmath
"strlen","strcpy","strncpy", # cstring
"count","count_if","max_element","min_element","sort","copy", # algorithm
"string","vector","deque","begin","end","iterator","size","length", # string+vector+deque
"return","main"
);




require("libvedran.php");
$db = "studenti2";

$aktuelna_zadaca = 3;


/*$k1=normalize(justcode(file_get_contents("$system_path/zadace/$db/483/2/2.c")));
$k2=normalize(justcode(file_get_contents("$system_path/zadace/$db/374/2/2.c")));
print "PRIPO:\n$k1";
print "\nFAZLIC:\n$k2\n\n";
print "Procenat: ".partialmatch($k1,$k2);
print "\nProcenat: ".partialmatch($k2,$k1);

exit;*/

dbconnect();

mysql_select_db("vedran_".$db);

// Punjenje baze zadaca
$fcn = 0;
$dir1 = opendir("$system_path/zadace/$db");
while ($file1 = readdir($dir1)) {
	if ($file1 == "." || $file1 == "..") continue;
	if (is_dir("$system_path/zadace/$db/$file1")) {
		$file2 = $aktuelna_zadaca;
//		while (is_dir("$system_path/zadace/$db/$file1/$file2")) {
		if (!is_dir("$system_path/zadace/$db/$file1/$file2"))
			continue;
			$dir3 = opendir("$system_path/zadace/$db/$file1/$file2");
			while ($file3 = readdir($dir3)) {
				if ($file3 == "." || $file3 == "..") continue;
//print "Citam ($file1,$file2,$file3)\n";
				$fc[$fcn] = file_get_contents("$system_path/zadace/$db/$file1/$file2/$file3");
				$fcjustcode[$fcn] = justcode($fc[$fcn]);
				$fcnormalized[$fcn] = normalize($fcjustcode[$fcn]);
				$fcstudent[$fcn] = $file1;
				$fczadaca[$fcn] = $file2;
				$fczadatak[$fcn] = substr($file3,0,strrpos($file3,"."));
				$fcn++;
//if ($file1==399 && $file2==2 && $file3=="3.c" ) print "Normalized:\n".$fcnormalized[$fcn-1]."\n\n";
			}
//			$file2++;
//		}
	}
}

// for ($i=0; $i<$fcn; $i++) {
// 	if (($fcstudent[$i]==636 && $fczadaca[$i]==1 && $fczadatak[$i]==1) ||
// 		($fcstudent[$i]==535 && $fczadaca[$i]==1 && $fczadatak[$i]==1)) {
// 	print "Student: $fcstudent[$i] Zadaca: $fczadaca[$i] Zadatak: $fczadatak[$i]<br>\n";
// 	print "<pre>".$fcnormalized[$i]."</pre>\n";
// 	}
// }
// exit;

// Testovi

$q2 = myquery("select jezik from zadace_objavljene where aktivna=1 and id=$aktuelna_zadaca");
if (mysql_num_rows($q2)>0) $jezik = mysql_result($q2,0,0);


$q1 = myquery("select zadaca,zadatak,student,id,status from zadace where zadaca=$aktuelna_zadaca order by student,zadaca,zadatak,id desc");
$brojac=0;
while ($r1 = mysql_fetch_row($q1)) {
	if ($r1[0]==$zadaca && $r1[1]==$zadatak && $r1[2]==$stud_id) continue; // ista zadaca, razlicit ID
	$zadaca = $r1[0];
	$zadatak = $r1[1];
	$stud_id = $r1[2];
if ($r1[4]!=1) continue;

$brojac++;
if ($brojac>10) exit;
	print "Testiram ($stud_id,$zadaca,$zadatak)<br>\n";
	
	// JEZIK
	if ($jezik == "C") { $ekst = ".c"; }
	elseif ($jezik == "C++") { $ekst = ".cpp"; }
	else { $ekst = ".cpp"; }	// DEFAULT EKSTENZIJA

	$filename = "$system_path/zadace/$db/$stud_id/$zadaca/$zadatak$ekst";

	// Provjera prepisivanja
	$trenutna = file_get_contents($filename);
	$jc = justcode($trenutna);
	$norm = normalize($jc);

//print "JUSTCODE: \n$jc\n\nNORMALIZE:\n$norm\n";
	for ($i=0; $i<$fcn; $i++) {
		if ($fczadaca[$i]!=$zadaca || $fczadatak[$i]!=$zadatak) continue;
		if ($fcstudent[$i]==$stud_id) continue;
		if ($fc[$i] == $trenutna) {
			print "100% duplikat - "; 
		}
		else { 
			if ($fcjustcode[$i] == $jc) {
				print "100% JC - ";
			} else {
				if ($fcnormalized[$i] == $norm) {
					print "100% NORM - ";
				} else {
					$k=0;
					$p = partialmatch($jc,$fcjustcode[$i]);
					$q = partialmatch($fcjustcode[$i],$jc);
					if ($q>$p) $p=$q;
					if ($p>0.9) {
						print intval($p*100)."% JC - ";
						$k=1;
					}
					$p = partialmatch($norm,$fcnormalized[$i]);
					$q = partialmatch($fcnormalized[$i],$norm);
					if ($q>$p) $p=$q;
					if ($p>0.9) {
						print intval($p*100)."% NORM - ";
						$k=1;
					} 
					if ($k==0) {
						continue;
					}
				}
			}
		}
//		} else {
//			$q3 = myquery("update zadace set status=4 where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id");
		print "($stud_id,$zadaca,$zadatak) <=> ($fcstudent[$i],$fczadaca[$i],$fczadatak[$i])<br>\n";
	} 

	// Test ispravnosti
	$blah = array();
	if ($jezik == "C") {
		$k = exec("/usr/bin/gcc -o /tmp/a.out -lm -pass-exit-codes $filename 2>&1", $blah, $return);
	} else { // DEFAULT JEZIK
		$k = exec("/usr/bin/gcc -o /tmp/a.out -lm -pass-exit-codes $filename 2>&1", $blah, $return);
	}
	$k = my_escape(join("\n",$blah));
	// Izbaci put i ime fajla iz ispisa
	$k = preg_replace("|/srv/www/web2/user/vedran.ljubovic/web/tng/zadace/studenti2/\d+/\d+/\d+.c:(\d+):(\d+):|", "red $1, kolona $2: ", $k);
	$k = preg_replace("|/srv/www/web2/user/vedran.ljubovic/web/tng/zadace/studenti2/\d+/\d+/\d+.c:(\d+):|", "red $1: ", $k);
	$k = preg_replace("|/srv/www/web2/user/vedran.ljubovic/web/tng/zadace/studenti2/\d+/\d+/\d+.c:|", "", $k);
	if ($return == 0) {
//		$q3 = myquery("update zadace set status=4, izvjestaj_skripte='$k' where zadaca=$zadaca and zadatak=$zadatak and student=$stud_id");
		$q3 = myquery("insert into zadace set status=4, izvjestaj_skripte='$k', zadaca=$zadaca, zadatak=$zadatak, student=$stud_id, vrijeme_slanja=now()");
	} else {
		$q3 = myquery("insert into zadace set status=3, izvjestaj_skripte='$k', zadaca=$zadaca, zadatak=$zadatak, student=$stud_id, vrijeme_slanja=now()");
	}
	print "Return: $return Blah: $k<br>\n";
}


# Convert program to normalized form

function justcode($code) {

	foreach (explode("\n",$code) as $k) {
		if (substr($k,0,1) == "#") continue; # Ignore includes and defines
		$k = preg_replace("/\/\/.*/","",$k); # Ignore single-line comments
		$join .= $k; # Join lines 
	}

	$join = strtolower($join); # Lowercase everything
	
	$join = preg_replace('/\s+/',' ',$join); # Ignore whitespace
	$join = preg_replace("/^ /","", $join); $join = preg_replace("/ $/","",$join); # Trailing spaces
	$join = preg_replace("/\/\*.*?\*\//", "", $join); # Ignore multi-line comments
	
	$join = preg_replace("/ (\W)/", "$1", $join); $join = preg_replace("/(\W) /", "$1", $join); # Ignore spaces between anything thats not a letter
	
	$join = preg_replace("/\s?using namespace std;\s?/","", $join); # Drop C++ header
	return $join;
}


function normalize($join) {

	global $keywords;

	# Replace numerals with Nn
	$i=1;
	while (preg_match("/[^\w\.](\d*\.?\d+)[^\w\.]/", $join, $matches)) {
		$f = $matches[1];
		$r = "N".($i++);
		$f = preg_quote($f);
		$join = preg_replace("/([^\w\.])$f([^\w\.])/","$1$r$2", $join);
		$join = preg_replace("/([^\w\.])$f([^\w\.])/","$1$r$2", $join);
	}
	$numerals = $i-1;
	
	# Replace string literals with Sn
	$i=1;
	while (preg_match("/(\".*?\")/", $join, $matches)) {
		$f = $matches[1];
		$r = "S".($i++);
		$f = preg_quote($f);
		$f = str_replace('/','\/', $f);
		$join = preg_replace("/$f/", "$r", $join);
	}
	$strings = $i-1;
	
	# Replace char literals with Cn
	$i=1;
	while (preg_match("/(\'.\')/", $join, $matches)) {
		$f = $matches[1];
		$r = "C".($i++);
		$f = preg_quote($f);
		$f = str_replace('/','\/', $f);
		$join = preg_replace("/$f/", "$r", $join);
	}
	$chars = $i-1;
	
	# Replace keywords (standard library functions) with Kn
	$i=1;
	foreach ($keywords as $k) {
		$join = preg_replace("/(\W)$k(\W)/","$1K$i$2",$join);
		$i++;
	}
	
	# Replace non-keywords (variables, constants... anything :)) with Vn
	$i=1;
	foreach (preg_split("/\W/",$join) as $f) {
		if (!preg_match("/\w/",$f)) { continue; }
		if ($f != strtolower($f)) { continue; } # $f is one of our tags
		$eksit = 0;
		foreach ($keywords as $k) { if ($f == $k) { $eksit=1; break; } }
		if ($eksit == 1) continue;
		if (!preg_match("/\W$f\W/",$join)) { continue; } # Prevent $i growing too fast
		$r = "V".($i++);
		$f = preg_quote($f);
		$join = preg_replace("/(\W)$f(\W)/", "$1$r$2", $join);
		$join = preg_replace("/(\W)$f(\W)/", "$1$r$2", $join);; # Two times, to fix single-letter variables
	}
	$idents = $i-1;
	
	
	return $join;
}

// Pronalazi velicinu najveceg bloka iz S1 koji se javlja u S2
function partialmatch ($s1, $s2) {
	$d = 5;
	$result = 0;
	$i = $j = 0;

if (strlen($s1)<5 || strlen($s2)<5) return 0;

	while ($i<strlen($s1)-$d) {
//print "I: $i J: $j\n";
		$t1 = substr($s1,$i,$d);
		$found = 0;
		$oldj = $j;
//print "Pretest: ($i)=($j)\n";
		while ($j<strlen($s2)-$d) {
			$t2 = substr($s2,$j,$d);
//if ($i==232) print "B: ($i)'$t1'=($j)'$t2'\n";
			while ($t1 == $t2 && $j<strlen($s2)-$d && $i<strlen($s1)-$d) {
//if ($i==232) print "C: ($i)=($j)\n";
				$d++;
//if ($i==232) print "D: ($i)=($j)\n";
				$t1 = substr($s1,$i,$d);
//if ($i==232) print "E: ($i)=($j)\n";
				$t2 = substr($s2,$j,$d);
//print "Test: '$t1'=($j)'$t2'\n";
			}
			if ($d>5) {
//print "Match(".($d-1)."): $t1\n";
				$result += ($d-1);
				$i += ($d-2);
				$j += ($d-2);
				$d = 5;
				$found = 1;
				break;
			}
			$j++;
		}
		if ($found == 0) {
			$j = $oldj;
			$i++;
		}
//print "Loop: ($i)=($j)\n";
	}

//print "Result: $result (".$result/strlen($s1).")\n";
	return $result/strlen($s1);

}

?>

Kraj.
