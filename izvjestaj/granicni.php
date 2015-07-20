<?

// IZVJESTAJ/GRANICNI - Izvjestaj o granicnim slucajevima

// v3.9.1.0 (2008/09/09) + Novi izvjestaj: granicni
// v3.9.1.1 (2008/09/23) + Dodajem polje akademska_godina; omogucen prikaz kada je aktuelni semestar neparni
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/24) + Prebacena polja ects i tippredmeta iz tabele ponudakursa u tabelu predmet
// v4.0.9.2 (2009/03/31) + Tabela ispit preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.3 (2009/03/31) + Tabela konacna_ocjena preusmjerena sa ponudakursa na tabelu predmet
// v4.0.9.4 (2009/09/14) + Izmjene u bazi su izazvale da predmeti nisu bili ispravno sortirani; predmeti su sada sortirani po studiju i semestru, a studenti po imenu



// Funkcija koja provjerava da li je student položio ili pao predmet
// Vraća:
//    0 - student je položio predmet u datoj akademskoj godini ili nekoj ranijoj
//    1 - studentu fali jedan parcijalni ili jedan usmeni ispit na predmetu u datoj akademskoj godini
//    2 - studentu fali više od jednog parcijalnog ispita na predmetu u datoj akademskoj godini
//    3 - student nije slušao predmet u datoj akademskoj godini niti ga je položio ranije
// (ne bi se smjelo desiti, osim ako student uopšte nije na studiju?)
// TODO: prebaciti u lib/manip ?
function pao_predmet($student, $predmet, $ak_god) {
	// Da li je student ikada položio predmet
	$q10 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$predmet and ocjena>5");
	if (mysql_result($q10,0,0)>0) return 0;

	// Da li student sluša predmet u datoj akademskoj godini?
	$qa20 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ak_god");
	if (mysql_result($qa20,0,0)==0) return 3;


	// Statistika za parcijalne ispite
	$nepolozenih_ispita=0;

	// Koje sve vrste ispita postoje na predmetu?
	// Integralni i usmeni nas ne interesuju
	$q100 = myquery("select k.id, k.prolaz from tippredmeta_komponenta as tpk, akademska_godina_predmet as agp, komponenta as k where agp.predmet=$predmet and agp.akademska_godina=$ak_god and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=1 and k.gui_naziv != 'Usmeni' and k.gui_naziv != 'Završni'"); 
	$broj_vrsta_ispita = mysql_num_rows($q100);
	while ($r100=mysql_fetch_row($q100)) {
		$prolaz = $r100[1];
		if ($prolaz == 0) continue; // Preskačemo ispite bez prolaza

		// Da li je student položio tu vrstu ispita?
		$q110 = myquery("select count(*) from ispit as i, ispitocjene as io where i.predmet=$predmet and i.akademska_godina=$ak_god and i.komponenta=$r100[0] and i.id=io.ispit and io.student=$student and io.ocjena>=$prolaz");
		if (mysql_result($q110,0,0)==0) { 
			$nepolozenih_ispita++;
		}
	}

	if ($nepolozenih_ispita==1) {
		// Postoje dvije mogućnosti
		//    1. Student je položio ispit integralno, ali nema ocjenu, što znači da mu fali usmeni ispit
		//    2. Student nije položio ispit integralno, fali mu jedan parcijalni ispit
		return 1;
	}

	else if ($nepolozenih_ispita==0) {
		// Da li su uopšte definisane komponente ispita?
		if ($broj_vrsta_ispita>0) {
			// Student je položio sve predviđene tipove ispita, ali nema konačnu ocjenu
			// Pretpostavljamo da mu fali usmeni ispit
			return 1;
		} else {
			// U sistemu bodovanja predmeta uopšte nisu predviđeni ispiti
			// Ne možemo ništa osim proglasiti da student nije položio predmet
			return 2;
		}
	}

	else {
		// Studentu fali više od jedne parcijale, ispiti nisu održani ili nisu uneseni u Zamger
		// Mi tu ne možemo ništa osim proglasiti da student nije položio predmet
		return 2;
	}
}



