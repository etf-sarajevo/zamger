<?

// NASTAVNIK/ZADACE - kreiranje zadaća i masovni unos

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/04/03) + Koristim lib/manip
// v3.9.1.2 (2008/05/09) + Forma za masovni unos koristila za hidden polje "akcija" vrijednost "masszadaca" umjesto "massinput", nije ispravno preuzeta vrijednost zadace iz db_dropdown (falilo _lv_column), dugme nazad nije bilo ispravno obradjeno
// v3.9.1.3 (2008/05/12) + Kod masovnog unosa u upitu stajalo SET... redni_broj=$bodova :( Popravljen logging
// v3.9.1.4 (2008/05/16) + Dodan update_komponente()
// v3.9.1.5 (2008/08/18) + Informativnija greska kod pokusaja masovnog unosa zadaca ako ne postoji nijedna zadaca, promijenjen naslov "Unos zadace" u "Kreiranje zadace", dodana zastita od visestrukog slanja kod masovnog unosa




function nastavnik_zadace() {

global $userid,$user_siteadmin;

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe
global $_lv_; // radi autogenerisanih formi


$predmet=intval($_REQUEST['predmet']);
if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

//$tab=$_REQUEST['tab'];
//if ($tab=="") $tab="Opcije";

//logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = myquery("select np.admin from nastavnik_predmet as np where np.nastavnik=$userid and np.predmet=$predmet");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/zadace privilegije (predmet p$predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Zadaće</h3></p>

<?

# Masovni unos zadaća

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1) {
	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0;

	// Provjera ostalih parametara
	$zadaca = intval($_REQUEST['_lv_column_zadaca']);
	$zadatak = intval($_REQUEST['zadatak']);

	$q20 = myquery("select naziv,zadataka,bodova,komponenta from zadaca where id=$zadaca");
	if (mysql_num_rows($q20)<1) {
		zamgerlog("nepostojeca zadaca $zadaca",3); // 3 = greška
		niceerror("Morate najprije kreirati zadaću");
		print "\n<p>Koristite formular &quot;Kreiranje zadaće&quot; koji se nalazi na prethodnoj stranici. Ukoliko ne vidite nijednu zadaću na spisku &quot;Postojeće zadaće&quot;, koristite dugme Refresh vašeg web preglednika.</p>\n";
		return;
	}
	if (mysql_result($q20,0,1)<$zadatak) {
		zamgerlog("zadaca $zadaca nema $zadatak zadataka",3);
		niceerror("Zadaća \"".mysql_result($q20,0,0)."\" nema $zadatak zadataka.");
		return;
	}
	$maxbodova=mysql_result($q20,0,2);
	$komponenta=mysql_result($q20,0,3);

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	if (count($mass_rezultat)==0) {
		niceerror("Niste unijeli ništa.");
		return;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="_lv_column_zadaca" value="<?=$zadaca?>">
		<?
	}


	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$bodova = $mass_rezultat['podatak1'][$student];

		// Student neocijenjen (prazno mjesto za ocjenu)
		if (floatval($bodova)==0 && strpos($bodova,"0")===FALSE) {
			if ($ispis)
				print "Student '$prezime $ime' - nema zadaću (nije unesen broj bodova $bodova)<br/>";
			continue;
		}

		// Bodovi moraju biti manji od maximalnih borova
		$bodova = floatval($bodova);
		if ($bodova>$maxbodova) {
			if ($ispis) {
				print "-- Studenta '$prezime $ime' ima $bodova bodova što je više od maksimalnih $maxbodova<br/>";
				//$greska=1;
				continue;
			}
		}

		// Zaključak
		if ($ispis) {
			print "Student '$prezime $ime' - zadaća $zadaca, bodova $bodova<br/>";
		} else {
			$q30 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$student, status=5, bodova=$bodova"); 
			// status 5: pregledana
			update_komponente($student,$predmet,$komponenta); // update statistike
		}
	}

	if ($ispis) {
		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		zamgerlog("masovno upisane zadaće na predmet p$predmet, zadaća z$zadaca, zadatak $zadatak",2); // 2 = edit
		?>
		Bodovi iz zadaća su upisani.
		<script language="JavaScript">
		location.href='?sta=nastavnik/zadace&predmet=<?=$predmet?>';
		</script>
		<?
	}
}


// Spisak postojećih zadaća

$_lv_["where:predmet"] = $predmet;
$_lv_["where:komponenta"] = 6; // namećemo standardnu komponentu za zadaće... FIXME

print "Postojeće zadaće:<br/>\n";
print db_list("zadaca");


// Kreiranje nove zadace ili izmjena postojeće

$izabrana = intval($_REQUEST['_lv_nav_id']);
if ($izabrana==0) {
	?><p><hr/></p>
	<p><b>Kreiranje zadaće</b><br/>
	<?
} else {
	?><p><hr/></p>
	<p><b>Izmjena zadaće</b></p>
	<?
}

$_lv_["label:programskijezik"] = "Programski jezik";
$_lv_["label:zadataka"] = "Broj zadataka";
$_lv_["label:bodova"] = "Max. broj bodova";
$_lv_["label:attachment"] = "Slanje zadatka u formi attachmenta";
$_lv_["label:rok"] = "Rok za slanje";
$_lv_["hidden:vrijemeobjave"] = 1;
print db_form("zadaca");



// Formular za masovni unos zadaća

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

$q130 = myquery("select count(*) from zadaca where predmet=$predmet");
if (mysql_result($q130,0,0)>0) {

?><p><hr/></p>
<p><b>Masovni unos zadaća</b><br/>
<?

print genform("POST");
?><input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="brpodataka" value="1">
<input type="hidden" name="duplikati" value="0">

Izaberite zadaću: <?=db_dropdown("zadaca");?>
Izaberite zadatak: <select name="zadatak"><?
$q112 = myquery("select zadataka from zadaca where predmet=$predmet order by zadataka desc limit 1");
for ($i=1; $i<=mysql_result($q112,0,0); $i++) {
	print "<option value=\"$i\">$i</option>\n";
}
?>
</select><br/><br/>

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

} else {

	?><p><hr/></p>
	<p><b>Masovni unos zadaća NIJE MOGUĆ</b><br/>
	Najprije kreirajte zadaću koristeći formular iznad</p>
	<?
}



}

?>