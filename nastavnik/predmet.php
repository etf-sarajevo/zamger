<?

// NASTAVNIK/PREDMET - pocetna stranica za administraciju predmeta - izbor studentskih modula



function nastavnik_predmet() {

	global $userid, $user_siteadmin, $_api_http_code;
	
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	// TODO prebaciti na aktivnosti
	/*$q15 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
	$tippredmeta = db_result($q15,0,0);
	if ($tippredmeta == 1000 || $tippredmeta == 1001) {
		require("nastavnik/zavrsni.php");
		nastavnik_zavrsni();
		return;
	}*/
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Opcije predmeta</h3></p>
	
	<?
	
	
	// Prikaz angažovanih nastavnika i saradnika
	// TODO ovo je sada na nastavnik/dodavanje_asistenata tako da ni ovo ne treba
	
	?>
	
	<p>Pristup predmetu imaju sljedeći nastavnici i saradnici (slovo N označava da saradnik ima privilegije nastavnika, a slovo S da ima privilegije "super-asistenta"):</p>
	
	<ul>
	<?
	
	$teachers = api_call("course/$predmet/access", [ "year" => $ag] )["results"];
	foreach($teachers as $teacher) {
		if ($teacher['accessLevel'] == "nastavnik") $dodaj=" (N)";
		else if ($teacher['accessLevel']=="super_asistent") $dodaj=" (S)";
		else $dodaj="";
		$name = $teacher['Person']['name'] . " " . $teacher['Person']['surname'];
		print "<li>$name$dodaj</li>\n";
	}
	
	?>
	</ul>
	
	<?
	
	// Click na checkbox za dodavanje modula
	
	
	// TODO prebaciti na aktivnosti
	/*
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
	*/


}

?>
