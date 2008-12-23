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
if ($nivo<1) $nivo=1;
if ($nivo>4) $nivo=4;



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
		$q100 = myquery("select id from osoba as o, auth as a where ($query) and a.id=o.id");
		$rezultata = mysql_num_rows($q100);
	}

	if ($rezultata>0) {
		while ($r100 = mysql_fetch_row($q100)) {
			if ($filterupita!="") $filterupita .= " OR ";
			$filterupita .= "userid=$r100[0] OR dogadjaj like '%u$r100[0]%'";
		}
	}

	// Probavamo predmete
	$q101 = myquery("select pk.id from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.predmet=p.id and (p.naziv like '%$src%' or p.kratki_naziv='$src') and pk.akademska_godina=ag.id and ag.aktuelna=1");
	if (mysql_num_rows($q101)>0) {
		if ($filterupita!="") $filterupita .= " OR ";
		$filterupita .= "dogadjaj like '%p$r101%'";
	}

	// Kraj, dodajemo and
	if ($filterupita!="") $filterupita = " AND ($filterupita)";
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


<script language="JavaScript">
	function toggleVisibility(ime){
		var me = document.getElementById(ime);
		var img = document.getElementById('img-'+ime);
		if (me.style.display=="none"){
			me.style.display="inline";
			img.src="images/minus.png";
		}
		else {
			me.style.display="none";
			img.src="images/plus.png";
		}
	}
</script>
<?



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
	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q30 = myquery("select p.naziv from ponudakursa as pk, predmet as p where pk.id=$id and pk.predmet=p.id");
		if (mysql_num_rows($q30)>0) {
			$predmeti[$id] = "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$id\" target=\"_new\">".mysql_result($q30,0,0)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}



// Glavni upit i petlja


