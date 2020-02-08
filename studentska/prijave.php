<?

// STUDENTSKA/PRIJAVE - štampanje prijava



function studentska_prijave() {

$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = db_query("select naziv from predmet where id=$predmet");
$naziv_predmeta = db_result($q10,0,0);

$q20 = db_query("select naziv from akademska_godina where id=$ag");
$naziv_ag = db_result($q20,0,0);


// Kreiramo spisak studenata i provjeravamo njihov status
$spisak = "";
$studenata_uslov = $studenata_bez_ocjene = $svih_studenata = 0;
$q30 = db_query("select o.id, o.prezime, o.ime from osoba as o, student_predmet as sp, ponudakursa as pk where o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime");
while ($r30 = db_fetch_row($q30)) {
	$student = $r30[0];
	$spisak .= "<option value=\"$student\">$r30[1] $r30[2]</option>\n";
	$svih_studenata++;

	// Ima li ocjenu?
	$q33 = db_query("select count(*) from konacna_ocjena where student=$student and predmet=$predmet");
	if (db_result($q33,0,0)==0) {
		$studenata_bez_ocjene++;

		// Ima li uslov?
		// Dva parcijalna ispita
		$q35 = db_query("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$student and io.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and k.tipkomponente=1 and io.ocjena>=k.prolaz");
		$parcijalnih = db_result($q35,0,0);
		// Integralni ispiti
		$q37 = db_query("select count(*) from ispitocjene as io, ispit as i, komponenta as k where io.student=$student and io.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and k.tipkomponente=2 and io.ocjena>=k.prolaz");
		$integralnih = db_result($q37,0,0);
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
$q40 = db_query("select it.id, UNIX_TIMESTAMP(it.datumvrijeme), k.gui_naziv, count(*) from ispit as i, ispit_termin as it, student_ispit_termin as sit, komponenta as k where it.ispit=i.id and i.predmet=$predmet and i.akademska_godina=$ag and i.komponenta=k.id and sit.ispit_termin=it.id group by sit.ispit_termin order by it.datumvrijeme");
$prosli_datum = $prosla_komponenta = ""; 
$broj_na_datum = $studenata_na_datum = 0;
while ($r40 = db_fetch_row($q40)) {
	if (date("d.m.Y", $r40[1]) != $prosli_datum || $prosla_komponenta != $r40[2]) {
		if ($broj_na_datum > 1) {
			?>
			<li><a href="?sta=izvjestaj/prijave&amp;tip=na_datum&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Svi studenti na datum <?="$prosli_datum, $prosla_komponenta</a> ($studenata_na_datum studenata)"?> - samo studenti <a href="?sta=izvjestaj/prijave&amp;tip=na_datum_sa_ocjenom&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">sa ocjenom</a></li>
			<?
		}
		$prosli_datum = date("d.m.Y", $r40[1]);
		$broj_na_datum = 1;
		$studenata_na_datum = $r40[3];
		$prosla_komponenta = $r40[2];
	} else {
		$broj_na_datum++;
		$studenata_na_datum += $r40[3];
	}
	?>
	<li><a href="?sta=izvjestaj/prijave&amp;ispit_termin=<?=$r40[0]?>"><?=date("d.m.Y. h:i", $r40[1]).", ".$r40[2]."</a> (".$r40[3]." studenata)"?> - samo studenti <a href="?sta=izvjestaj/prijave&amp;ispit_termin=<?=$r40[0]?>&amp;tip=sa_ocjenom">sa ocjenom</a></li>
	<?
}
if ($broj_na_datum > 1) {
	?>
	<li><a href="?sta=izvjestaj/prijave&amp;tip=na_datum&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Svi studenti na datum <?="$prosli_datum, $prosla_komponenta</a> ($studenata_na_datum studenata)"?> - samo studenti <a href="?sta=izvjestaj/prijave&amp;tip=na_datum_sa_ocjenom&amp;datum=<?=$prosli_datum?>&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">sa ocjenom</a></li>
	<?
}

?>
	<br/></ul>
</li>
<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=uslov">Sve studente koji imaju uslove za usmeni (<?=$studenata_uslov?> studenata)</a><br/>&nbsp;</li>
<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=bez_ocjene">Sve studente koji nemaju upisanu ocjenu (<?=$studenata_bez_ocjene?> studenata)</a><br/>&nbsp;</li>
<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=sa_ocjenom">Sve studente koji imaju upisanu ocjenu (<?=$studenata_sa_ocjenom?> studenata)</a><br/>&nbsp;</li>
<li><a href="?sta=izvjestaj/prijave&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;tip=sve">Sve studente (<?=$svih_studenata?> studenata)</a><br/>&nbsp;</li>
<li>Pojedinačnog studenta:<br/>
<form action="index.php" method="GET">
<input type="hidden" name="sta" value="izvjestaj/prijave">
<input type="hidden" name="predmet" value="<?=$predmet?>">
<input type="hidden" name="ag" value="<?=$ag?>">
<select name="student" class="default"><?=$spisak?></select> <input type="submit" value=" Odaberi " class="default"></form></li>
</ul>
<?

	$q33 = db_query("select o.id from osoba as o, angazman as a where a.predmet=$predmet and a.akademska_godina=$ag and a.angazman_status=1 and a.osoba=o.id");
	if (db_num_rows($q33)==0) {
		?><p><b>Napomena:</b> Za ovaj predmet nije podešen odgovorni nastavnik!</p><?
	} else if (db_num_rows($q33)>1) { // Ako imaju dva odgovorna nastavnika, ne znam kojeg da stavim
		?><p><b>Napomena:</b> Za ovaj predmet je podešen više od jednog odgovornog nastavnika! Polje za odgovornog nastavnika na prijavi neće biti popunjeno. Morate ga popuniti ručno.</p><?
	} else {
		$id_nastavnika = db_result($q33,0,0);
		// Određujemo zvanje
		$q34 = db_query("select count(*) from izbor where osoba=$id_nastavnika");
		if (db_result($q34, 0, 0)<1) {
			?><p><b>Napomena:</b> Predmetnom nastavniku je istekao izbor ili nisu popunjeni odgovarajući podaci. Bez podataka o izboru ne možemo ispravno popuniti titulu nastavnika. Polje za odgovornog nastavnika na prijavi neće biti popunjeno. Morate ga popuniti ručno.</p><?
		}
	}


}

?>
