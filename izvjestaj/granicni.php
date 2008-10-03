<?

// IZVJESTAJ/GRANICNI - Izvjestaj o granicnim slucajevima

// v3.9.1.0 (2008/09/09) + Novi izvjestaj: granicni
// v3.9.1.1 (2008/09/23) + Dodajem polje akademska_godina; omogucen prikaz kada je aktuelni semestar neparni


function izvjestaj_granicni() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<?


$ak_god = intval($_REQUEST['akademska_godina']);
if ($ak_god==0) {
	// Aktuelna godina
	$q10 = myquery("select id, naziv from akademska_godina where aktuelna=1");
	$ak_god = mysql_result($q10,0,0);
	$ak_god_naziv = mysql_result($q10,0,1);
} else {
	$q10 = myquery("select naziv from akademska_godina where id=$ak_god");
	$ak_god_naziv = mysql_result($q10,0,0);
}


// Ovo ispod je nidje veze:
if ($_REQUEST['prosjek'] == 1) {
	$prosjek=array();
	$q10 = myquery("select student from student_studij where semestar=6 and akademska_godina=3");
	while ($r10 = mysql_fetch_row($q10)) {
		$suma=$broj=0;
		$q20 = myquery("select ocjena from konacna_ocjena where student=$r10[0]");
		while ($r20 = mysql_fetch_row($q20)) {
			$suma += $r20[0];
			$broj++;
		}
		$prosjek[$r10[0]] = $suma/$broj;
	}
	arsort($prosjek);
	?>
	<table border="1"><tr><td>Prezime i ime</td><td>Prosjek</td></tr>
	<?
	foreach ($prosjek as $student=>$p) {
		if ($p<8.5) break;
		$q40 = myquery("select prezime,ime from osoba where id=$student");
		print "<tr><td>".mysql_result($q40,0,0)." ".mysql_result($q40,0,1)."</td><td>".round($p,2)."</td></tr>";
	}
	return;
}

// Parametri izvještaja
$parcijalnih = intval($_REQUEST['parcijalni']);
$predmeta = intval($_REQUEST['predmet']);
$parcijalnipredmet = intval($_REQUEST['parcijalnipredmet']);

$sort_po_predmetu=0;
if ($_REQUEST['sort']=="predmet") $sort_po_predmetu=1;

$polozili = intval($_REQUEST['polozili']);
$statistika = intval($_REQUEST['statistika']);


if ($polozili==1) {

	?>
	
	<h2>Studenti koji mogu upisati sljedeću godinu</h2>
	<table border="0">
	<?

} else {
	?>
	<h2>Granični slučajevi - <?=$ak_god_naziv?> - <? 
		if ($parcijalnih==1) print "parcijalni ispit"; 
		else print "$predmeta predmeta"; ?></h2>
	<?
	
	if ($sort_po_predmetu==0) {
		?>
		<table border="0">
		<?
	}
}

$studenti_pali=array();
$rbr=1;

$total_ispis = array();
$predmeti_naziv=array();

$student_studij = array();
$student_status = array();

// Upit koji vraca sve studente upisane u aktuelnoj godini
$q20 = myquery("select ss.student,s.naziv,ss.semestar,o.ime,o.prezime from student_studij as ss, studij as s, osoba as o where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=0 and ss.student=o.id order by ss.studij, ss.semestar, o.prezime, o.ime");
if (mysql_num_rows($q20)==0) 
	// Nema nikog u parnom semestru, probavamo neparni
	$q20 = myquery("select ss.student,s.naziv,ss.semestar,o.ime,o.prezime from student_studij as ss, studij as s, osoba as o where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=1 and ss.student=o.id order by ss.studij, ss.semestar, o.prezime, o.ime");

