<?

// NASTAVNIK/ISPITI - kreiranje i unos rezultata ispita

// v3.9.1.0 (2008/02/18) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/02/28) + Koristim lib/manip
// v3.9.1.2 (2008/04/09) + Dozvoljeno kreiranje praznog ispita; dodan update komponente u masovni unos
// v3.9.1.3 (2008/04/25) + Popravljeno prosljedjivanje parametra $ispis funkciji mass_input
// v3.9.1.4 (2008/05/16) + Ponovo ukljucen update komponente (bio iskomentiran zbog sporosti)
// v3.9.1.5 (2008/08/27) + Dodana zastita od visestrukog slanja kod masovnog unosa
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/04/14) + Zaboravio izbaciti polje naziv iz tabele ispit
// v4.0.9.4 (2009/04/15) + Popravljena redirekcija nakon masovnog unosa i logging


function nastavnik_ispiti() {

global $userid,$user_siteadmin;

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe



$ponudakursa=intval($_REQUEST['predmet']);
if ($ponudakursa==0) { 
	zamgerlog("ilegalan predmet $ponudakursa",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

$q1 = myquery("select p.naziv, p.id, pk.akademska_godina from predmet as p, ponudakursa as pk where pk.id=$ponudakursa and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);
$predmet = mysql_result($q1,0,1);
$ag = mysql_result($q1,0,2);

//$tab=$_REQUEST['tab'];
//if ($tab=="") $tab="Opcije";

//logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/ispiti privilegije (predmet p$ponudakursa)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Ispiti</h3></p>

<?


// Masovni unos rezultata ispita

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1 && check_csrf_token()) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

// Dozvoljavamo kreiranje blank ispita

//	if (count($mass_rezultat)==0) {
//		zamgerlog("parsiranje kod masovnog upisa nije vratilo ništa (predmet $predmet)",3);
//		niceerror("Niste unijeli ništa.");
//		return;
//	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<?
	}

	// Registrovati ispit u bazi

	$naziv = my_escape($_POST['naziv']);
	$dan = intval($_POST['day']);
	$mjesec = intval($_POST['month']);
	$godina = intval($_POST['year']);
	$mdat = mktime(0,0,0,$mjesec,$dan,$godina);

	$tipispita = intval($_POST['tipispita']);

	// Da li je ispit vec registrovan?
	$q10 = myquery("select id from ispit where predmet=$predmet and datum=FROM_UNIXTIME('$mdat') and komponenta=$tipispita");
	if (mysql_num_rows($q10)>0) {
		$ispit = mysql_result($q10,0,0);
		if ($ispis) {
			print "Dodati rezultate na postojeći ispit (ID: $ispit):<br/>";
		}
		$dodavanje=1;
	} else if (!$ispis) {
		$q20 = myquery("insert into ispit set predmet=$predmet, akademska_godina=$ag, datum=FROM_UNIXTIME('$mdat'), komponenta=$tipispita");
		$q30 = myquery("select id from ispit where predmet=$predmet and akademska_godina=$ag and datum=FROM_UNIXTIME('$mdat') and komponenta=$tipispita");

		if (mysql_num_rows($q30)<1) {
			zamgerlog("unos ispita nije uspio (predmet p$ponudakursa, ag $ag, datum $mdat, tipispita $tipispita)",3);
			niceerror("Unos ispita nije uspio.");
			return;
		} 
		$ispit = mysql_result($q30,0,0);
		$dodavanje=0;
	}


	// Obrada rezultata

	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$bodova = $mass_rezultat['podatak1'][$student];

		// pretvori bodove u float uz obradu decimalnog zareza
		$fbodova = floatval(str_replace(",",".",$bodova));
		// samo 0 priznajemo za nula bodova, inace student nije izasao na ispit
		if ($fbodova==0 && strpos($bodova,"0")===FALSE) {
			if ($ispis)
				print "Student '$prezime $ime' - nije izašao na ispit (nije unesen broj bodova $bodova)<br/>";
			continue;
		}
		$bodova = $fbodova;


		// Da li je ocjena za studenta vec ranije unesena?
		if ($dodavanje == 1) {
			$q40 = myquery("select ocjena from ispitocjene where ispit=$ispit and student=$student");
			if (mysql_num_rows($q40)>0) {
				if ($ispis) {
					$oc2 = mysql_result($q40,0,0);
					print "-- GREŠKA! Student '$prezime $ime' je već ranije unesen na ovaj ispit sa $oc2 bodova (a sada sa $fbodova bodova). Izmjena unesenih bodova trenutno nije moguća.<br/>";
				}
				$greska=1;
			}
		}
		if ($ispis) {
//			print "Student '$prezime $ime' (ID: $student) - bodova: $bodova<br/>";
			print "Student '$prezime $ime' - bodova: $bodova<br/>";
		} else {
			$q50 = myquery("insert into ispitocjene set ispit=$ispit, student=$student, ocjena=$bodova");
			// Update komponenti
			update_komponente($student, $ponudakursa, $tipispita);
		}
	}

	if ($ispis) {
		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		zamgerlog("masovni rezultati ispita za predmet p$ponudakursa",4);
		?>
		Rezultati ispita su upisani.
		<script language="JavaScript">
		location.href='?sta=nastavnik/ispiti&predmet=<?=$ponudakursa?>';
		</script>
		<?
	}
}



