<?

// IZVJESTAJ/GRANICNI - Izvjestaj o granicnim slucajevima

// v3.9.1.0 (2008/09/09) + Novi izvjestaj: granicni


function izvjestaj_granicni() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<?

// Parametri izvještaja
$parcijalnih = intval($_REQUEST['parcijalnih']);
$predmeta = intval($_REQUEST['predmeta']);

$sort_po_predmetu=0;
if ($_REQUEST['sort']=="predmet") $sort_po_predmetu=1;

$polozili = intval($_REQUEST['polozili']);

// Aktuelna godina
$q10 = myquery("select id, naziv from akademska_godina where aktuelna=1");
$ak_god = mysql_result($q10,0,0);


if ($polozili==1) {

	?>
	
	<h2>Studenti koji mogu upisati sljedeću godinu</h2>
	<table border="0">
	<?

} else {
	?>
	<h2>Granični slučajevi - <?=mysql_result($q10,0,1)?></h2>
	<?
	
	if ($sort_po_predmetu==0) {
		?>
		<table border="1">
		<tr><td>R. br.</td><td>Ime i prezime</td><td>Studij</td><td>Semestar</td><td>Uslovni predmeti</td></tr>
		<?
	}
}

$studenti_pali=array();
$rbr=1;
$ects_uslov = 6; // Maksimalan broj ECTS bodova koji se mogu prenijeti

$total_ispis = array();
$predmeti_naziv=array();

// Upit koji vraca sve studente upisane u aktuelnoj godini
$q20 = myquery("select ss.student,s.naziv,ss.semestar,o.ime,o.prezime from student_studij as ss, studij as s, osoba as o where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=0 and ss.student=o.id order by ss.studij, ss.semestar");
while($r20 = mysql_fetch_row($q20)) {
	$student = $r20[0];
	$studij = $r20[1];
	$semestar = $r20[2];
	$imeprezime = $r20[3]." ".$r20[4];

	if ($semestar == 6) $ects_uslov=12; // Zavrsni rad :(

	// Svi predmeti koje je ikada slusao
	$q30 = myquery("select pk.predmet, pk.ects, pk.semestar, p.naziv from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<=$semestar and pk.predmet=p.id order by pk.akademska_godina desc");
	$predmeti_pao=array();
	$ects_pao=array();
	$ects_suma=0;
	while ($r30 = mysql_fetch_row($q30)) {
		$q40 = myquery("select count(*) from konacna_ocjena as ko, ponudakursa as pk where ko.student=$student and ko.predmet=pk.id and pk.predmet=$r30[0]");
		if (mysql_result($q40,0,0)<1 && !in_array($r30[0], $predmeti_pao)) {
			$predmeti_pao[] = $r30[0];
			$ects_pao[$r30[0]] = $r30[1];
			// Predmeti sa ranijih godina imaju beskonacan ECTS
			if ($r30[2]<$semestar-1) $ects_pao[$r30[0]] = 1000;
			$ects_suma += $ects_pao[$r30[0]];
			$predmeti_naziv[$r30[0]] = $r30[3];
		}
	}

	if ($ects_suma <= $ects_uslov) {
		if ($polozili==1) {
			// Prikazujemo studente koji su dali uslov
			if ($studij!=$oldstudij) {
				$rbr=1;
				?>
				</table>
				<h3>Studij: <?=$studij?>, Upisuju semestar: <?=($semestar+1)?></h3>
				<table border="1">
				<tr><td>R. br.</td><td>Ime i prezime</td><td>Prenosi predmet?</td></tr>
				<?
				$oldstudij=$studij;
			}
			if ($ects_suma==0) $ispis="NE"; else $ispis="DA";

			?><tr><td><?=($rbr++)?></td><td><?=$imeprezime?></td><td><?=$ispis?></td></tr><?
			
		} else 
			// Preskacemo studente koji su dali uslov
			continue;
	}

	if ($polozili==1) continue; // Za ove sto su polozili vise nas nista ne interesuje

	$ispis=$ispis_nemoze="";
	foreach ($predmeti_pao as $predmet) {
		// Ako polozi ovaj predmet, daje uslov
		if ($ects_suma-$ects_pao[$predmet] <= $ects_uslov) {

			// Provjeravamo da li zadovoljava uslove za parcijale
			if ($parcijalnih==1) {
				$q50 = myquery("select count(*) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and io.student=$student and io.ocjena>=10 and i.predmet=pk.id and pk.predmet=$predmet and (i.komponenta=1 or i.komponenta=2)");
				if (mysql_result($q50,0,0)==0) {
					// Integralni?
					$q60 = myquery("select count(*) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and io.student=$student and io.ocjena>=20 and i.predmet=pk.id and pk.predmet=$predmet and i.komponenta=3");
					if (mysql_result($q60,0,0)==0) {
$ispis_nemoze .= $predmeti_naziv[$predmet]." - NE<br/>\n";
						// Ne moze proci, preskacemo ga
						continue;
					}
				}
			}
			$ispis .= $predmeti_naziv[$predmet]."<br/>\n";
			if ($sort_po_predmetu==1) {
				$counter[$predmet]++;
				$total_ispis[$predmet] .= "<tr><td>".$counter[$predmet]."</td><td>$imeprezime</td></tr>\n";
			}
		}
	}

	if ($ispis != "" && $sort_po_predmetu==0) {
		?><tr><td><?=($rbr++)?></td><td><?=$imeprezime?></td><td><?=$studij?></td><td><?=$semestar?></td><td><?=$ispis?></td></tr><?
	}
}

if ($sort_po_predmetu==1) {
	foreach ($total_ispis as $predmet => $ispis) {
		?>
		<h3><?=$predmeti_naziv[$predmet]?></h3>
		<table border="1"><tr><td>R. br.</td><td>Ime i prezime</td></tr>
		<?=$ispis?>
		</table>
		<?
	}

}

}

?>