while($r20 = mysql_fetch_row($q20)) {
	$student = $r20[0];
	$studij = $r20[1];
	$semestar = $r20[2];
	if ($semestar%2==1) $semestar++;
	$imeprezime = $r20[4]." ".$r20[3];

	$ects_uslov = 6; // Maksimalan broj ECTS bodova koji se mogu prenijeti
	if ($semestar == 6) $ects_uslov=12; // Zavrsni rad :(
	if ($semestar == 4) $ects_uslov=0; // Ne moze se prenijeti 2->3 godina

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


	// Na kojoj godini/odsjeku je student
	if ($statistika==1) {
		$q45 = myquery("select ss.studij,ss.semestar from student_studij as ss, akademska_godina as ag where ss.student=$student and ss.akademska_godina=ag.id and ag.aktuelna=1 order by ss.semestar desc");
		$student_studij[$student] = mysql_result($q45,0,0);
		$student_semestar[$student] = mysql_result($q45,0,1);

	}


	if ($ects_suma <= $ects_uslov) {
		if ($statistika==1) $student_status[$student]=1; // 1 = polozio
		if ($polozili==1) {
			// Prikazujemo studente koji su dali uslov
			if ($studij!=$oldstudij || $semestar!=$oldsemestar) {
				$rbr=1;
				?>
				</table>
				<h3>Studij: <?=$studij?>, Upisuju semestar: <?=($semestar+1)?></h3>
				<table border="1">
				<tr><td>R. br.</td><td>Ime i prezime</td><td>Prenosi predmet?</td></tr>
				<?
				$oldstudij=$studij;
				$oldsemestar=$semestar;
			}
			if ($ects_suma==0) $ispis="NE"; else $ispis="DA";

			?><tr><td><?=($rbr++)?></td><td><?=$imeprezime?></td><td><?=$ispis?></td></tr><?
			
		} else 
			// Preskacemo studente koji su dali uslov
			continue;
	}

	if ($polozili==1) continue; // Za upit tipa "polozili" vise nas nista ne interesuje

	if ($statistika==1) $student_status[$student]=4; // pretpostavljamo status 4 = pao

	$ispis=$ispis_nemoze="";

	if ($predmeta>=2) {
		if (count($predmeti_pao)<=$predmeta) {
			if ($statistika==1) $student_status[$student]=3; // status 3 = fali citav predmet
			foreach ($predmeti_pao as $predmet) {
				$ispis .= $predmeti_naziv[$predmet]."<br/>\n";
				if ($sort_po_predmetu==1) {
					$counter[$predmet]++;
					$total_ispis[$predmet] .= "<tr><td>".$counter[$predmet]."</td><td>$imeprezime</td></tr>\n";
				}
			}
		}

	} else {

	foreach ($predmeti_pao as $predmet) {
		// Ako polozi ovaj predmet, daje uslov
		if ($ects_suma-$ects_pao[$predmet] <= $ects_uslov && $predmet!=131) {
			if ($statistika==1) $student_status[$student]=3; // status 3 = fali citav predmet

			// Provjeravamo da li zadovoljava uslove za parcijale
			if ($parcijalnih==1 || $statistika==1) {
				$q50 = myquery("select count(*) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and io.student=$student and io.ocjena>=10 and i.predmet=pk.id and pk.predmet=$predmet and (i.komponenta=1 or i.komponenta=2)");
				if (mysql_result($q50,0,0)==0) {
					// Integralni?
					$q60 = myquery("select count(*) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and io.student=$student and io.ocjena>=20 and i.predmet=pk.id and pk.predmet=$predmet and i.komponenta=3");
					if (mysql_result($q60,0,0)==0) {
//$ispis_nemoze .= $predmeti_naziv[$predmet]." - NE<br/>\n";
						// Ne moze proci, preskacemo ga
						if ($parcijalnih==1) continue;
						
					} else if ($statistika==1) {
						$student_status[$student]=2; // 2 = parcijalni
					}
				} else if ($statistika==1) {
					$student_status[$student]=2; // 2 = parcijalni
				}
			}
			$ispis .= $predmeti_naziv[$predmet]."<br/>\n";
			if ($sort_po_predmetu==1) {
				$counter[$predmet]++;
				$total_ispis[$predmet] .= "<tr><td>".$counter[$predmet]."</td><td>$imeprezime</td></tr>\n";
			}
		}
	}
	}

	if ($ispis != "" && $sort_po_predmetu==0) {
		if ($studij!=$oldstudij || $semestar!=$oldsemestar) {
			$rbr=1;
			?>
			</table>
			<h3>Studij: <?=$studij?>, Upisuju semestar: <?=($semestar+1)?></h3>
			<table border="1">
			<tr><td>R. br.</td><td>Ime i prezime</td><td>Nedostajući predmet(i)</td></tr>
			<?
			$oldstudij=$studij;
			$oldsemestar=$semestar;
		}

		?><tr><td><?=($rbr++)?></td><td><?=$imeprezime?></td><td><?=$ispis?></td></tr><?
	}
}


// Ispis sortiran po predmetu

if ($sort_po_predmetu==1) {
	foreach ($total_ispis as $predmet => $ispis) {
		if ($predmet==131) continue;
		$q1000 = myquery("select s.kratkinaziv, pk.semestar from studij as s, ponudakursa as pk where pk.predmet=$predmet and pk.studij=s.id");
		$studij = mysql_result($q1000,0,0);
		$semestar = mysql_result($q1000,0,1);

		?>
		<h3><?=$predmeti_naziv[$predmet]?> (<?=$studij?>, <?=$semestar?>. semestar)</h3>
		<table border="1"><tr><td>R. br.</td><td>Ime i prezime</td></tr>
		<?=$ispis?>
		</table>
		<?
	}
}


// Statistika

if ($statistika == 1) {
	foreach ($student_studij as $student=>$studij) {
		$semestar = $student_semestar[$student];
		//if ($studij==2 && $semestar==4) print "$student $studij $semestar ".$student_status[$student]."<br/>";
		if ($student_status[$student]==1) $studij_polozilo["$studij $semestar"]++;
		else if ($student_status[$student]==2) $studij_parcijalni["$studij $semestar"]++;
		else if ($student_status[$student]==3) $studij_citavpredmet["$studij $semestar"]++;
	}

	?>
	<p>&nbsp;</p>
	<b>Statistika po studijima:</b>
	<table border="1"><tr bgcolor="#cccccc"><td><b>Naziv studija</b></td>
	<td align="center"><b>I<br/>Dalo uslov</b></td>
	<td align="center"><b>II<br/>Jedan parcijalni</b></td>
	<td align="center"><b>III<br/>(I+II)</b></td>
	<td align="center"><b>IV<br/>Čitav predmet</b></td>
	<td align="center"><b>V<br/>(I+IV)</b></td>
	</tr>
	<?
	$q2000 = myquery("select id,naziv from studij order by id");
	while ($r20 = mysql_fetch_row($q2000)) {
		for ($i=1; $i<=10; $i++) {
			$s="$r20[0] $i";
			if ($studij_polozilo[$s]==0) continue;
			?>
			<tr>
				<td><?=$r20[1]?>, <?=($i/2)?>. godina</td>
				<td><?=$studij_polozilo[$s]?></td>
				<td><?=intval($studij_parcijalni[$s])?></td>
				<td><?=($studij_polozilo[$s]+$studij_parcijalni[$s])?></td>
				<td><?=intval($studij_citavpredmet[$s]+$studij_parcijalni[$s])?></td>
				<td><?=($studij_polozilo[$s]+$studij_parcijalni[$s]+$studij_citavpredmet[$s])?></td>
			</tr>
			<?
		}
	}

}


}

?>