// Uneseni ispiti

print "Uneseni ispiti:<br/>\n";

$q110 = myquery("select i.id,UNIX_TIMESTAMP(i.datum),k.gui_naziv from ispit as i, komponenta as k where i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id order by i.datum,i.komponenta");
print "<ul>\n";
if (mysql_num_rows($q110)<1)
	print "<li>Nije unesen nijedan ispit.</li>";
while ($r110 = mysql_fetch_row($q110)) {
	print '<li><a href="?sta=izvjestaj/ispit&predmet='.$predmet.'&ispit='.$r110[0].'">'.$r110[2].' ('.date("d. m. Y.",$r110[1]).')</a></li>'."\n";
}
print "</ul>\n";



// Masovni unos rezultata ispita

$format = intval($_POST['format']);
if (!$_POST['format']) {
	$q110 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (mysql_num_rows($q110)>0) $format = mysql_result($q110,0,0);
	else //default vrijednost
		$format=0;
}

$separator = intval($_POST['separator']);
if (!$_POST['separator']) {
	$q120 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (mysql_num_rows($q120)>0) $separator = mysql_result($q120,0,0);
	else //default vrijednost
		$separator=0;
}

?><p><hr/></p>
<p><b>Kreiranje ispita</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="brpodataka" value="1">
<input type="hidden" name="duplikati" value="0">

<!--br/>Naziv ispita: <input type="text" name="naziv" size="20">&nbsp;-->
<br/>Tip ispita: <select name="tipispita" class="default"><?
	$tipispita = intval($_POST['tipispita']);
	$q111 = myquery("select k.id,k.gui_naziv from tippredmeta_komponenta as tpk, komponenta as k, predmet as p where p.id=$predmet and p.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and (k.tipkomponente=1 or k.tipkomponente=2) order by k.id");
	while ($r111 = mysql_fetch_row($q111)) {
		print '<option value="'.$r111[0].'"';
		if ($tipispita==$r111[0]) print ' SELECTED';
		print '>'.$r111[1].'</option>'."\n";
	}
?></select><br/><br/>
Datum: <?
$day=intval($_POST['day']); $month=intval($_POST['month']); $year=intval($_POST['year']); 
if ($day>0) print datectrl($day,$month,$year);
else print datectrl(date('d'),date('m'),date('Y'));
?><br/><br/>

<textarea name="massinput" cols="50" rows="10"><?
if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
?></textarea><br/>
<br/>Format imena i prezimena: <select name="format" class="default">
<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option></select>&nbsp;
Separator: <select name="separator" class="default">
<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
<input type="submit" value="  Dodaj  ">
</form></p>
<?




}

?>