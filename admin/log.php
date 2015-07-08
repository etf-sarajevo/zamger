<?

// ADMIN/LOG - pregled logova

// v3.9.1.0 (2008/02/26) + Preimenovan bivsi admin_site
// v3.9.1.1 (2008/03/04) + Dodani logging nivoi, pocetak novog formata logova
// v3.9.1.2 (2008/03/08) + Nova auth tabela
// v3.9.1.3 (2008/03/15-22) + Kod nivoa>1 nisu razdvajani logini, popravljen SU, novi format loga (stari će biti uklonjen uskoro)
// v3.9.1.4 (2008/04/09) + Popravljen prikaz ispita
// v3.9.1.5 (2008/04/28) + Naslov u <h3>
// v3.9.1.6 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.7 (2008/09/08) + JOIN izmedju log i osoba ne mora vratiti nista ako je userid 0
// v3.9.1.8 (2008/10/31) + Dodana mogucnost pretrage; dodan tag za studij
// v3.9.1.9 (2009/01/23) + Podignut default nivo radi brzeg otvaranja
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet; obrisan legacy parser koji se koristio u ranim dev verzijama loga
// v4.0.9.3 (2009/04/07) + Dodajem tag za lab grupu
// v4.0.9.4 (2009/04/22) + Tag za predmet (za razliku od ponudekursa) - posto se dobar broj modula prebacuje na predmet, bice lakse logirati tako; prebacujem labgrupu sa ponudakursa na predmet
// v4.0.9.5 (2009/05/05) + Vise ispravki kod pretrage, dodan prikaz masovnih unosa za studenta koji se pretrazuje; popravljen pogresan link na zadace, ispite, grupe
// v4.0.9.6 (2009/05/18) + Popravljen link na grupu "svi studenti" (ne postoji vise grupa 0)
// v4.0.9.7 (2009/05/20) + Ukinuta labgrupa 0 i polja predmet i ag iz tabele labgrupa
// v4.0.9.8 (2009/10/07) + Omoguceno vise tagova istog tipa a razlicitog sadrzaja (npr. u123 poslao poruku u456)


