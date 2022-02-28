<?php


// Ažuriranje dugovanja studenta

function studentska_osobe_zaduzenje() {
	$osoba = int_param('osoba');
	
	$zaduzenje = db_get("SELECT zaduzenje FROM student_zaduzenje WHERE student=$osoba");
	if (param('subakcija') == "potvrda" && check_csrf_token()) {
		$novo_zaduzenje = floatval($_REQUEST['zaduzenje']);
		if ($novo_zaduzenje == 0 && $zaduzenje != 0)
			db_query("DELETE FROM student_zaduzenje WHERE student=$osoba");
		else if ($zaduzenje === false && $novo_zaduzenje != 0)
			db_query("INSERT INTO student_zaduzenje SET student=$osoba, zaduzenje=$novo_zaduzenje");
		else
			db_query("UPDATE student_zaduzenje SET zaduzenje=$novo_zaduzenje WHERE student=$osoba");
		
		nicemessage("Ažurirano zaduženje za studenta " . db_get("SELECT CONCAT(ime, ' ', prezime) FROM osoba WHERE id=$osoba"));
		?>
		<a href="?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$osoba?>">Nazad na podatke o studentu</a>
		<?
		return;
	}
	
	?>
	<h2><?=db_get("SELECT CONCAT(ime, ' ', prezime) FROM osoba WHERE id=$osoba")?></h2>
	<?=genform("POST")?>
	<input type="hidden" name="subakcija" value="potvrda">
	Zaduženje: <input type="text" name="zaduzenje" value="<?=$zaduzenje?>">
	<input type="submit" value="Izmijeni">
	</form>
	<a href="?sta=studentska/osobe&amp;akcija=edit&amp;osoba=<?=$osoba?>">Nazad</a>
	<?
}

function studentska_osobe_applet_zaduzenje($osoba) {
	$zaduzenje = db_get("SELECT zaduzenje FROM student_zaduzenje WHERE student=$osoba");
	if ($zaduzenje > 0) {
		print "<p style=\"color: red\">Evidentirano je zaduženje: <b>" . sprintf("%.2f", $zaduzenje) . " KM</b> - ";
	}
	print "<a href=\"?sta=studentska/osobe&osoba=$osoba&akcija=izmijeni_zaduzenje\">Izmijeni zaduženje</a></p>\n";
}