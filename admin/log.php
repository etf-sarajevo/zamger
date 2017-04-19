<?

// ADMIN/LOG - pregled logova


function admin_log() {

global $userid, $conf_files_path;

global $_lv_; // We use form generators


$maxlogins = 20;
$stardate = int_param('stardate');
if ($stardate == 0) $stardate = time();

$param_nivo = int_param('nivo');
if ($param_nivo<1) $param_nivo=1; // Default nivo
if ($param_nivo>4) $param_nivo=4;

$analyze = int_param('analyze');


// Pretraga / filtriranje

$pretraga = param('pretraga');
$grepovi = array();
$rezultata = 0;
if ($pretraga) {
	$src = preg_replace("/\s+/"," ",$pretraga);
	$src=trim($src);
	$dijelovi = explode(" ", $src);
	$query = "";

	// Probavamo traziti ime i prezime istovremeno
	if (count($dijelovi)==2) {
		$q100 = db_query("select id from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%'");
		if (db_num_rows($q100)==0) {
			$q100 = db_query("select id from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%'");
		}
		$rezultata = db_num_rows($q100);
	}

	// Nismo nasli ime i prezime, pokusavamo bilo koji dio
	if ($rezultata==0) {
		foreach($dijelovi as $dio) {
			if ($query != "") $query .= "or ";
			$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
			if (intval($dio)>0) $query .= "or id=".intval($dio)." ";
		}
		$q100 = db_query("select id from osoba where ($query)");
		$rezultata = db_num_rows($q100);
	}

	// Nismo nasli nista, pokusavamo login
	if ($rezultata==0) {
		$query="";
		foreach($dijelovi as $dio) {
			if ($query != "") $query .= "or ";
			$query .= "a.login like '%$dio%' ";
		}
		$q100 = db_query("select o.id from osoba as o, auth as a where ($query) and a.id=o.id");
		$rezultata = db_num_rows($q100);
	}

	if ($rezultata>0) {
		while ($r100 = db_fetch_row($q100)) {
			$grepovi[] = " ($r100[0]) - ";
			$grepovi[] = "u$r100[0]\d";
			if ($filterupita!="") $filterupita .= " OR ";
			$filterupita .= "userid=$r100[0] OR dogadjaj like '%u$r100[0]%'";
			if ($rezultata==1) $nasaokorisnika = $r100[0]; // najčešće nađemo tačno jednog...
		}
	}

	// Probavamo predmete
	if ($rezultata==0) {
		$q101 = db_query("select id from predmet where naziv like '%$src%' or kratki_naziv='$src'");
		if (db_num_rows($q101)>0) {
			$pp=db_result($q101,0,0);
			$grepovi[] = "pp$pp\d";
			$q102 = db_query("select pk.id from ponudakursa as pk, akademska_godina as ag where pk.predmet=$pp and pk.akademska_godina=ag.id and ag.aktuelna=1");
			while ($r102 = db_fetch_row($q102)) {
				$grepovi[] = "p$r102[0]\d";
			}
		}
	}
}

else if ($analyze) {
	$q105 = db_query("select UNIX_TIMESTAMP(vrijeme), userid FROM log2 WHERE id=$analyze");
	$stardate = db_result($q105,0,0) + 100;
	$koristnik = db_result($q105,0,1);
	$grepovi[] = " ($koristnik) - ";
	$nivo=1;
}

// Grepovi za nivo
if ($param_nivo == 4) $grepovi[] = "\[AAA\]";
else if ($param_nivo > 1) { 
	$grepovi[] = "-v '\-\-\-'";
	if ($param_nivo > 2) $grepovi[] = "-v \[CCC\]";
}


// Izbor nivoa logiranja (JavaScript)

?>
<h3>Pregled logova</h3>
<p>Izaberite logging nivo:<br/>
<?=genform("GET")?>
<table width="100%"><tr>
<td><input type="radio" name="nivo" value="1" onchange="document.forms[0].submit()" <? if ($param_nivo==1) print "CHECKED";?>><img src="static/images/16x16/info.png" width="16" height="16" align="center"> Posjete stranicama</td>
<td><input type="radio" name="nivo" value="2" onchange="document.forms[0].submit()" <? if ($param_nivo==2) print "CHECKED";?>><img src="static/images/16x16/edit_red.png" width="16" height="16" align="center"> Izmjene</td>
<td><input type="radio" name="nivo" value="3" onchange="document.forms[0].submit()" <? if ($param_nivo==3) print "CHECKED";?>><img src="static/images/16x16/warning.png" width="16" height="16" align="center"> Greške</td>
<td><input type="radio" name="nivo" value="4" onchange="document.forms[0].submit()" <? if ($param_nivo==4) print "CHECKED";?>><img src="static/images/16x16/audit.png" width="16" height="16" align="center"> Kritične izmjene</td>
</tr></table>
</form>
<br/><br/>

<center>
<form action="index.php" method="GET">
<input type="hidden" name="sta" value="admin/log">
<input type="hidden" name="nivo" value="<?=$param_nivo?>">
<input type="text" name="pretraga" size="40" value="<?=$pretraga?>">
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" value=" Traži ">
</form>
</center>

<?

// Skripta daj_stablo se sada nalazi u js/stablo.js, a ukljucena je u index.php


// Funkcije koje cachiraju imena korisnika i predmeta 

function get_user_link($id) {
	static $users = array();
	if (!$users[$id]) {
		$q20 = db_query("select ime, prezime from osoba where id=$id");
		if (db_num_rows($q20)>0) {
			$link="?sta=studentska/osobe&akcija=edit&osoba=$id";
			$users[$id] = "<a href=\"$link\" target=\"_new\">".db_result($q20,0,0)." ".db_result($q20,0,1)."</a>";
		} else return $id;
	}
	return $users[$id];
}

function get_predmet_link($id) {
	static $aktuelna_ag = 0; // Aktuelna akademska godina
	if ($aktuelna_ag==0) {
		$q35 = db_query("select id from akademska_godina where aktuelna=1 order by id desc");
		$aktuelna_ag = db_result($q35,0,0);
	}

	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q30 = db_query("select p.id, p.naziv from ponudakursa as pk, predmet as p where pk.id=$id and pk.predmet=p.id");
		if (db_num_rows($q30)>0) {
			$predmeti[$id] = "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=".db_result($q30,0,0)."&ag=$aktuelna_ag\" target=\"_new\">".db_result($q30,0,1)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}


function get_ppredmet_link($id) {
	static $aktuelna_ag = 0; // Aktuelna akademska godina
	if ($aktuelna_ag==0) {
		$q35 = db_query("select id from akademska_godina where aktuelna=1 order by id desc");
		$aktuelna_ag = db_result($q35,0,0);
	}

	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q40 = db_query("select naziv from predmet where id=$id");
		if (db_num_rows($q40)>0) {
			$predmeti[$id] = "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$id&ag=$aktuelna_ag\" target=\"_new\">".db_result($q40,0,0)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}


// Glavni upit i petlja

$lastlogin = array();
$eventshtml = array();
$user_log = array();
$logins=0;
$prvidatum=$zadnjidatum=0;

$godina = date("Y", $stardate);
$mjesec = date("m", $stardate);

do {
	$logfile = $conf_files_path . "/log/$godina/$godina-$mjesec.log";
	if (!file_exists($logfile)) {
		$stardate = daj_najnoviji_stardate($stardate);
		if ($stardate == 0) break;
		$godina = date("Y", $stardate);
		$mjesec = date("m", $stardate);
		continue;
	}
	
	$cmd = "tac $logfile";
	foreach ($grepovi as $grep)
		$cmd .= " | grep $grep";
	
	// Sljedeći kod nam omogućuje da uzimamo output liniju po liniju
	$descriptorspec = array(
		0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
		2 => array("pipe", "w")    // stderr is a pipe that the child will write to
	);

	$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
	if (!is_resource($process)) {
		niceerror("Neuspjelo otvaranje log datoteke");
		return;
	}
	
	while ($s = trim(fgets($pipes[1]))) {
		// Parsiramo log liniju
		if (!preg_match("/^\[(.{3})\] ([\.\d]*?) - ([\w\.]*?)\s*\((\d+?)\) - \[([\d\. \:\-]+?)\] \"(.*?)\"$/", $s, $matches)) {
			print "no matchez $s<br>\n";
			continue;
		}
		
		$nivo = $matches[1];
		$ip_adresa = $matches[2];
		$username = $matches[3];
		$usr = $matches[4];
		$datum_vrijeme = $matches[5];
		$evt = $matches[6];
		
		$timestamp = strtotime($datum_vrijeme);
		if ($timestamp > $stardate) continue;
		$nicedate = " (".date("d.m.Y. H:i:s", $timestamp).")";
		
		//if (strlen($evt)>100) $evt = substr($evt,0,100); // but why?

		// ne prikazuj login ako je to jedina stavka, ako je nivo veci od 1 ili ako nema pretrage
		if ($lastlogin[$usr]==0 && (($nivo==1 && $pretraga=="") || $evt != "login")) { 
			$lastlogin[$usr]=$timestamp;
			$logins++;
			if ($logins > $maxlogins) {
				break; // izlaz iz while
			}
		}
		
		if ($nivo == "---") $nivoimg="info";
		else if ($nivo == "CCC") $nivoimg="edit_red";
		else if ($nivo == "EEE") $nivoimg="warning";
		else if ($nivo == "AAA") $nivoimg="audit";


		// Prepoznavanje određenih elemenata eventa - TAGOVA
		// Legenda:
		//   uID - korisnik
		//   ppID - predmet
		//   pID - ponudakursa
		//   gID - labgrupa
		//   cID - čas
		//   zID - zadaća
		//   iID - ispit
		//   agID - akademska godina
		//   sID - studij

		while (preg_match("/\Wu(\d+)/", $evt, $m)) { // korisnik
			$evt = str_replace("u$m[1]",get_user_link($m[1]), $evt);
			$zadnjikorisnik = $m[1]; // Ovo ce omoguciti neke dodatne upite kasnije
		}
		while (preg_match("/\Wpp(\d+)/", $evt, $m)) { // predmet
			$evt = str_replace("pp$m[1]",get_ppredmet_link($m[1]),$evt);
		}
		while (preg_match("/\Wp(\d+)/", $evt, $m)) { // ponudakursa
			$evt = str_replace("p$m[1]",get_predmet_link($m[1]),$evt);
		}
		while (preg_match("/\Wg(\d+)/", $evt, $m)) { // labgrupa
			$q39 = db_query("select naziv from labgrupa where id=$m[1]");
			if (db_num_rows($q39)>0) {
				$evt = str_replace("g$m[1]","<a href=\"?sta=saradnik/grupa&id=$m[1]\" target=\"_blank\">".db_result($q39,0,0)."</a>",$evt);
			} else {
				$evt = str_replace("g$m[1]","$m[1]",$evt);
			}
		}
		while (preg_match("/\Wc(\d+)/", $evt, $m)) { // cas
			$q40 = db_query("select labgrupa from cas where id=$m[1]");
			if (db_num_rows($q40)>0) {
				$link="?sta=saradnik/grupa&id=".db_result($q40,0,0);
				$evt = str_replace("c$m[1]","<a href=\"$link\" target=\"_blank\">$m[1]</a>",$evt);
			} else {
				$evt = str_replace("c$m[1]","$m[1]",$evt);
			}
		}
		if (preg_match("/\Wz(\d+)/", $evt, $m)) { // zadaca
			$q50 = db_query("select naziv,predmet,akademska_godina from zadaca where id=$m[1]");
			if (db_num_rows($q50)>0) {
				$naziv=db_result($q50,0,0);
				if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
				$predmet=db_result($q50,0,1);
				$ag=db_result($q50,0,2);
				if (intval($usr)>0) {
					$q55 = db_query("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$usr and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
					if (db_num_rows($q55)<1 && $zadnjikorisnik>0) {
						$q55 = db_query("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$zadnjikorisnik and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
					}
					if (db_num_rows($q55)<1) {
						$q55 = db_query("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
					}
					$link="?sta=saradnik/grupa&id=".db_result($q55,0,0);
					$evt = str_replace("z$m[1]","<a href=\"$link\" target=\"_blank\">$naziv</a>",$evt);
				}
			}
		}
		while (preg_match("/\Wi(\d+)/", $evt, $m)) { // ispit
			$q60 = db_query("select k.gui_naziv, i.predmet, p.naziv, i.akademska_godina from ispit as i, komponenta as k, predmet as p where i.id=$m[1] and i.komponenta=k.id and i.predmet=p.id");
			if (db_num_rows($q60)>0) {
				$naziv=db_result($q60,0,0);
				if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
				$predmet=db_result($q60,0,1);
				$predmetnaziv=db_result($q60,0,2);
				$ag=db_result($q60,0,3);
				$evt = str_replace("i$m[1]","<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\" target=\"_blank\">$naziv ($predmetnaziv)</a>",$evt);
			} else {
				$evt = str_replace("i$m[1]","$m[1]",$evt);
			}
		}
		while (preg_match("/\Wag(\d+)/", $evt, $m)) { // akademska godina
			$q70 = db_query("select naziv from akademska_godina where id=$m[1]");
			if (db_num_rows($q70)>0) {
				$naziv=db_result($q70,0,0);
				$evt = str_replace("ag$m[1]","$naziv",$evt);
			} else {
				$evt = str_replace("ag$m[1]","$m[1]",$evt);
			}
		}
		while (preg_match("/\Ws(\d+)/", $evt, $m)) { // studij
			$q80 = db_query("select naziv from studij where id=$m[1]");
			if (db_num_rows($q80)>0) {
				$naziv=db_result($q80,0,0);
				$evt = str_replace("s$m[1]","$naziv",$evt);
			} else {
				$evt = str_replace("s$m[1]","$m[1]",$evt);
			}
		}


		// Pošto idemo unazad, login predstavlja kraj zapisa za korisnika

		if ($evt == "login") {
			if ($lastlogin[$usr] && $lastlogin[$usr]!=0) {
				$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"static/images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"static/images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> login (ID: $usr) $nicedate\n".$eventshtml[$lastlogin[$usr]];
				$user_log[$lastlogin[$usr]] = $usr;
				$stardate=$timestamp;
				$lastlogin[$usr]=0;
			}
		}
		else if (strstr($evt," su=")) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"static/images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"static/images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> SU to ID: $usr $nicedate\n".$eventshtml[$lastlogin[$usr]];
			$user_log[$lastlogin[$usr]] = $usr;
			$lastlogin[$usr]=0;
		}


		else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"static/images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"static/images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> ".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
			$user_log[$lastlogin[$usr]] = $usr;
		}
	}
	
	$mjesec--;
	if ($mjesec==0) { $mjesec=12; $godina--; }

} while($logins <= $maxlogins);


// Dodajemo zaglavlja sa [+] poljem (prebaciti iznad)

foreach ($eventshtml as $logid => $event) {
	if (substr($event,0,4)!="<img") {
		// Login počinje sa <br/>

		// logid je sada vrijeme
		$nicedate = " (".date("d.m.Y. H:i:s", $logid).")";
		$userid = $user_log[$logid];

		if ($userid==0) {
			$imeprezime = "ANONIMNI PRISTUPI";
			$usrimg="bug";

		} else {
			$q202 = db_query("select ime, prezime from osoba where id=$userid");
			$imeprezime = db_result($q202,0,0)." ".db_result($q202,0,1);

			$q203 = db_query("select count(*) from privilegije where osoba=$userid and privilegija='nastavnik'");
			$q204 = db_query("select count(*) from privilegije where osoba=$userid and privilegija='studentska'");
			$q205 = db_query("select count(*) from privilegije where osoba=$userid and privilegija='siteadmin'");

			if (db_result($q205,0,0)>0) {
				$usrimg="admin"; 
			} else if (db_result($q204,0,0)>0) {
				$usrimg="teta"; 
			} else if (db_result($q203,0,0)>0) {
				$usrimg="tutor"; 
			} else {
				$usrimg="user";
			}
		}
	
		$link = "?sta=studentska/osobe&akcija=edit&osoba=$userid";

		print "<img src=\"static/images/plus.png\" width=\"13\" height=\"13\" id=\"img-$logid\" onclick=\"daj_stablo('$logid')\">
<img src=\"static/images/16x16/$usrimg.png\" width=\"16\" height=\"16\" align=\"center\">
<a href=\"$link\">$imeprezime</a> $nicedate
<div id=\"$logid\" style=\"display:none\">\n";
	}

	print "$event</div><br/>\n";
}

print "<p>&nbsp;</p><p><a href=\"".genuri()."&stardate=$stardate\">Sljedećih $maxlogins</a></p>";



}


// Ako ne postoji logfile za dati stardate, ova funkcija će probati naći najnoviji logfile
// koji je stariji od datog, ili vratiti nulu ako takav ne postoji
function daj_najnoviji_stardate($stardate) {
	global $conf_files_path;

	$godina = date("Y", $stardate);
	$mjesec = date("m", $stardate);
	do {
		$mjesec--;
		if ($mjesec == 0) { $mjesec=12; $godina--; }
		$logfile = $conf_files_path . "/log/$godina/$gmj.log";
	} while(!file_exists($logfile));
	
	// Uzimamo prvu sekundu sljedećeg mjeseca, pa smanjujemo za 1
	$mjesec++;
	if ($mjesec==13) { $mjesec=1; $godina++; }
	$stardate = mktime(0, 0, 0, $mjesec, 1, $godina);
	return $stardate-1;
}


?>