function admin_log() {

global $userid;

global $_lv_; // We use form generators


$maxlogins = 20;
$stardate = intval($_GET['stardate']);
if ($stardate == 0) {
	$q199 = myquery("select id from log order by id desc limit 1");
	$stardate = mysql_result($q199,0,0)+1;
}
$nivo = intval($_GET['nivo']);
if ($nivo<1) $nivo=2;
if ($nivo>4) $nivo=4;

$analyze = intval($_REQUEST['analyze']);


// Pretraga / filtriranje

$pretraga = $_REQUEST['pretraga'];
if ($pretraga) {
	$src = preg_replace("/\s+/"," ",$pretraga);
	$src=trim($src);
	$dijelovi = explode(" ", $src);
	$query = "";
	$filterupita = "";

	// Probavamo traziti ime i prezime istovremeno
	if (count($dijelovi)==2) {
		$q100 = myquery("select id from osoba where ime like '%$dijelovi[0]%' and prezime like '%$dijelovi[1]%'");
		if (mysql_num_rows($q100)==0) {
			$q100 = myquery("select id from osoba where ime like '%$dijelovi[1]%' and prezime like '%$dijelovi[0]%'");
		}
		$rezultata = mysql_num_rows($q100);
	}

	// Nismo nasli ime i prezime, pokusavamo bilo koji dio
	if ($rezultata==0) {
		foreach($dijelovi as $dio) {
			if ($query != "") $query .= "or ";
			$query .= "ime like '%$dio%' or prezime like '%$dio%' or brindexa like '%$dio%' ";
			if (intval($dio)>0) $query .= "or id=".intval($dio)." ";
		}
		$q100 = myquery("select id from osoba where ($query)");
		$rezultata = mysql_num_rows($q100);
	}

	// Nismo nasli nista, pokusavamo login
	if ($rezultata==0) {
		$query="";
		foreach($dijelovi as $dio) {
			if ($query != "") $query .= "or ";
			$query .= "a.login like '%$dio%' ";
		}
		$q100 = myquery("select o.id from osoba as o, auth as a where ($query) and a.id=o.id");
		$rezultata = mysql_num_rows($q100);
	}

	if ($rezultata>0) {
		while ($r100 = mysql_fetch_row($q100)) {
			if ($filterupita!="") $filterupita .= " OR ";
			$filterupita .= "userid=$r100[0] OR dogadjaj like '%u$r100[0]%'";
			if ($rezultata==1) $nasaokorisnika = $r100[0]; // najčešće nađemo tačno jednog...
		}
	}

	// Probavamo predmete
	if ($rezultata==0) {
		$q101 = myquery("select id from predmet where naziv like '%$src%' or kratki_naziv='$src'");
		if (mysql_num_rows($q101)>0) {
			$pp=mysql_result($q101,0,0);
			if ($filterupita!="") $filterupita .= " OR ";
			$filterupita .= "dogadjaj like '%pp$pp%'";
			$q102 = myquery("select pk.id from ponudakursa as pk, akademska_godina as ag where pk.predmet=$pp and pk.akademska_godina=ag.id and ag.aktuelna=1");
			while ($r102 = mysql_fetch_row($q102)) {
				$filterupita .= " OR dogadjaj like '%p$r102[0]%'";
			}
		}
	}

	// Kraj, dodajemo and
	if ($filterupita!="") $filterupita = " AND ($filterupita)";
}

else if ($analyze) {
	$q105 = myquery("select UNIX_TIMESTAMP(vrijeme), userid FROM log2 WHERE id=$analyze");
	$vrijeme = mysql_result($q105,0,0);
	$nasaokorisnika = mysql_result($q105,0,1);
	$filterupita = " AND userid=$nasaokorisnika";
	$q106 = myquery("select id from log where vrijeme=FROM_UNIXTIME($vrijeme) limit 1");
	$stardate = mysql_result($q106,0,0)+100;
	if ($nasaokorisnika > 0) {
		$q107 = myquery("SELECT ime, prezime FROM osoba WHERE id=$nasaokorisnika");
		$pretraga = mysql_result($q107,0,0)." ".mysql_result($q107,0,1);
	} else $pretraga = "";
	$nivo=1;
}


// Izbor nivoa logiranja (JavaScript)

?>
<h3>Pregled logova</h3>
<p>Izaberite logging nivo:<br/>
<?=genform("GET")?>
<table width="100%"><tr>
<td><input type="radio" name="nivo" value="1" onchange="document.forms[0].submit()" <? if ($nivo==1) print "CHECKED";?>><img src="images/16x16/log_info.png" width="16" height="16" align="center"> Posjete stranicama</td>
<td><input type="radio" name="nivo" value="2" onchange="document.forms[0].submit()" <? if ($nivo==2) print "CHECKED";?>><img src="images/16x16/log_edit.png" width="16" height="16" align="center"> Izmjene</td>
<td><input type="radio" name="nivo" value="3" onchange="document.forms[0].submit()" <? if ($nivo==3) print "CHECKED";?>><img src="images/16x16/log_error.png" width="16" height="16" align="center"> Greške</td>
<td><input type="radio" name="nivo" value="4" onchange="document.forms[0].submit()" <? if ($nivo==4) print "CHECKED";?>><img src="images/16x16/log_audit.png" width="16" height="16" align="center"> Kritične izmjene</td>
</tr></table>
</form>
<br/><br/>

<center>
<form action="index.php" method="GET">
<input type="hidden" name="sta" value="admin/log">
<input type="hidden" name="nivo" value="<?=$nivo?>">
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
		$q20 = myquery("select ime, prezime from osoba where id=$id");
		if (mysql_num_rows($q20)>0) {
			$link="?sta=studentska/osobe&akcija=edit&osoba=$id";
			$users[$id] = "<a href=\"$link\" target=\"_new\">".mysql_result($q20,0,0)." ".mysql_result($q20,0,1)."</a>";
		} else return $id;
	}
	return $users[$id];
}

