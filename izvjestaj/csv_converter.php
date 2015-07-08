<?
  
function ob_file_callback($buffer) {
	global $sadrzaj_bafera_za_csv;
	$sadrzaj_bafera_za_csv=$buffer;
}


function izvjestaj_csv_converter() {
	global $sadrzaj_bafera_za_csv,$conf_files_path, $registry;
	global $userid, $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;

	// Određujemo separator iz korisničkih preferenci
	$separator = ";";
	if ($userid>0) {
		$q10 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='csv-separator'");
		if (mysql_num_rows($q10)>0)
			$separator = mysql_result($q10,0,0);
	}

	ob_start('ob_file_callback');
	$koji = my_escape($_REQUEST['koji_izvjestaj']);
	$staf = str_replace("/","_",$koji);

	$found=false;
	foreach ($registry as $r) {
		if ($r[0] == $koji) {
			if (strstr($r[3],"P") || (strstr($r[3],"S") && $user_student) || (strstr($r[3],"N") && $user_nastavnik) || (strstr($r[3],"B") && $user_studentska) || (strstr($r[3],"A") && $user_siteadmin)) {
				$found=true;
			} else {
				zamgerlog ("csv_converter pristup nedozvoljenom modulu $koji", 3);
				zamgerlog2 ("pristup nedozvoljenom modulu", 0, 0, 0, $koji);
				niceerror("Pristup nedozvoljenom modulu");
				return;
			}
			break;
		}
	}
	if ($found===false) {
		zamgerlog ("csv_converter nepostojeći modul $koji", 3);
		zamgerlog2 ("nepostojeci modul", 0, 0, 0, $koji);
		niceerror("Pristup nepostojećem modulu");
		return;
	}


	include("$koji.php");//ovdje ga ukljucujem
	eval("$staf();");
	ob_end_clean();

	// Konverzija charseta
	$encoding = "Windows-1250";
	if ($userid>0) {
		$q10 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='csv-encoding'");
		if (mysql_num_rows($q10)>0)
			$encoding = mysql_result($q10,0,0);
	}
	if ($encoding != "UTF-8") $sadrzaj_bafera_za_csv = iconv("UTF-8", $encoding, $sadrzaj_bafera_za_csv);

	// Neke uobičajene tagove za novi red unutar polja tabele pretvaramo u space
	// a van polja tabele u novi red
	$sadrzaj_bafera_za_csv = preg_replace("/(\<td.*?\>.*?)\<br.*?\>(.*?\<\/td\>)/",'$1 $2',$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = preg_replace("/(\<td.*?\>.*?)\<\/p\>(.*?\<\/td\>)/",'$1 $2',$sadrzaj_bafera_za_csv);

	// Ukidamo nove redove i spajamo whitespace
	$sadrzaj_bafera_za_csv = str_replace("\n","",$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = str_replace("&nbsp;"," ",$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = preg_replace("/\s+/"," ",$sadrzaj_bafera_za_csv);

	// Pretvaramo uobičajene tagove za novi red u \n
	$sadrzaj_bafera_za_csv = preg_replace("/\<br.*?\>/","\n",$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = str_replace("</p>","\n",$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = preg_replace("/\<\/h[123]\>/","\n",$sadrzaj_bafera_za_csv);

	// Na sve ćelije tabele dodajemo separator
	// Zbog performansi unutar ove petlje ćemo obraditi razne posebne slučajeve
	while (preg_match("/(\<td.*?\>)(.*?)(\<\/td\>)/", $sadrzaj_bafera_za_csv, $matches)) {
		$sadrzaj = $matches[2];

		// Ukidamo početni whitespace
		while (substr($sadrzaj,0,1)==" ") $sadrzaj = substr($sadrzaj,1);

		// Ako je separator tačka-zarez, decimale se razdvajaju zarezom
		if ($separator == ";" && $result = strstr($sadrzaj,".")) {
			while ($result) {
				$res2 = ",".substr($result,1);
				if (ord(substr($result, 1, 1)) >= ord('1') && ord(substr($result, 1, 1)) <= ord('9'))
					$sadrzaj = str_replace ($result, $res2, $sadrzaj);
				$result = strstr(substr($result,1), ".");
			}
		}
			
		// Ako se u sadržaju javlja separator, stavljamo pod navodnike
		if ($result = strstr($sadrzaj,$separator)) {
			// Ako je separator zarez, za decimale koristimo tačku
			if ($separator == ",") {
				$res2 = ".".substr($result,1);
				if (ord(substr($result, 1, 1)) >= ord('0') && ord(substr($result, 1, 1)) <= ord('9'))
					$sadrzaj = str_replace ($result, $res2, $sadrzaj);
			}

			// Ako i dalje sadrži separator stavljamo ga pod navodnike
			if (strstr($sadrzaj,$separator)) {
				// Prethodno sve navodnike u stringu dupliramo
				$sadrzaj = str_replace('"', '""', $sadrzaj);
				$sadrzaj = '"'.$sadrzaj.'"';
			}
		}

		// Dodajemo separator na kraj
		$sadrzaj = $sadrzaj.$separator;

		// Za colspan dodajemo još separatora
		if ($result = strstr($matches[1], "colspan=")) {
			$i=8;
			$broj = "";
			do {
				$slovo = substr($result, $i, 1);
				$broj .= $slovo;
				if ($broj=='"') $broj="";
				else if ($slovo=='"' || $slovo==' ') break;
				$i++;
			} while (true);
			for ($i=1; $i<$broj; $i++)
				$sadrzaj = $sadrzaj.$separator;
		}
		// TODO može li se rowspan nekako riješiti? morao bi se pratiti <tr>

		// Ubacujemo novi sadržaj
		$sadrzaj_bafera_za_csv = str_replace($matches[0], $sadrzaj, $sadrzaj_bafera_za_csv);
	}

	// Pretvaramo </tr> u novi red a </td> u separator
	$sadrzaj_bafera_za_csv = str_replace("</tr>","\n",$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = str_replace("</td>",$separator,$sadrzaj_bafera_za_csv);

	// Ostale tagove ukidamo
	$sadrzaj_bafera_za_csv = strip_tags($sadrzaj_bafera_za_csv);

	// Čistimo whitespace na početku reda / ćelije
	$sadrzaj_bafera_za_csv = str_replace("\n ","\n",$sadrzaj_bafera_za_csv);
	$sadrzaj_bafera_za_csv = str_replace("$separator ","$separator",$sadrzaj_bafera_za_csv);


	header("Content-Disposition: inline; filename=".$staf.".csv");
	header("Content-Type: text/csv; charset=".$encoding);

	header("Pragma: dummy=bogus"); 
	header("Cache-Control: private");

	print $sadrzaj_bafera_za_csv;
}

?>