// Vraća naziv predmeta iz cache-a na osnovu IDa
function naziv_predmeta_cache($id_predmeta) {
	static $nazivi_predmeta = array();
	if ($nazivi_predmeta[$id_predmeta]) return $nazivi_predmeta[$id_predmeta];
	$q10 = myquery("select naziv from predmet where id=$id_predmeta");
	$nazivi_predmeta[$id_predmeta] = mysql_result($q10,0,0);
	return $nazivi_predmeta[$id_predmeta];
}



// Početak izvještaja

function izvjestaj_granicni() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<?

$varijanta = intval($_REQUEST['varijanta']);
if ($varijanta==1) {


// Druga varijanta izvjestaja

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

// Daj spisak studenata, a ne sumarne statistike
$prikaz = $_REQUEST['prikaz'];

$svi_studenti = intval($_REQUEST['svi_studenti']);

$limit_studij = $limit_godina = 0;
if ($_REQUEST['studij_godina'] == "izbor") {
	$limit_studij = intval($_REQUEST['studij']);
	$limit_godina = intval($_REQUEST['godina_studija']);
}

global $limit_predmet, $limit_parcijalni, $douslova;
$limit_predmet = $limit_parcijalni = -1;
if ($_REQUEST['vrste_granicnih'] == "izbor") {
	$limit_predmet = intval($_REQUEST['predmeta']);
	$limit_parcijalni = intval($_REQUEST['parcijalnih']);
}

$ispis_predmeta=array();



// Naslov

?>
<h2>Studenti po broju položenih predmeta - <?=$ak_god_naziv?></h2>
<p>
<?

if ($limit_studij > 0) {
	$q15 = myquery("select naziv from studij where id=$limit_studij");
	print "Studij: ".mysql_result($q15,0,0).",\n";
}
if ($limit_studij < 0) {
	print "Svi studiji ".(-$limit_studij).". ciklusa,\n";
}
if ($limit_godina > 0) {
	print "$limit_godina. godina studija<br>\n";
}
if ($limit_predmet>-1 && $limit_parcijalni>-1) {
	print "Studenti kojima je ostalo $limit_predmet nepoloženih predmeta integralno i $limit_parcijalni parcijalno";
	if ($_REQUEST['douslova']) print " <b>do uslova</b>";
}

print "</p>";

// Spisak studenata po studiju

$q20 = myquery("select ss.student, s.naziv, ss.semestar, o.ime, o.prezime, o.brindexa, ss.studij, ss.plan_studija, ts.ciklus, ts.trajanje from student_studij as ss, studij as s, osoba as o, tipstudija as ts where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=0 and ss.student=o.id and s.tipstudija=ts.id order by ss.studij, ss.semestar, o.prezime, o.ime");

if (mysql_num_rows($q20)==0) 
	// Nema nikog u parnom semestru, probavamo neparni
	$q20 = myquery("select ss.student, s.naziv, ss.semestar, o.ime, o.prezime, o.brindexa, ss.studij, ss.plan_studija, ts.ciklus, ts.trajanje from student_studij as ss, studij as s, osoba as o, tipstudija as ts where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=1 and ss.student=o.id and s.tipstudija=ts.id order by ss.studij, ss.semestar, o.prezime, o.ime");

$studij_id=-1; $plan_studija=-1; $semestar=-1;
$plan_studija = $plan_studija_obavezan = array();
$ukupno=0; $koliko_nepolozenih=array(); $max_nepolozenih=0;
$nazivi_predmeta=array();

while ($r20 = mysql_fetch_row($q20)) {
	// Provjeravam limite
	if ($limit_studij != 0 && $limit_studij != $r20[6] && $limit_studij != - $r20[8]) continue;
	if ($limit_godina != 0 && $limit_godina != ceil($r20[2]/2)) continue;

	$old_studij_id = $studij_id;
	$studij_id = $r20[6];
	$old_plan_studija = $plan_studija;
	$ss_plan_studija = $r20[7];
	$old_semestar = $semestar;
	$semestar = $r20[2];
	$godina = ceil($semestar/2);
	$ciklus_studija = $r20[8];
	$semestara_na_ciklusu = $r20[9];

//print "ciklus $ciklus_studija limit $limit_studij uslov ".($limit_studij==-$ciklus_studija)." godina $godina<br>";


	// Ako se promijenio studij ili semestar, ispisujemo statistiku
	if (($studij_id != $old_studij_id || $plan_studija != $old_plan_studija || $semestar != $old_semestar) &&
		($old_studij_id!=-1 && $old_plan_studija != -1 && $old_semestar != -1)) {

		if ($prikaz=="po_studiju") {
			// Da li se uzima u obzir parametar "do uslova"?
			$pdouslova=0;
			if ($_REQUEST['douslova'] && $old_semestar%2==0)
//				if ($old_semestar != $semestara_na_ciklusu)
				// Uslov izbacujemo jer na završnom semestru postoji završni rad
					$pdouslova=1;

			// Ispisujemo podatke za svakog pojedinačnog studenta
			?>
			<p><b><?=$studij?>, <?=($old_semestar)?>. semestar</b></p>
			<table>
			<tr bgcolor="#CCCCCC"><td><b>Student</b></td><td><b>Broj nepoloženih / Broj parcijalnih</b></td><td><b>Nepoloženi predmeti</b></td><td><b>Parcijalni predmeti</b></td></tr>
			<?
			for ($i=0; $i<=$max_nepolozenih; $i++) {
				for ($j=0; $j<=$max_parcijalnih; $j++) {
					if ($limit_predmet>=0 && $limit_parcijalni>=0 && $i+$j != $limit_predmet+$limit_parcijalni+$pdouslova || $j<$limit_parcijalni) continue;
					if ($koliko_nepolozenih[$i][$j]>0) {
						print $ispis_nepolozenih[$i][$j];
					}
				}
			}
			$ukupno=0; $koliko_nepolozenih=array(); $max_nepolozenih=0; $ispis_nepolozenih=array();

			print "</table>\n";

		} else if ($prikaz=="sumarno") {
			?>
			<p><b><?=$studij?>, <?=$old_semestar?>. semestar</b></p>
			<table>
			<tr bgcolor="#CCCCCC"><td><b>Broj predmeta koji nisu položeni</b></td><td><b>Broj studenata</b></td></tr>
			<?

			for ($i=0; $i<=$max_nepolozenih; $i++) {
				if ($i==0) {
					?>
					<tr><td>Sve položeno</td><td><?=$koliko_nepolozenih[0][0]?> studenata</td></tr>
					<tr><td>Jedan parcijalni ili završni ispit</td><td><?=$koliko_nepolozenih[0][1]?> studenata</td></tr>
					<tr><td>Dva parcijalna ili završna ispita</td><td><?=$koliko_nepolozenih[0][2]?> studenata</td></tr>
					<?
				} else if ($i==1) {
					?>
					<tr><td>Jedan čitav predmet</td><td><?=$koliko_nepolozenih[1][0]?> studenata</td></tr>
					<tr><td>Jedan predmet i jedan parcijalni ili završni ispit</td><td><?=$koliko_nepolozenih[1][1]?> studenata</td></tr>
					<?
				} else if ($koliko_nepolozenih[$i][0]>0) {
					?>
					<tr><td><?=$i?>. predmeta</td><td><?=$koliko_nepolozenih[$i][0]?> studenata</td></tr>
					<?
				}
			}

			if ($old_semestar%2==0) {
				?>
				<tr><td>UKUPNO DALO USLOV:</td><td><?= ($koliko_nepolozenih[0][0]+$koliko_nepolozenih[0][1]+$koliko_nepolozenih[1][0])?> studenata</td></tr>
				<?
			}
			?>
			<tr><td>UKUPNO:</td><td><?=$ukupno?> studenata</td></tr>
			</table>
			<?
			$ukupno=0; $koliko_nepolozenih=array(); $max_nepolozenih=0;
		}
	}

	// Ostali parametri upita
	$student = $r20[0];
	$oldoldstudij=$oldstudij;
	$oldstudij = $studij;
	$studij = $r20[1];
	$imeprezime = $r20[4]." ".$r20[3];
	$brindexa = $r20[5];

	// Ako se studij promijenio, uzimamo novi plan studija
	if ($studij_id != $old_studij_id || $plan_studija != $old_plan_studija || $semestar != $old_semestar) {
		$plan_studija = $plan_studija_obavezan = array();

		for ($i=1; $i<10; $i++) // 10 - neće valjda biti više od 10 semestara? FIXME
			$plan_studija[$i] = $plan_studija_obavezan[$i] = array();

		$q30 = myquery("select semestar, predmet, obavezan from plan_studija where studij=$studij_id and godina_vazenja=$ss_plan_studija and semestar<=$semestar order by semestar");
		while ($r30 = mysql_fetch_row($q30)) {
			$plan_studija[$r30[0]][] = $r30[1];
			$plan_studija_obavezan[$r30[0]][] = $r30[2];
		}
	}

	$nepolozenih=0;
	$parcijalnih=0;
	$nepolozeni_predmet=array();
	$parcijalni_predmet=array();
	$pao_niza_godina=array();
	$ne_gledaj_predmet=0;

	// Koliko predmeta iz plana student nije polozio?
	for ($pssem=$semestar; $pssem>=0; $pssem--) {
		foreach ($plan_studija[$pssem] as $redni_broj => $predmet) {
			// Obavezan predmet
			if ($plan_studija_obavezan[$pssem][$redni_broj]==1) {
				$pao_predmet = pao_predmet($student, $predmet, $ak_god);
				if ($pao_predmet==1) {
					$parcijalnih++;
					$parcijalni_predmet[] = naziv_predmeta_cache($predmet);
				} else if ($pao_predmet!=0) {
					$nepolozenih++;
					$nepolozeni_predmet[] = naziv_predmeta_cache($predmet);
				}

				// Predmet sa niže godine
				if ($pao_predmet != 0 && ceil($pssem/2)<ceil($semestar/2))
					$pao_niza_godina[] = naziv_predmeta_cache($predmet);

			// Izborni predmet
			} else {
				$q50 = myquery("select predmet from izborni_slot where id=$predmet");
				$ijedan=false;
				$zapamti_parcijalni=$zapamti_izborni=0;
				while ($r50 = mysql_fetch_row($q50)) {
					if ($r50[0]==$ne_gledaj_predmet) continue;
					$pao_predmet = pao_predmet($student, $r50[0], $ak_god);
					if ($pao_predmet==0) {
						$ne_gledaj_predmet=$r50[0];
						$ijedan=true;
						break; 
					} else if ($pao_predmet==1) {
						$zapamti_parcijalni=$r50[0];
					} else if ($pao_predmet==2) {
						$zapamti_izborni=$r50[0];
					}
				}
				if (!$ijedan) {
					if ($zapamti_parcijalni>0) {
						$parcijalnih++;
						$parcijalni_predmet[] = naziv_predmeta_cache($zapamti_parcijalni);
					} else {
						$nepolozenih++;
						if ($zapamti_izborni>0)
							$nepolozeni_predmet[] = naziv_predmeta_cache($zapamti_izborni);
						else
							$nepolozeni_predmet[] = "[IZBORNI PREDMET]"; // Ne znamo koji
					}
				}
			}
		}
	}

	// Pretvaramo parcijalne predmete u nepoložene
//	while ($parcijalnih>2) { $parcijalnih--; $nepolozenih++; }
//	if ($parcijalnih==2 && $nepolozenih==1) { $nepolozenih=3; $parcijalnih=0; }
//	if ($nepolozenih>1) { $nepolozenih+=$parcijalnih; $parcijalnih=0; }


	$koliko_nepolozenih[$nepolozenih][$parcijalnih]++;
	if ($nepolozenih>$max_nepolozenih) $max_nepolozenih=$nepolozenih;
	if ($parcijalnih>$max_parcijalnih) $max_parcijalnih=$parcijalnih;
	$ukupno++;

	// Generišem ispis pojedinačnih studenata
	if ($prikaz=="po_studiju") {
		$ispis_nepolozenih[$nepolozenih][$parcijalnih] .= "<tr><td>$imeprezime ($brindexa)</td><td>$nepolozenih / $parcijalnih</td><td>";
		foreach ($nepolozeni_predmet as $np) $ispis_nepolozenih[$nepolozenih][$parcijalnih] .= $np;
		$ispis_nepolozenih[$nepolozenih][$parcijalnih] .= "</td><td>";
		foreach ($parcijalni_predmet as $np) $ispis_nepolozenih[$nepolozenih][$parcijalnih] .= $np;
		$ispis_nepolozenih[$nepolozenih][$parcijalnih] .= "</td><tr>\n";
	}

	if ($prikaz=="po_predmetu") {
		$pdouslova=0;
		if ($_REQUEST['douslova'] && $semestar%2==0)
			// Želimo vidjeti koliko je studenata kojim fali završni rad
			if ($semestar != $semestara_na_ciklusu)
				$pdouslova=1;

		// Preskacemo studenta ako 
		if ($limit_predmet>=0 && $limit_parcijalni>=0 && $nepolozenih+$parcijalnih != $limit_predmet+$limit_parcijalni+$pdouslova || $parcijalnih < $limit_parcijalni) continue;

		// Ako je limit jedan predmet i ima predmeta sa niže godine, taj predmet mora biti sa niže godine jer u suprotnom student nema uslov
		if ($limit_predmet+$limit_parcijalni == 1 && !empty($pao_niza_godina)) {
			$tmp = array();
			foreach ($nepolozeni_predmet as $np)
				if (in_array($np, $pao_niza_godina))
					$tmp[]=$np;
			$nepolozeni_predmet = $tmp;
			$tmp = array();
			foreach ($parcijalni_predmet as $np)
				if (in_array($np, $pao_niza_godina))
					$tmp[]=$np;
			$parcijalni_predmet = $tmp;
		}

		// Ako je limit 0 integralnih, ne mogu se polagati integralni odnosno moraju se ostaviti za uslov
		if ($limit_predmet>0) { 
			foreach ($nepolozeni_predmet as $np) {
				if ($np == "[IZBORNI PREDMET]") continue;
				$ispis_predmeta[$np] .= "$imeprezime ($brindexa)<br/>";
			}
		}

		foreach ($parcijalni_predmet as $np) {
			if ($np == "[IZBORNI PREDMET]") continue;
			$ispis_predmeta[$np] .= "$imeprezime ($brindexa)<br/>";
		}
	}
}


// Zavrsni ispis

if ($prikaz=="po_studiju") {
	// Ispisujemo podatke za svakog pojedinačnog studenta
	?>
	<p><b><?=$studij?>, <?=$old_semestar?>. semestar</b></p>
	<table>
	<tr bgcolor="#CCCCCC"><td><b>Student</b></td><td><b>Broj nepoloženih / Broj parcijalnih</b></td><td><b>Nepoloženi predmeti</b></td><td><b>Parcijalni predmeti</b></td></tr>
	<?
	for ($i=0; $i<=$max_nepolozenih; $i++) {
		for ($j=0; $j<=$max_parcijalnih; $j++) {
			if ($limit_predmet>=0 && $limit_parcijalni>=0 && $i+$j != $limit_predmet+$limit_parcijalni+$douslova || $j<$limit_parcijalni) continue;
			if ($koliko_nepolozenih[$i][$j]>0) {
				print $ispis_nepolozenih[$i][$j];
			}
		}
	}
	$ukupno=0; $koliko_nepolozenih=array(); $max_nepolozenih=0; $ispis_nepolozenih=array();
	print "</table>\n";


} else if ($prikaz=="sumarno") {
	?>
	<p><b><?=$studij?>, <?=$old_semestar?>. semestar</b></p>
	<table>
	<tr bgcolor="#CCCCCC"><td><b>Broj predmeta koji nisu položeni</b></td><td><b>Broj studenata</b></td></tr>
	<?

	for ($i=0; $i<=$max_nepolozenih; $i++) {
		if ($i==0) {
			?>
			<tr><td>Sve položeno</td><td><?=$koliko_nepolozenih[0][0]?> studenata</td></tr>
			<tr><td>Jedan parcijalni ili završni ispit</td><td><?=$koliko_nepolozenih[0][1]?> studenata</td></tr>
			<tr><td>Dva parcijalna ili završna ispita</td><td><?=$koliko_nepolozenih[0][2]?> studenata</td></tr>
			<?
		} else if ($i==1) {
			?>
			<tr><td>Jedan čitav predmet</td><td><?=$koliko_nepolozenih[1][0]?> studenata</td></tr>
			<tr><td>Jedan predmet i jedan parcijalni ili završni ispit</td><td><?=$koliko_nepolozenih[1][1]?> studenata</td></tr>
			<?
		} else if ($koliko_nepolozenih[$i][0]>0) {
			?>
			<tr><td><?=$i?>. predmeta</td><td><?=$koliko_nepolozenih[$i][0]?> studenata</td></tr>
			<?
		}
	}

	if ($old_semestar%2==0) {
		?>
		<tr><td>UKUPNO DALO USLOV:</td><td><?= ($koliko_nepolozenih[0][0]+$koliko_nepolozenih[0][1]+$koliko_nepolozenih[1][0])?> studenata</td></tr>
		<?
	}
	?>
	<tr><td>UKUPNO:</td><td><?=$ukupno?> studenata</td></tr>
	</table>
	<?
	$ukupno=0; $koliko_nepolozenih=array(); $max_nepolozenih=0;

} else if ($prikaz == "po_predmetu") {
	foreach ($ispis_predmeta as $ime => $ispis) {
		print "<b>$ime</b>\n<br/>\n$ispis\n<br/><br/>\n";
	}
}



return 0;

} // if ($varijanta==1)