function get_predmet_link($id) {
	static $aktuelna_ag = 0; // Aktuelna akademska godina
	if ($aktuelna_ag==0) {
		$q35 = myquery("select id from akademska_godina where aktuelna=1 order by id desc");
		$aktuelna_ag = mysql_result($q35,0,0);
	}

	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q30 = myquery("select p.id, p.naziv from ponudakursa as pk, predmet as p where pk.id=$id and pk.predmet=p.id");
		if (mysql_num_rows($q30)>0) {
			$predmeti[$id] = "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=".mysql_result($q30,0,0)."&ag=$aktuelna_ag\" target=\"_new\">".mysql_result($q30,0,1)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}


function get_ppredmet_link($id) {
	static $aktuelna_ag = 0; // Aktuelna akademska godina
	if ($aktuelna_ag==0) {
		$q35 = myquery("select id from akademska_godina where aktuelna=1 order by id desc");
		$aktuelna_ag = mysql_result($q35,0,0);
	}

	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q40 = myquery("select naziv from predmet where id=$id");
		if (mysql_num_rows($q40)>0) {
			$predmeti[$id] = "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$id&ag=$aktuelna_ag\" target=\"_new\">".mysql_result($q40,0,0)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}


// Glavni upit i petlja

$q10 = myquery ("select id, UNIX_TIMESTAMP(vrijeme), userid, dogadjaj, nivo from log where id<$stardate and ((nivo>=$nivo $filterupita) or dogadjaj='login') order by id desc");
//$q10 = myquery ("select id, UNIX_TIMESTAMP(vrijeme), userid, dogadjaj, nivo from log where id<$stardate and (nivo>=$nivo $filterupita) order by id desc");
$lastlogin = array();
$eventshtml = array();
$logins=0;
$prvidatum=$zadnjidatum=0;
$stardate=1;
while ($r10 = mysql_fetch_row($q10)) {
	
	if ($prvidatum==0) $prvidatum=$r10[1];
	$zadnjidatum=$r10[1];
	$nicedate = " (".date("d.m.Y. H:i:s", $r10[1]).")";
	$usr=$r10[2]; // ID korisnika
	$evt=$r10[3]; // string koji opisuje dogadjaj

	if ($rezultata==1 && preg_match("/u$nasaokorisnika\d/", $evt)) continue; // kada je ID korisnika kratak, moze se desiti da se javlja unutar eventa

	if (strlen($evt)>100) $evt = substr($evt,0,100);

	// ne prikazuj login ako je to jedina stavka, ako je nivo veci od 1 ili ako nema pretrage
	if ($lastlogin[$usr]==0 && (($nivo==1 && $pretraga=="") || $evt != "login")) { 
		$lastlogin[$usr]=$r10[0];
		$logins++;
		if ($logins > $maxlogins) {
			$stardate=$r10[0]+1;
			break; // izlaz iz while
		}
	}

	if ($r10[4]==1) $nivoimg="log_info";
	else if ($r10[4]==2) $nivoimg="log_edit";
	else if ($r10[4]==3) $nivoimg="log_error";
	else if ($r10[4]==4) $nivoimg="log_audit";


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
		$q39 = myquery("select naziv from labgrupa where id=$m[1]");
		if (mysql_num_rows($q39)>0) {
			$evt = str_replace("g$m[1]","<a href=\"?sta=saradnik/grupa&id=$m[1]\" target=\"_blank\">".mysql_result($q39,0,0)."</a>",$evt);
		} else {
			$evt = str_replace("g$m[1]","$m[1]",$evt);
		}
	}
	while (preg_match("/\Wc(\d+)/", $evt, $m)) { // cas
		$q40 = myquery("select labgrupa from cas where id=$m[1]");
		if (mysql_num_rows($q40)>0) {
			$link="?sta=saradnik/grupa&id=".mysql_result($q40,0,0);
			$evt = str_replace("c$m[1]","<a href=\"$link\" target=\"_blank\">$m[1]</a>",$evt);
		} else {
			$evt = str_replace("c$m[1]","$m[1]",$evt);
		}
	}
	if (preg_match("/\Wz(\d+)/", $evt, $m)) { // zadaca
		$q50 = myquery("select naziv,predmet,akademska_godina from zadaca where id=$m[1]");
		if (mysql_num_rows($q50)>0) {
			$naziv=mysql_result($q50,0,0);
			if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
			$predmet=mysql_result($q50,0,1);
			$ag=mysql_result($q50,0,2);
			if (intval($usr)>0) {
				$q55 = myquery("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$usr and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
				if (mysql_num_rows($q55)<1 && $zadnjikorisnik>0) {
					$q55 = myquery("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$zadnjikorisnik and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
				}
				if (mysql_num_rows($q55)<1) {
					$q55 = myquery("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
				}
				$link="?sta=saradnik/grupa&id=".mysql_result($q55,0,0);
				$evt = str_replace("z$m[1]","<a href=\"$link\" target=\"_blank\">$naziv</a>",$evt);
			}
		}
	}
	while (preg_match("/\Wi(\d+)/", $evt, $m)) { // ispit
		$q60 = myquery("select k.gui_naziv, i.predmet, p.naziv, i.akademska_godina from ispit as i, komponenta as k, predmet as p where i.id=$m[1] and i.komponenta=k.id and i.predmet=p.id");
		if (mysql_num_rows($q60)>0) {
			$naziv=mysql_result($q60,0,0);
			if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
			$predmet=mysql_result($q60,0,1);
			$predmetnaziv=mysql_result($q60,0,2);
			$ag=mysql_result($q60,0,3);
			$evt = str_replace("i$m[1]","<a href=\"?sta=nastavnik/ispiti&predmet=$predmet&ag=$ag\" target=\"_blank\">$naziv ($predmetnaziv)</a>",$evt);
		} else {
			$evt = str_replace("i$m[1]","$m[1]",$evt);
		}
	}
	while (preg_match("/\Wag(\d+)/", $evt, $m)) { // akademska godina
		$q70 = myquery("select naziv from akademska_godina where id=$m[1]");
		if (mysql_num_rows($q70)>0) {
			$naziv=mysql_result($q70,0,0);
			$evt = str_replace("ag$m[1]","$naziv",$evt);
		} else {
			$evt = str_replace("ag$m[1]","$m[1]",$evt);
		}
	}
	while (preg_match("/\Ws(\d+)/", $evt, $m)) { // studij
		$q80 = myquery("select naziv from studij where id=$m[1]");
		if (mysql_num_rows($q80)>0) {
			$naziv=mysql_result($q80,0,0);
			$evt = str_replace("s$m[1]","$naziv",$evt);
		} else {
			$evt = str_replace("s$m[1]","$m[1]",$evt);
		}
	}


	// Pošto idemo unazad, login predstavlja kraj zapisa za korisnika

	if ($evt == "login") {
		if ($lastlogin[$usr] && $lastlogin[$usr]!=0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> login (ID: $usr) $nicedate\n".$eventshtml[$lastlogin[$usr]];
			$lastlogin[$usr]=0;
		}
	}
	else if (strstr($evt," su=")) {
		$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> SU to ID: $usr $nicedate\n".$eventshtml[$lastlogin[$usr]];
		$lastlogin[$usr]=0;
	}


	else {
		$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> ".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
	}
}
if ($stardate==1) $zadnjidatum=1; // Nije doslo do breaka...


// Insertujem masovni unos ocjena i rezultata ispita
if ($rezultata==1) {
	// Konacne ocjene
	$q300 = myquery("select predmet, ocjena, UNIX_TIMESTAMP(datum) from konacna_ocjena where student=$nasaokorisnika AND datum>=FROM_UNIXTIME($zadnjidatum) AND datum<=FROM_UNIXTIME($prvidatum)");
	while ($r300 = mysql_fetch_row($q300)) {
		$predmet=$r300[0];
		$ocjena=$r300[1];
		$datum=$r300[2];
		$nicedate = " (".date("d.m.Y. H:i:s", $datum).")";

		// Prvo cemo varijantu sa predmetom pa sa ponudom kursa
		$q310 = myquery("select id from log where dogadjaj='masovno upisane ocjene na predmet pp$predmet' and vrijeme=FROM_UNIXTIME($datum)");
		if (mysql_num_rows($q310)>0) {
			$eventshtml[mysql_result($q310,0,0)] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/log_audit.png\" width=\"16\" height=\"16\" align=\"center\"> masovno upisane ocjene na predmet ".get_ppredmet_link($predmet)." (".get_user_link($nasaokorisnika)." dobio: $ocjena)".$nicedate."\n";
		} 

		$q320 = myquery("select pk.id from ponudakursa as pk, akademska_godina as ag where pk.predmet=$predmet and pk.akademska_godina=ag.id and ag.aktuelna=1");
		while ($r320 = mysql_fetch_row($q320)) {
			$q310 = myquery("select id from log where dogadjaj='masovno upisane ocjene na predmet p$r320[0]' and vrijeme=FROM_UNIXTIME($datum)");
			if (mysql_num_rows($q310)>0) {
				$eventshtml[mysql_result($q310,0,0)] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/log_audit.png\" width=\"16\" height=\"16\" align=\"center\"> masovno upisane ocjene na predmet ".get_ppredmet_link($predmet)." (".get_user_link($nasaokorisnika)." dobio: $ocjena)".$nicedate."\n";
			}
		}
	}


	// Isto ovo za ispite
	$q330 = myquery("select i.predmet, io.ocjena, UNIX_TIMESTAMP(i.vrijemeobjave) from ispit as i, ispitocjene as io where io.student=$nasaokorisnika AND io.ispit=i.id AND i.datum>=FROM_UNIXTIME($zadnjidatum) AND i.datum<=FROM_UNIXTIME($prvidatum)");
	while ($r330 = mysql_fetch_row($q330)) {
		$predmet=$r330[0];
		$ocjena=$r330[1];
		$datum=$r330[2]; // Datum je zaokruzen :(

		// Prvo cemo varijantu sa predmetom pa sa ponudom kursa
		$q340 = myquery("select id, vrijeme from log where dogadjaj='masovni rezultati ispita za predmet pp$predmet' and vrijeme=FROM_UNIXTIME($datum)");
		if (mysql_num_rows($q340)>0) {
			$nicedate = " (".date("d.m.Y. H:i:s", mysql_result($q340,0,1)).")";
			$eventshtml[mysql_result($q340,0,0)] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/log_audit.png\" width=\"16\" height=\"16\" align=\"center\"> masovni rezultati ispita za predmet ".get_ppredmet_link($predmet)." (".get_user_link($nasaokorisnika)." dobio: $ocjena)".$nicedate."\n";
		}

		$q320 = myquery("select pk.id from ponudakursa as pk, akademska_godina as ag where pk.predmet=$predmet and pk.akademska_godina=ag.id and ag.aktuelna=1");
		while ($r320 = mysql_fetch_row($q320)) {
			$q340 = myquery("select id, vrijeme from log where dogadjaj='masovni rezultati ispita za predmet p$r320[0]' and vrijeme=FROM_UNIXTIME($datum)");
			if (mysql_num_rows($q340)>0) {
				$nicedate = " (".date("d.m.Y. H:i:s", mysql_result($q340,0,1)).")";
				$eventshtml[mysql_result($q340,0,0)] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/log_audit.png\" width=\"16\" height=\"16\" align=\"center\"> masovni rezultati ispita za predmet ".get_ppredmet_link($predmet)." (".get_user_link($nasaokorisnika)." dobio: $ocjena)".$nicedate."\n";
			}
		}
	}
	krsort($eventshtml);
}


// Dodajemo zaglavlja sa [+] poljem (prebaciti iznad)

foreach ($eventshtml as $logid => $event) {
	if (substr($event,0,4)!="<img") {
		// Login počinje sa <br/>

		// TODO: optimizovati upite!

		$q201 = myquery("select userid, UNIX_TIMESTAMP(vrijeme) from log where id=$logid");
		$userid = intval(mysql_result($q201,0,0));
		$nicedate = " (".date("d.m.Y. H:i:s", mysql_result($q201,0,1)).")";

		if ($userid==0) {
			$imeprezime = "ANONIMNI PRISTUPI";
			$usrimg="zad_bug";

		} else {
			$q202 = myquery("select ime, prezime from osoba where id=$userid");
			$imeprezime = mysql_result($q202,0,0)." ".mysql_result($q202,0,1);

			$q203 = myquery("select count(*) from privilegije where osoba=$userid and privilegija='nastavnik'");
			$q204 = myquery("select count(*) from privilegije where osoba=$userid and privilegija='studentska'");
			$q205 = myquery("select count(*) from privilegije where osoba=$userid and privilegija='siteadmin'");

			if (mysql_result($q205,0,0)>0) {
				$usrimg="admin"; 
			} else if (mysql_result($q204,0,0)>0) {
				$usrimg="teta"; 
			} else if (mysql_result($q203,0,0)>0) {
				$usrimg="tutor"; 
			} else {
				$usrimg="user";
			}
		}
	
		$link = "?sta=studentska/osobe&akcija=edit&osoba=$userid";

		print "<img src=\"images/plus.png\" width=\"13\" height=\"13\" id=\"img-$logid\" onclick=\"daj_stablo('$logid')\">
<img src=\"images/16x16/$usrimg.png\" width=\"16\" height=\"16\" align=\"center\">
<a href=\"$link\">$imeprezime</a> $nicedate
<div id=\"$logid\" style=\"display:none\">\n";
	}

	print "$event</div><br/>\n";
}
print "<p>&nbsp;</p><p><a href=\"".genuri()."&stardate=$stardate\">Sljedećih $maxlogins</a></p>";



}

?>