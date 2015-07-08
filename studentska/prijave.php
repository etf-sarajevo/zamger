<?

// STUDENTSKA/PRIJAVE - štampanje prijava


function studentska_prijave() {

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
$naziv_predmeta = mysql_result($q10,0,0);

$q20 = myquery("select naziv from akademska_godina where id=$ag");
$naziv_ag = mysql_result($q20,0,0);


// Kreiramo spisak studenata i provjeravamo njihov status
$spisak = "";
$studenata_uslov = $studenata_bez_ocjene = $svih_studenata = 0;
$q30 = myquery("select o.id, o.prezime, o.ime from osoba as o, student_predmet as sp, ponudakursa as pk where o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime");
while ($r30 = mysql_fetch_row($q30)) {
	$student = $r30[0];
	$spisak .= "<option value=\"$student\">$r30[1] $r30[2]</option>\n";
	$svih_studenata++;

	// Ima li ocjenu?
	$q33 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet");
	if (mysql_result($q33,0,0)==0) {
		$studenata_bez_ocjene++;

		// Ima li uslov?
		// Dva parcijalna ispita
		$q35 = myquery("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$student and io.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and k.tipkomponente=1 and io.ocjena>=k.prolaz");
		$parcijalnih = mysql_result($q35,0,0);
		// Integralni ispiti
		$q37 = myquery("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$student and io.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and k.tipkomponente=2 and io.ocjena>=k.prolaz");
		$integralnih = mysql_result($q37,0,0);
		if ($integralnih==1 || $parcijalnih==2) // FIXME: ovo radi samo za ETF Bologna standard
			$studenata_uslov++;
	}
}

$studenata_sa_ocjenom = $svih_studenata - $studenata_bez_ocjene;

?>
<p><h3>Studentska služba - Štampanje prijava</h3></p>

<p><h3>Predmet: <?=$naziv_predmeta?> (<?=$naziv_ag?>)</h3></p>

<p><a href="?sta=studentska/predmeti&akcija=edit&predmet=<?=$predmet?>&ag=<?=$ag?>">&gt; &gt; Povratak na stranicu predmeta</a></p>

<p>Štampajte prijave za:

<ul>
<li>Sve studente koji su se prijavili za ispit:<br/>
	<ul>
<?

// Spisak termina za ispite
$q40 = myquery("select it.id, UNIX_TIMESTAMP(it.datumvrijeme), k.gui_naziv, count(*) from ispit as i, ispit_termin as it, student_ispit_termin as sit, komponenta as k where it.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and sit.ispit_termin=it.id group by sit.ispit_termin order by it.datumvrijeme");
while ($r40 = mysql_fetch_row($q40)) {
	?>
	<li><a href="?sta=izvjestaj/prijave&ispit_termin=<?=$r40[0]?>"><?=date("d.m.Y. h:i", $r40[1]).", ".$r40[2]."</a> (".$r40[3]." studenata)"?></li>
	<?
}

?>
	<br/></ul>
</li>
<li><a href="?sta=izvjestaj/prijave&predmet=<?=$predmet?>&ag=<?=$ag?>&tip=uslov">Sve studente koji imaju uslove za usmeni (<?=$studenata_uslov?> studenata)</a><br/>&nbsp;</li>
<li><a href="?sta=izvjestaj/prijave&predmet=<?=$predmet?>&ag=<?=$ag?>&tip=bez_ocjene">Sve studente koji nemaju upisanu ocjenu (<?=$studenata_bez_ocjene?> studenata)</a><br/>&nbsp;</li>
<li><a href="?sta=izvjestaj/prijave&predmet=<?=$predmet?>&ag=<?=$ag?>&tip=sa_ocjenom">Sve studente koji imaju upisanu ocjenu (<?=$studenata_sa_ocjenom?> studenata)</a><br/>&nbsp;</li>
<li><a href="?sta=izvjestaj/prijave&predmet=<?=$predmet?>&ag=<?=$ag?>&tip=sve">Sve studente (<?=$svih_studenata?> studenata)</a><br/>&nbsp;</li>
<li>Pojedinačnog studenta:<br/>
<form action="index.php" method="GET">
<input type="hidden" name="sta" value="izvjestaj/prijave">
<input type="hidden" name="predmet" value="<?=$predmet?>">
<input type="hidden" name="ag" value="<?=$ag?>">
<select name="student" class="default"><?=$spisak?></select> <input type="submit" value=" Odaberi " class="default"></form></li>
</ul>
<?

	$q33 = myquery("select o.id from osoba as o, angazman as a where a.predmet=$predmet and a.akademska_godina=$ag and a.angazman_status=1 and a.osoba=o.id");
	if (mysql_num_rows($q33)==0) {
		?><p><b>Napomena:</b> Za ovaj predmet nije podešen odgovorni nastavnik!</p><?
	} else if (mysql_num_rows($q33)>1) { // Ako imaju dva odgovorna nastavnika, ne znam kojeg da stavim
		?><p><b>Napomena:</b> Za ovaj predmet je podešen više od jednog odgovornog nastavnika! Polje za odgovornog nastavnika na prijavi neće biti popunjeno. Morate ga popuniti ručno.</p><?
	} else {
		$id_nastavnika = mysql_result($q33,0,0);
		// Određujemo zvanje
		$q34 = myquery("select count(*) from izbor where osoba=$id_nastavnika");
		if (mysql_result($q34, 0, 0)<1) {
			?><p><b>Napomena:</b> Predmetnom nastavniku je istekao izbor ili nisu popunjeni odgovarajući podaci. Bez podataka o izboru ne možemo ispravno popuniti titulu nastavnika. Polje za odgovornog nastavnika na prijavi neće biti popunjeno. Morate ga popuniti ručno.</p><?
		}
	}


}

?>
