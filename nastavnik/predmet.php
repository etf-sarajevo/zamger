<?

// NASTAVNIK/PREDMET - pocetna stranica za administraciju predmeta - izbor studentskih modula



function nastavnik_predmet() {

global $userid,$user_siteadmin;



// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = db_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}


$q15 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
$tippredmeta = db_result($q15,0,0);
if ($tippredmeta == 1000 || $tippredmeta == 1001) {
	require("nastavnik/zavrsni.php");
	nastavnik_zavrsni();
	return;
}

?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Opcije predmeta</h3></p>

<?


// Prikaz angažovanih nastavnika i saradnika

?>

<p>Pristup predmetu imaju sljedeći nastavnici i saradnici (slovo N označava da saradnik ima privilegije nastavnika, a slovo S da ima privilegije "super-asistenta"):</p>

<ul>
<?

$q100 = db_query("select o.ime, o.prezime, np.nivo_pristupa from osoba as o, nastavnik_predmet as np where np.nastavnik=o.id and np.predmet=$predmet and np.akademska_godina=$ag order by np.nivo_pristupa");
while ($r100 = db_fetch_row($q100)) {
	if ($r100[2]=="nastavnik") $dodaj=" (N)";
	else if ($r100[2]=="super_asistent") $dodaj=" (S)";
	else $dodaj="";
	print "<li>$r100[0] $r100[1]$dodaj</li>\n";
}

?>
</ul>

<?

// Click na checkbox za dodavanje modula
// Prebaciti na POST?

if (param('akcija') == "set_smodul" && check_csrf_token()) {
	$smodul = intval($_POST['smodul']);
	if ($_POST['aktivan']==0) $aktivan=1; else $aktivan=0;
	$q15 = db_query("replace studentski_modul_predmet set predmet=$predmet, akademska_godina=$ag, studentski_modul=$smodul, aktivan=$aktivan");
	if ($aktivan==1) {
		zamgerlog("aktiviran studentski modul $smodul (predmet pp$predmet)",2); // nivo 2: edit
		zamgerlog2("aktiviran studentski modul", $predmet, $ag, $smodul);
	} else {
		zamgerlog("deaktiviran studentski modul $smodul (predmet pp$predmet)",2); // nivo 2: edit
		zamgerlog2("deaktiviran studentski modul", $predmet, $ag, $smodul);
	}
}



// Opcije predmeta

?>

<SCRIPT language="JavaScript">
function upozorenje(smodul,aktivan) {
	document.smodulakcija.smodul.value=smodul;
	document.smodulakcija.aktivan.value=aktivan;
	document.smodulakcija.submit();
}
</SCRIPT>
<?
unset ($_REQUEST['smodul']);
unset ($_REQUEST['aktivan']);
print genform("POST", "smodulakcija");
?>
<input type="hidden" name="akcija" value="set_smodul">
<input type="hidden" name="smodul" value="">
<input type="hidden" name="aktivan" value="">
</form>

<p>Izaberite opcije koje želite da učinite dostupnim studentima:<br/>
<?





// Studentski moduli koji su aktivirani za ovaj predmet

$q20 = db_query("select id, gui_naziv from studentski_modul order by id");
if (db_num_rows($q20)<1)
	print "<p>Nijedan modul nije ponuđen.</p>\n";
while ($r20 = db_fetch_row($q20)) {
	$smodul = $r20[0];
	$naziv = $r20[1];
	if ($smodul == 6) continue; // Onemogućujemo isključenje ankete

	$q30 = db_query("select aktivan from studentski_modul_predmet where predmet=$predmet and akademska_godina=$ag and studentski_modul=$smodul");
	if (db_num_rows($q30)<1 || db_result($q30,0,0)==0) {
		$aktivan=0; $checked="";
	} else {
		$aktivan=1; $checked="CHECKED";
	}
	?>
	<input type="checkbox" onchange="javascript:onclick=upozorenje('<?=$smodul?>','<?=$aktivan?>')" <?=$checked?>> <?=$naziv?><br/>
	<?
}



}

?>