// Prva varijanta izvjestaja

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
if ($sort_po_predmetu)
	$q20 = myquery("select ss.student,s.naziv,ss.semestar,o.ime,o.prezime, o.brindexa, s.id from student_studij as ss, studij as s, osoba as o where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=0 and ss.student=o.id order by o.prezime, o.ime, ss.studij, ss.semestar");
else
	$q20 = myquery("select ss.student,s.naziv,ss.semestar,o.ime,o.prezime, o.brindexa from student_studij as ss, studij as s, osoba as o where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=0 and ss.student=o.id order by ss.studij, ss.semestar, o.prezime, o.ime");

if (mysql_num_rows($q20)==0) 
	// Nema nikog u parnom semestru, probavamo neparni
	$q20 = myquery("select ss.student,s.naziv,ss.semestar,o.ime,o.prezime from student_studij as ss, studij as s, osoba as o where ss.akademska_godina=$ak_god and ss.studij=s.id and ss.semestar%2=1 and ss.student=o.id order by ss.studij, ss.semestar, o.prezime, o.ime");

while($r20 = mysql_fetch_row($q20)) {
	$student = $r20[0];
	$studij = $r20[1];
	$semestar = $r20[2];
	if ($semestar%2==1) $semestar++;
	$imeprezime = $r20[4]." ".$r20[3];
	$brindexa = $r20[5];
	$studij_id = $r20[6];

	$ects_uslov = 7.5; // Maksimalan broj ECTS bodova koji se mogu prenijeti
	if ($semestar == 6) $ects_uslov=12; // Zavrsni rad :(
	//if ($semestar == 4) $ects_uslov=0; // Ne moze se prenijeti 2->3 godina


	// Svi predmeti koje je ikada slusao
	$q30 = myquery("select pk.predmet, p.ects, pk.semestar, p.naziv, pk.obavezan, pk.studij from ponudakursa as pk, student_predmet as sp, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.semestar<=$semestar and pk.predmet=p.id order by pk.akademska_godina desc");
	$predmeti_pao=array();
	$ects_pao=array();
	$ects_suma=0;
	while ($r30 = mysql_fetch_row($q30)) {
		if ($r30[5]!=$studij_id) continue; // preskacemo predmete sa drugog studija
		if ($r30[4]==0) continue; // preskacemo izborne predmete jer ovo ne moze raditi
		$q40 = myquery("select count(*) from konacna_ocjena where student=$student and predmet=$r30[0]");
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
				<tr><td>R. br.</td><td>Ime i prezime</td><td>Broj indexa</td><td>Prenosi predmet?</td></tr>
				<?
				$oldstudij=$studij;
				$oldsemestar=$semestar;
			}
			if ($ects_suma==0) $ispis="NE"; else $ispis="DA";

			?><tr><td><?=($rbr++)?></td><td><?=$imeprezime?></td><td><?=$brindexa?></td><td><?=$ispis?></td></tr><?
			
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
					$total_ispis[$predmet] .= "<tr><td>".$counter[$predmet]."</td><td>$imeprezime</td><td>$brindexa</td></tr>\n";
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
				$q50 = myquery("select count(*) from ispitocjene as io, ispit as i where io.ispit=i.id and io.student=$student and io.ocjena>=10 and i.predmet=$predmet and (i.komponenta=1 or i.komponenta=2)");
				if (mysql_result($q50,0,0)==0) {
					// Integralni?
					$q60 = myquery("select count(*) from ispitocjene as io, ispit as i where io.ispit=i.id and io.student=$student and io.ocjena>=20 and i.predmet=$predmet and i.komponenta=3");
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
				$total_ispis[$predmet] .= "<tr><td>".$counter[$predmet]."</td><td>$imeprezime</td><td>$brindexa</td></tr>\n";
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
			<tr><td>R. br.</td><td>Ime i prezime</td><td>Broj indexa</td><td>Nedostajući predmet(i)</td></tr>
			<?
			$oldstudij=$studij;
			$oldsemestar=$semestar;
		}

		?><tr><td><?=($rbr++)?></td><td><?=$imeprezime?></td><td><?=$brindexa?></td><td><?=$ispis?></td></tr><?
	}
}


// Ispis sortiran po predmetu

if ($sort_po_predmetu==1) {

	$q1000 = myquery("select p.id, p.naziv, s.kratkinaziv, pk.semestar, p.ects from ponudakursa as pk, predmet as p, studij as s where pk.predmet=p.id and pk.akademska_godina=$ak_god and pk.studij=s.id order by s.id, pk.semestar, p.naziv");
	while ($r1000 = mysql_fetch_row($q1000)) {
		if ($r1000[4]==12) continue; // ignorišemo završni rad

		$ispis = $total_ispis[$r1000[0]];
		if ($ispis=="") continue; // predmeti bez graničnih slučajeva
		$total_ispis[$r1000[0]]=""; // da se ne bi ponavljali predmeti koji se nude na više mjesta...

		?>
		<h3><?=$predmeti_naziv[$r1000[0]]?> (<?=$r1000[2]?>, <?=$r1000[3]?>. semestar)</h3>
		<table border="1"><tr><td>R. br.</td><td>Ime i prezime</td><td>Broj indexa</td></tr>
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
