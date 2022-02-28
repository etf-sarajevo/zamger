<?php


// Promjena načina studiranja

function studentska_osobe_promijeni_nacin() {
	$osoba = int_param('osoba');
	
	$studij = intval($_REQUEST['studij']);
	$semestar = intval($_REQUEST['semestar']);
	$godina = intval($_REQUEST['godina']);
	
	$q10 = db_query("SELECT ime, prezime, brindexa FROM osoba WHERE id=$osoba");
	$ime = db_result($q10,0,0);
	$prezime = db_result($q10,0,1);
	$brindexa = db_result($q10,0,2);
	
	$q20 = db_query("SELECT nacin_studiranja FROM student_studij WHERE student=$osoba AND studij=$studij AND semestar=$semestar AND akademska_godina=$godina");
	if (db_num_rows($q20)<1) {
		niceerror("Greška");
		return;
	}
	$nacin = db_result($q20,0,0);
	
	if ($_REQUEST['subakcija'] == "mijenjaj") {
		$nacin = intval($_REQUEST['_lv_column_nacin_studiranja']);
		$q50 = db_query("UPDATE student_studij SET nacin_studiranja=$nacin WHERE student=$osoba AND studij=$studij AND semestar=$semestar AND akademska_godina=$godina");
		nicemessage("Promijenjen način studiranja za studenta $ime $prezime");
		print "<p><a href=\"?sta=studentska/osobe&amp;akcija=edit&amp;osoba=$osoba\">Nazad na podatke o studentu $ime $prezime</a></p>\n";
		zamgerlog("promijenjen nacin studiranja za u$osoba na $nacin", 4);
		zamgerlog2("promijenjen nacin studiranja", $osoba, $nacin);
		return;
	}
	
	// Podaci za ispis na ekran
	$q30 = db_query("SELECT naziv FROM studij WHERE id=$studij");
	$naziv_studija = db_result($q30,0,0);
	$q40 = db_query("SELECT naziv FROM akademska_godina WHERE id=$godina");
	$naziv_godine = db_result($q40,0,0);
	
	
	?>
	<a href="?sta=studentska/osobe&osoba=<?=$osoba?>&akcija=edit">Nazad na podatke o studentu</a><br/><br/>
	
	<h3><?=$ime?> <?=$prezime?> (<?=$brindexa?>)</h3>
	
	<p>Način studiranja na: <b><?=$naziv_studija?>, <?=$naziv_godine?>, <?=$semestar?>. semestar</b></p>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="mijenjaj">
	<?=db_dropdown("nacin_studiranja",$nacin) ?><br>
	<input type="submit" value=" Promijeni ">
	</form>
	<?
}