$q10 = myquery ("select id, UNIX_TIMESTAMP(vrijeme), userid, dogadjaj, nivo from log where id<$stardate and ((nivo>=$nivo $filterupita) or dogadjaj='login') order by id desc");
$lastlogin = array();
$eventshtml = array();
$logins=0;
while ($r10 = mysql_fetch_row($q10)) {
	
	$nicedate = " (".date("d.m.Y. H:i:s", $r10[1]).")";
	$usr=$r10[2]; // ID korisnika
	$evt=$r10[3]; // string koji opisuje dogadjaj
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
	//   pID - predmet
	//   cID - čas
	//   zID - zadaća
	//   iID - ispit
	//   agID - akademska godina
	//   sID - studij

	if (preg_match("/\Wu(\d+)/", $evt, $m)) { // korisnik
		$evt = str_replace("u$m[1]",get_user_link($m[1]), $evt);
	}
	if (preg_match("/\Wp(\d+)/", $evt, $m)) { // predmet
		$evt = str_replace("p$m[1]",get_predmet_link($m[1]),$evt);
	}
	if (preg_match("/\Wc(\d+)/", $evt, $m)) { // cas
		$q40 = myquery("select labgrupa, predmet from cas where id=$m[1]");
		if (mysql_num_rows($q40)>0) {
			if (mysql_result($q40,0,0)==0) {
				$link="?sta=saradnik/grupa&id=0&predmet=".mysql_result($q40,0,1);
			} else {
				$link="?sta=saradnik/grupa&id=".mysql_result($q40,0,0);
			}
			$evt = str_replace("c$m[1]","<a href=\"$link\" target=\"_blank\">$m[1]</a>",$evt);
		}
	}
	if (preg_match("/\Wz(\d+)/", $evt, $m)) { // zadaca
		$q50 = myquery("select naziv,predmet from zadaca where id=$m[1]");
		if (mysql_num_rows($q50)>0) {
			$naziv=mysql_result($q50,0,0);
			if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
			$predmet=mysql_result($q50,0,1);
			$q60 = myquery("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$usr and sl.labgrupa=l.id and l.predmet=$predmet");
			if (mysql_num_rows($q60)>0) {
				$link="?sta=saradnik/grupa&id=".mysql_result($q60,0,0);
			} else {
				$link="?sta=saradnik/grupa&id=0&predmet=$predmet";
			}
			$evt = str_replace("z$m[1]","<a href=\"$link\" target=\"_blank\">$naziv</a>",$evt);
		}
	}
	if (preg_match("/\Wi(\d+)/", $evt, $m)) { // ispit
		$q60 = myquery("select k.gui_naziv,i.predmet,p.naziv from ispit as i, komponenta as k, ponudakursa as pk, predmet as p where i.id=$m[1] and i.komponenta=k.id and i.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q60)>0) {
			$naziv=mysql_result($q60,0,0);
			if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
			$predmet=mysql_result($q60,0,1);
			$predmetnaziv=mysql_result($q60,0,2);
			$evt = str_replace("i$m[1]","<a href=\"?sta=nastavnik/ispiti&predmet=$predmet\" target=\"_blank\">$naziv ($predmetnaziv)</a>",$evt);
		}
	}
	if (preg_match("/\Wag(\d+)/", $evt, $m)) { // akademska godina
		$q70 = myquery("select naziv from akademska_godina where id=$m[1]");
		if (mysql_num_rows($q70)>0) {
			$naziv=mysql_result($q70,0,0);
			$evt = str_replace("ag$m[1]","$naziv",$evt);
		}
	}
	if (preg_match("/\Ws(\d+)/", $evt, $m)) { // studij
		$q80 = myquery("select naziv from studij where id=$m[1]");
		if (mysql_num_rows($q80)>0) {
			$naziv=mysql_result($q80,0,0);
			$evt = str_replace("s$m[1]","$naziv",$evt);
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


	// Legacy parser loga... (brisati!)


	else if (preg_match("/Admin grupa (\d+)/", $evt, $matches)) {
		$q203 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p, ponudakursa as pk where l.id=$matches[1] and l.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q203)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Predmet ".mysql_result($q203,0,0).", labgrupa ".mysql_result($q203,0,1).$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	} 
	else if (preg_match("/Poslana zadaca (\d+)-(\d+)/", $evt, $matches)) {
		$q204 = myquery("select p.naziv, z.naziv from zadaca as z, predmet as p, ponudakursa as pk where z.id=$matches[1] and z.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q204)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> Poslana zadaća ".mysql_result($q204,0,1)." / $matches[2] (predmet ".mysql_result($q204,0,0).")".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> ".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else if (preg_match("/Admin Predmet (\d+) - (.+)/", $evt, $matches)) {
		$q205 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$matches[1] and pk.predmet=p.id");
		if (mysql_num_rows($q205)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Admin Predmet ".mysql_result($q205,0,0)." - $matches[2]".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else if (preg_match("/Registrovan cas \d+ za labgrupu (\d+)/", $evt, $matches)) {
		$q203 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p, ponudakursa as pk where l.id=$matches[1] and l.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q203)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Registrovan čas za labgrupu ".mysql_result($q203,0,1)." (predmet ".mysql_result($q203,0,0).")".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else if (preg_match("/Izmjena ličnih podataka studenta (\d+)/", $evt, $matches)) {
		$q206 = myquery("select ime, prezime from student where id=$matches[1]");
		if (mysql_num_rows($q206)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Izmjena ličnih podataka studenta ".mysql_result($q206,0,0)." ".mysql_result($q206,0,1).$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else if (preg_match("/Izvrsena zadaca \((\d+),(\d+),(\d+)\)/", $evt, $matches)) {
		$q204 = myquery("select z.naziv from zadaca as z, predmet as p, ponudakursa as pk where z.id=$matches[1] and z.predmet=pk.id and pk.predmet=p.id");
		$q206 = myquery("select ime, prezime from student where id=$matches[3]");
		if (mysql_num_rows($q204)>0 && mysql_num_rows($q206)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Izvršena zadaća ".mysql_result($q204,0,0)." / $matches[2] (student ".mysql_result($q206,0,0)." ".mysql_result($q206,0,1).")".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else if (preg_match("/Izmjena statusa zadace \((\d+),(\d+),(\d+)\)/", $evt, $matches)) {
		$q204 = myquery("select z.naziv from zadaca as z, predmet as p, ponudakursa as pk where z.id=$matches[1] and z.predmet=pk.id and pk.predmet=p.id");
		$q206 = myquery("select ime, prezime from student where id=$matches[3]");
		if (mysql_num_rows($q204)>0 && mysql_num_rows($q206)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Izmjena statusa zadaće ".mysql_result($q204,0,0)." / $matches[2] (student ".mysql_result($q206,0,0)." ".mysql_result($q206,0,1).")".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else if (preg_match("/Nastavnik (\d+) dodan na predmet (\d+)/", $evt, $matches)) {
		$q205 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$matches[2] and pk.predmet=p.id");
		$q207 = myquery("select ime,prezime from nastavnik where id=$matches[1]");
		if (mysql_num_rows($q205)>0 && mysql_num_rows($q207)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Nastavnik '".mysql_result($q207,0,0)." ".mysql_result($q207,0,1)."' dodan na predmet '".mysql_result($q205,0,0)."' ".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}

	}
	else if (preg_match("/Nastavnik (\d+) proglasen za admina predmeta (\d+)/", $evt, $matches)) {
		$q205 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$matches[2] and pk.predmet=p.id");
		$q207 = myquery("select ime,prezime from nastavnik where id=$matches[1]");
		if (mysql_num_rows($q205)>0 && mysql_num_rows($q207)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Nastavnik '".mysql_result($q207,0,0)." ".mysql_result($q207,0,1)."' proglašen za admina predmeta '".mysql_result($q205,0,0)."' ".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}

	}
	else if (preg_match("/Student labgrupa (\d+)/", $evt, $matches)) {
		$q203 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p, ponudakursa as pk where l.id=$matches[1] and l.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q203)>0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">Statusna stranica, predmet ".mysql_result($q203,0,0).", labgrupa ".mysql_result($q203,0,1)." ".$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		} else {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\">".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
		}
	}
	else {
		$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> ".$evt.$nicedate."\n".$eventshtml[$lastlogin[$usr]];
	}
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
			$usrimg="";

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

		print "<img src=\"images/plus.png\" width=\"13\" height=\"13\" id=\"img-$logid\" onclick=\"toggleVisibility('$logid')\">
<img src=\"images/16x16/$usrimg.png\" width=\"16\" height=\"16\" align=\"center\">
<a href=\"$link\">$imeprezime</a> $nicedate
<div id=\"$logid\" style=\"display:none\">\n";
	}

	print "$event</div><br/>\n";
}
print "<p>&nbsp;</p><p><a href=\"".genuri()."&stardate=$stardate\">Sljedećih $maxlogins</a></p>";



}

?>