<?php

// IZVJESTAJ/KOLIZIJA_PREDMET - spisak studenata na koliziji po predmetu



function izvjestaj_kolizija_predmet() {
	
	$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	?>
	<p>Univerzitet u Sarajevu<br/>
		Elektrotehnički fakultet Sarajevo</p>
	<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
	<h2>Studenti na koliziji po predmetu</h2>
	<?
	
	?>
	<table border="1" cellspacing="0" cellpadding="2">
		<tr><th>Student</th>
			<th>Predmet</th>
			<th>Studij</th>
			<th>Semestar predmeta</th>
		</tr>
	<?
	
	$q10 = db_query("SELECT o.prezime, o.ime, o.brindexa, p.naziv, pk.semestar, s.naziv
		FROM `student_predmet` sp, ponudakursa pk, student_studij ss, predmet p, osoba o, studij s
		WHERE sp.predmet=pk.id and pk.akademska_godina=$ag and ss.student=sp.student and ss.akademska_godina=$ag and (ss.semestar=1 or ss.semestar=3) and pk.semestar>ss.semestar+1 and pk.predmet=p.id and sp.student=o.id AND ss.studij=s.id
		ORDER BY pk.studij, pk.semestar, p.naziv, o.prezime, o.ime");
	while (db_fetch6($q10, $prezime, $ime, $brindexa, $predmet, $semestar, $studij)) {
		?>
		<tr>
			<td><?=$prezime?> <?=$ime?> (<?=$brindexa?>)</td>
			<td><?=$predmet?></td>
			<td><?=$studij?></td>
			<td><?=$semestar?></td>
		</tr>
		<?
	}

		?></table><?
}