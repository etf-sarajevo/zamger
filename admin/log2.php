<?

// ADMIN/LOG2 - pregled logova


function admin_log2() {

global $userid;

global $_lv_; // We use form generators


// LOG v2.0




$maxlogins = 20;
$stardate = intval($_GET['stardate']);
if ($stardate == 0) {
	$q199 = myquery("select id from log order by id desc limit 1");
	$stardate = mysql_result($q199,0,0)+1;
}
$nivo = intval($_GET['nivo']);
if ($nivo<1) $nivo=2;
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

function get_pk_link($id) {
	static $predmeti = array();
	if (!$predmeti[$id]) {
		$q30 = myquery("select p.id, p.naziv, pk.akademska_godina from ponudakursa as pk, predmet as p where pk.id=$id and pk.predmet=p.id");
		if (mysql_num_rows($q30)>0) {
			$predmeti[$id] = "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=".mysql_result($q30,0,0)."&ag=".mysql_result($q30,0,2)."\" target=\"_new\">".mysql_result($q30,0,1)."</a>";
		} else return $id;
	}
	return $predmeti[$id];
}

function get_predmet_link($id) {
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

function get_predmet_ag_link($predmet, $ag) {
	static $godine = array();
	if (!$godine[$ag]) {
		$q50 = myquery("select naziv from akademska_godina where id=$ag");
		if (mysql_num_rows($q50)>0) {
			$godine[$ag] = mysql_result($q50,0,0);
		} else return "$predmet, $ag";
	}

	static $predmeti = array();
	if (!$predmeti[$predmet]) {
		$q40 = myquery("select naziv from predmet where id=$predmet");
		if (mysql_num_rows($q40)>0) {
			$predmeti[$predmet] = mysql_result($q40,0,0);
		} else return "$predmet, $ag";
	}
	return "<a href=\"?sta=studentska/predmeti&akcija=edit&predmet=$predmet&ag=$ag\" target=\"_new\">".$predmeti[$predmet]." ".$godine[$ag]."</a>";
}

function get_zadaca_link($id, $usr) {
	$q50 = myquery("select z.naziv,z.predmet,z.akademska_godina, p.naziv from zadaca as z, predmet as p where z.id=$id and z.predmet=p.id");
	if (mysql_num_rows($q50)>0) {
		$naziv=mysql_result($q50,0,0);
		if (!preg_match("/\w/",$naziv)) $naziv="[Bez imena]";
		$predmet=mysql_result($q50,0,1);
		$ag=mysql_result($q50,0,2);
		$pnaziv=mysql_result($q50,0,3);
		if (intval($usr)>0) {
			$q55 = myquery("select l.id from student_labgrupa as sl, labgrupa as l where sl.student=$usr and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag order by virtualna");
			if (mysql_num_rows($q55)>0)
				$link="?sta=saradnik/grupa&id=".mysql_result($q55,0,0);
			else
				$link="?sta=nastavnik/zadace&predmet=$predmet&ag=$ag";
			return "<a href=\"$link\" target=\"_blank\">$naziv ($pnaziv)</a>";
		}
	}
	return "$id";
}

function get_cas_link($id) {
	$q70 = myquery("select l.id, p.naziv, l.naziv from cas as c, labgrupa as l, predmet as p where c.id=$id and c.labgrupa=l.id and l.predmet=p.id");
	if (mysql_num_rows($q70)>0) {
		$link = "?sta=saradnik/grupa&id=".mysql_result($q70,0,0);
		$tekst = mysql_result($q70,0,2)." (".mysql_result($q70,0,1).")";
		return "<a href=\"$link\" target=\"_blank\">$tekst</a>";
	}
	return "$id";
}

function get_ispit_link($id) {
	static $ispiti = array();
	if (!$ispiti[$id]) {
		$q60 = myquery("select p.naziv, k.gui_naziv, i.predmet, i.akademska_godina from ispit as i, predmet as p, komponenta as k where i.id=$id and i.predmet=p.id and i.komponenta=k.id");
		if (mysql_num_rows($q60)>0) {
			$link = "?sta=nastavnik/ispiti&predmet=".mysql_result($q60,0,2)."&ag=".mysql_result($q60,0,3);
			$tekst = mysql_result($q60,0,1)." (".mysql_result($q60,0,0).")";
			$ispiti[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $ispiti[$id];
}

function get_termin($id) {
	static $termini = array();
	if (!$termini[$id]) {
		$q60 = myquery("select p.naziv, k.gui_naziv, i.predmet, i.akademska_godina, UNIX_TIMESTAMP(it.datumvrijeme) from ispit as i, predmet as p, komponenta as k, ispit_termin as it where it.id=$id and it.ispit=i.id and i.predmet=p.id and i.komponenta=k.id");
		if (mysql_num_rows($q60)>0) {
			$link = "?sta=nastavnik/ispiti&predmet=".mysql_result($q60,0,2)."&ag=".mysql_result($q60,0,3);
			$tekst = mysql_result($q60,0,1).", ".date("j.n. H:i",mysql_result($q60,0,4))." (".mysql_result($q60,0,0).")";
			$termini[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $termini[$id];
}

function get_grupa_link($id) {
	static $grupe = array();
	if (!$grupe[$id]) {
		$q80 = myquery("select p.naziv, l.naziv from labgrupa as l, predmet as p where l.id=$id and l.predmet=p.id");
		if (mysql_num_rows($q80)>0) {
			$link = "?sta=saradnik/grupa&id=$id";
			$tekst = mysql_result($q80,0,1)." (".mysql_result($q80,0,0).")";
			$grupe[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $grupe[$id];
}

function get_komp_link($id) {
	static $komponente = array();
	if (!$komponente[$id]) {
		$q70 = myquery("select gui_naziv from komponenta where id=$id");
		if (mysql_num_rows($q70)>0) {
			$komponente[$id] = mysql_result($q70,0,0);
		}
		else return "$id";
	}
	return $komponente[$id];
}

function get_projekat_link($id) {
	static $projekti = array();
	if (!$projekti[$id]) {
		$q90 = myquery("select p.naziv, p2.naziv, p2.id, p.akademska_godina from projekat as p, predmet as p2 where p.id=$id and p.predmet=p2.id");
		if (mysql_num_rows($q90)>0) {
			$link = "?sta=nastavnik/projekti&predmet=".mysql_result($q90,0,2)."&ag=".mysql_result($q90,0,3)."&akcija=projektna_stranica&projekat=$id";
			$tekst = mysql_result($q90,0,0)." (".mysql_result($q90,0,1).")";
			$projekti[$id] = "<a href=\"$link\" target=\"_blank\">$tekst</a>";
		}
		else return "$id";
	}
	return $projekti[$id];
}

function get_studij($id) {
	static $studiji = array();
	if (!$studiji[$id]) {
		$q100 = myquery("select naziv from studij where id=$id");
		$studiji[$id] = mysql_result($q100,0,0);
	}
	return $studiji[$id];
}

function get_ag($id) {
	static $ags = array();
	if (!$ags[$id]) {
		$q110 = myquery("select naziv from akademska_godina where id=$id");
		$ags[$id] = mysql_result($q110,0,0);
	}
	return $ags[$id];
}

function add_string($s1, $s2, $s3) {
	if ($s1=="") return $s3;
	return $s1.$s2.$s3;
}

// Glavni upit i petlja

$q10 = myquery ("SELECT l.id, UNIX_TIMESTAMP(l.vrijeme), l.userid, lm.naziv, l.dogadjaj, ld.opis, ld.nivo, l.objekat1, l.objekat2, l.objekat3 
FROM log2 AS l, log2_dogadjaj AS ld, log2_modul AS lm 
WHERE l.modul=lm.id AND l.dogadjaj=ld.id AND l.id<$stardate and ((ld.nivo>=$nivo $filterupita) or ld.opis='login') 
ORDER BY l.id DESC");
$lastlogin = array();
$eventshtml = array();
$logins=0;
$prvidatum=$zadnjidatum=0;
$stardate=1;
while ($r10 = mysql_fetch_row($q10)) {
	
	if ($prvidatum==0) $prvidatum = $r10[1];
	$zadnjidatum = $r10[1];
	$nicedate = " (".date("d.m.Y. H:i:s", $r10[1]).")";
	$usr = $r10[2]; // ID korisnika
	$modul = $r10[3];
	$evt_id = $r10[4];
	$opis = $r10[5]; // string koji opisuje dogadjaj

	// ne prikazuj login ako je to jedina stavka, ako je nivo veci od 1 ili ako nema pretrage
	if ($lastlogin[$usr]==0 && (($nivo==1 && $pretraga=="") || $opis != "login")) { 
		$lastlogin[$usr]=$r10[0];
		$logins++;
		if ($logins > $maxlogins) {
			$stardate=$r10[0]+1;
			break; // izlaz iz while
		}
	}

	if ($r10[6]==1) $nivoimg="log_info";
	else if ($r10[6]==2) $nivoimg="log_edit";
	else if ($r10[6]==3) $nivoimg="log_error";
	else if ($r10[6]==4) $nivoimg="log_audit";


	$evt = "";
	if ($modul != "") $evt .= "$modul: ";
	$evt .= "$opis";
	$objekti = "";


	// Log transformacije opisa
	if (substr($opis,0,14) == "poslana zadaca" || substr($opis,0,24) == "greska pri slanju zadace" || $opis == "poslao praznu zadacu" || $opis == "ne postoji fajl za zadacu" || $opis == "zadaca nema toliko zadataka") {
		$objekti = get_zadaca_link($r10[7], $usr).", zadatak $r10[8]";

	} else if ($opis == "isteklo vrijeme za slanje zadace" || $opis == "pogresan tip datoteke" || $opis == "student ne slusa predmet za zadacu" || $opis == "nije nastavnik na predmetu za zadacu" || $opis == "ogranicenje na predmet za zadacu" || $opis == "postavka ne postoji" || $opis == "obrisana postavka zadace" || $opis == "smanjen broj zadataka u zadaci" || $opis == "azurirana zadaca" || $opis == "niko nije poslao zadacu" || $opis == "kreiranje arhive zadaca nije uspjelo") {
		$objekti = get_zadaca_link($r10[7], $usr);

	} else if ($opis == "ne postoji attachment" || $opis == "bodovanje zadace" || $opis == "autotestiran student") {
		$objekti = get_user_link($r10[7]).", ".get_zadaca_link($r10[8], $r10[7]).", zadatak $r10[9]";

	} else if ($opis == "prisustvo azurirano") {
		$objekti = get_user_link($r10[7]).", ".get_cas_link($r10[8]).", prisustvo: $r10[9]";

	} else if ($opis == "prisustvo - nije nastavnik na predmetu" || $opis == "prisustvo - ima ogranicenje za grupu" || $opis == "registrovan cas") {
		$objekti = get_cas_link($r10[7]);

	} else if ($opis == "student ne slusa predmet" || $opis == "ne postoji moodle ID za predmet" || substr($opis,0,25) == "nije saradnik na predmetu" || $opis == "svi projekti su jos otkljucani" || $opis == "nije nastavnik na predmetu" || $opis == "predmet nema virtuelnu grupu" || $opis == "nije definisan tip predmeta" || substr($opis,0,22) == "dosegnut limit za broj projekata" || substr($opis,0,19) == "projekti zakljucani" || $opis == "nije ni na jednom projektu (odjava)" || $opis == "prekopirane labgrupe" || $opis == "izmijenjeni parametri projekata na predmetu" || $opis == "kreiran tip predmeta") {
		$objekti = get_predmet_ag_link($r10[7],$r10[8]);

	} else if ($opis == "ne postoji komponenta za zadace" || $opis == "promijenjen tip predmeta" || $opis == "nije definisana komponenta za prisustvo" || $opis == "nepostojeca virtualna labgrupa" || $opis == "nije ponudjen predmet") {
		$objekti = get_predmet_ag_link($r10[7],$r10[8]);

	} else if (substr($opis,0,17) == "obrisana labgrupa") {
		$objekti = "$r10[9], ".get_predmet_ag_link($r10[7],$r10[8]);

	} else if ($opis == "dodana ocjena" || $opis == "obrisana ocjena" || $opis == "izmjena ocjene" || $opis == "promijenjen datum ocjene" || substr($opis,0,27) ==  "student ispisan sa predmeta" || $opis == "nastavniku data prava na predmetu" || $opis == "nastavnik angazovan na predmetu" || $opis == "nastavniku oduzeta prava na predmetu" || $opis == "nastavnik deangazovan sa predmeta") {
		$objekti = get_user_link($r10[7]).", ".get_predmet_ag_link($r10[8], $r10[9]);

	} else if ($opis == "nijedna zadaca nije aktivna" || $opis == "popunjena anketa" || $opis == "odabrana tema za zadacu" || $opis == "ponisten datum za izvoz") {
		$objekti = get_predmet_link($r10[7]);

	} else if ($opis == "student ne slusa ponudukursa") {
		$objekti = get_pk_link($r10[7]);

	} else if ($opis == "kreirao ponudu kursa zbog studenta" || substr($opis,0,25) == "student upisan na predmet") {
		$objekti = get_user_link($r10[7]).", ".get_pk_link($r10[8]);

	} else if ($opis == "upisan rezultat ispita" || $opis == "izbrisan rezultat ispita" || $opis == "izmjenjen rezultat ispita" || $opis == "ispit - vrijednost > max") {
		$objekti = get_user_link($r10[7]).", ".get_ispit_link($r10[8]);

	} else if ($opis == "promijenjen tip ispita" || $opis == "promijenjen datum ispita") {
		$objekti = get_ispit_link($r10[7]);

	} else if ($opis == "kreiran novi ispit") {
		$objekti = get_ispit_link($r10[7]).", ".get_predmet_ag_link($r10[8], $r10[9]);

	} else if ($opis == "prijavljen na termin" || $opis == "odjavljen sa termina" || $opis == "izmijenjen ispitni termin") {
		$objekti = get_termin($r10[7]);

	} else if ($opis == "kreiran novi ispitni termin") {
		$objekti = get_termin($r10[7]); //.", ".get_predmet_ag_link($r10[8], $r10[9]); - sadržano u prethodnom linku

	} else if ($opis == "izmjena bodova za fiksnu komponentu") {
		$objekti = get_user_link($r10[7]).", ".get_pk_link($r10[8]).", ".get_komp_link($r10[9]);

	} else if ($opis == "nije na projektu" || $opis == "dodao link na projektu" || $opis == "uredio link na projektu" || $opis == "obrisao link na projektu" || $opis == "dodao rss feed na projektu" || $opis == "uredio rss feed na projektu" || $opis == "obrisao rss feed na projektu" || $opis == "dodao clanak na projektu" || $opis == "uredio clanak na projektu" || $opis == "obrisao clanak na projektu" || $opis == "dodao fajl na projektu" || $opis == "uredio fajl na projektu" || $opis == "obrisao fajl na projektu" || $opis == "dodao temu na projektu" || $opis == "obrisao post na projektu" || substr($opis, 0, 18) == "projekat zakljucan" || substr($opis, 0, 17) == "projekat popunjen" || $opis == "dodao projekat na predmetu" || $opis == "izmijenio projekat" || $opis == "dodao biljesku na projekat") {
		$objekti = get_projekat_link($r10[7]);
//		$objekti = $r10[7];

	} else if ($opis == "student prijavljen na projekat" || $opis == "student prebacen na projekat" || $opis == "student odjavljen sa projekta") {
		$objekti = get_user_link($r10[7]).", ".get_projekat_link($r10[8]);

	} else if ($opis == "poslana poruka" || $opis == "osoba nema sliku" || $opis == "nema datoteke za sliku" || $opis == "nepoznat tip slike" || $opis == "citanje fajla za sliku nije uspjelo" || $opis == "nije studentska, a pristupa tudjem izvjestaju" || $opis == "korisnik nikada nije studirao" || $opis == "prihvacen zahtjev za promjenu podataka" || $opis == "odbijen zahtjev za promjenu podataka" || $opis == "korisnik vec postoji u bazi" || $opis == "dodan novi korisnik" || $opis == "promijenjeni licni podaci korisnika" || $opis == "postavljena slika za korisnika" || $opis == "obrisana slika za korisnika" || $opis == "proglasen za studenta") {
		$objekti = get_user_link($r10[7]);

	} else if ($opis == "postavljen broj indeksa" || $opis == "prihvacen zahtjev za koliziju" || $opis == "dodani podaci o izboru" || $opis == "azurirani podaci o izboru" || $opis == "promijenjen email za korisnika" || $opis == "izmjena kandidata za prijemni" || $opis == "novi kandidat za prijemni") {
		$objekti = get_user_link($r10[7]);

	} else if ($opis == "greska prilikom slanja fajla na zavrsni" || $opis == "dodao fajl na zavrsni" || $opis == "azuriran sazetak zavrsnog rada" || $opis == "izmijenio temu zavrsnog rada" || $opis == "dodao biljesku na zavrsni rad" || $opis == "dodana tema zavrsnog rada") {
//		$objekti = get_zavrsni_link($r10[7]);
		$objekti = $r10[7];

	} else if (substr($opis,0,24) == "student ispisan sa grupe" || substr($opis,0,22) == "student upisan u grupu" || $opis == "dodan komentar na studenta" || $opis == "promijenjena grupa studenta") {
		$objekti = get_user_link($r10[7]).", ".get_grupa_link($r10[8]);

	} else if ($opis == "preimenovana labgrupa" || $opis == "ima ogranicenje na labgrupu") {
		$objekti = get_grupa_link($r10[7]);

	} else if (substr($opis, 0, 17) == "kreirana labgrupa") {
		$objekti = get_grupa_link($r10[7]).", ".get_predmet_ag_link($r10[8],$r10[9]);

	} else if ($opis == "student upisan na studij" || $opis == "pokusao ispisati studenta sa studija koji ne slusa") {
		$objekti = get_user_link($r10[7]).", ".get_studij($r10[8])." ".get_ag($r10[9]);

	} else {
		// Kreiranje log zapisa
		if ($r10[7]>0) $objekti = add_string($objekti, ", ", $r10[7]);
		if ($r10[8]>0) $objekti = add_string($objekti, ", ", $r10[8]);
		if ($r10[9]>0) $objekti = add_string($objekti, ", ", $r10[9]);
	}

	$q20 = myquery("SELECT tekst FROM log2_blob WHERE log2=$r10[0]");
	if (mysql_num_rows($q20)>0) 
		$objekti = add_string($objekti, ", ", mysql_result($q20,0,0));
	if ($objekti !== "") $evt .= " ($objekti)";

	$analyze_link = "<a href=\"?sta=admin/log&analyze=$r10[0]\">*</a>";

	/*
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
	}*/


	// Pošto idemo unazad, login predstavlja kraj zapisa za korisnika

	if ($opis == "login") {
		if ($lastlogin[$usr] && $lastlogin[$usr]!=0) {
			$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> login (ID: $usr) $nicedate $analyze_link\n".$eventshtml[$lastlogin[$usr]];
			$lastlogin[$usr]=0;
		}
	}
	else if (strstr($evt," su=")) {
		$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> SU to ID: $usr $nicedate $analyze_link\n".$eventshtml[$lastlogin[$usr]];
		$lastlogin[$usr]=0;
	}


	else {
		$eventshtml[$lastlogin[$usr]] = "<br/><img src=\"images/fnord.gif\" width=\"37\" height=\"1\"> <img src=\"images/16x16/$nivoimg.png\" width=\"16\" height=\"16\" align=\"center\"> ".$evt.$nicedate." ".$analyze_link."\n".$eventshtml[$lastlogin[$usr]];
	}
}
if ($stardate==1) $zadnjidatum=1; // Nije doslo do breaka...

/*
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
*/

// Dodajemo zaglavlja sa [+] poljem (prebaciti iznad)

foreach ($eventshtml as $logid => $event) {
	if (substr($event,0,4)!="<img") {
		// Login počinje sa <br/>

		// TODO: optimizovati upite!

		$q201 = myquery("select userid, UNIX_TIMESTAMP(vrijeme) from log2 where id=".intval($logid));
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
