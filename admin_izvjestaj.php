<?

// v3.0.1.1 (2007/09/20) + Novi modul "Izvjestaj" - izdvojen iz admin_nihada; dodan izvjestaj "grupe"
// v3.0.1.2 (2007/09/25) + Dodan izvjestaj "predmet_full"; optimizacija racunanja bodova na ispitima
// v3.0.1.3 (2007/10/09) + Dodan izvjestaj "prolaznost"; nova struktura baze za predmete; sortiraj grupe po IDu
// v3.0.1.4 (2007/10/19) + Nova shema tabele ispita
// v3.0.1.5 (2007/10/20) + Razdvojen izvjestaj "grupe" i "grupedouble" (u jednoj i dvije kolone); u izvjestaj "grupe" dodan ispis komentara
// v3.0.1.6 (2007/10/24) + Dovrsen izvjestaj "prolaznost"
// v3.0.1.7 (2007/11/02) + Dodana kolona za konacnu ocjenu u predmet_full
// v3.0.1.8 (2007/11/19) + Izvjestaj "ispit" - statistika pojedinacnog ispita
// v3.0.1.9 (2007/12/10) + U izvjestaj "ispit" dodan sumarni izvjestaj za sve ispite na predmetu
// v3.0.1.10 (2007/12/22) + Nastavak rada na izvjestaju "prolaznost", optimizacije itd.
// v3.0.1.11 (2008/01/17) + Ljepse zaglavlje za "prolaznost", dodan podizvjestaj o konacnim ocjenama
// v3.0.1.12 (2008/01/19) + Dodana skracena verzija izvjestaja "predmet_full"; boldovana kolona "ukupno", ubacene kose crte za ispite na koje student nije izasao


function admin_izvjestaj() {

global $userid;


$imena_ocjena = array("Nije položio/la", "Šest","Sedam","Osam","Devet","Deset");
$tip = $_REQUEST['tip']; // tip izvjestaja
$student = intval($_REQUEST['student']);
$predmet = intval($_REQUEST['predmet']);


// Uspostava permisija

$q1 = myquery("select siteadmin from nastavnik where id=$userid");
if (mysql_num_rows($q1) < 1) {
	niceerror("Nepoznat user ID");	// ne bi se trebalo desiti
	return;
}
$siteadmin = mysql_result($q1,0,0);
	// LEGENDA: 2 = site admin, 1 = studentska služba, 0 = nastavnik

$predmetadmin = "-1";
if ($predmet>0) {
	$q2 = myquery("select admin from nastavnik_predmet where nastavnik=$userid and predmet=$predmet");
	if (mysql_num_rows($q2) > 0)
		$predmetadmin = mysql_result($q2,0,0);
}
	// LEGENDA: -1 = nije na predmetu, 0 = jeste na predmetu, 1 = admin predmeta

?>
<html>
<head>
	<title>Izvještaji</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body bgcolor="#FFFFFF">
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?


// INDEX - Spisak položenih predmeta sa ocjenama

if ($tip == "index") {
	if ($siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	// Podaci o studentu
	$q100 = myquery("select ime,prezime,brindexa from student where id=$student");
	if (!($r100 = mysql_fetch_row($q100))) {
		niceerror("Student se ne nalazi u bazi podataka.");
		return;
	}
	print "<p>&nbsp;</br>Student:</br><h1>$r100[0] $r100[1]</h1><br/>\nBroj indeksa: $r100[2]<br/><br/><br/>\n";

	?><p><b>Pregled položenih predmeta sa ocjenama</b></p>
	<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
		<td width="20">&nbsp;</td>
		<td width="330">Naziv predmeta</td>
		<td width="200">Akademska godina</td>
		<td width="150">Konačna ocjena</td>
	</tr>
	<?
	$i=1;
	$q101 = myquery("select id,naziv from akademska_godina order by naziv");
	while ($r101 = mysql_fetch_row($q101)) {
		$q102 = myquery("select p.naziv,k.ocjena from konacna_ocjena as k,predmet as p, ponudakursa as pk where k.student=$student and k.predmet=pk.id and pk.akademska_godina=$r101[0] and pk.predmet=p.naziv order by p.naziv");
		while ($r102 = mysql_fetch_row($q102)) {
			print "<tr><td>".($i++)."</td><td>".$r102[0]."</td><td>".$r101[1]."</td><td>".$r102[1]." (".$imena_ocjena[$r102[1]-5].")</td></tr>";
		}
	}
	print "</table>";
}


// PROGRESS - Pregled svih predmeta koje je student slušao ili sluša sa bodovima

if ($tip == "progress") {
	if ($siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	$razdvoji = intval($_REQUEST['razdvoji_ispite']); // da li prikazivati nepoložene pokušaje ispita

	// Podaci o studentu
	$q100 = myquery("select ime,prezime,brindexa from student where id=$student");
	if (!($r100 = mysql_fetch_row($q100))) {
		niceerror("Student se ne nalazi u bazi podataka.");
		return;
	}
	print "<p>&nbsp;</br>Student:</br><h1>$r100[0] $r100[1]</h1><br/>\nBroj indeksa: $r100[2]<br/><br/><br/>\n";

	?><p><b>Pregled ostvarenog rezultata na predmetima</b></p>
	<table width="775" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
		<td width="20">&nbsp;</td>
		<td width="155">Predmet</td>
		<td width="75">Ak. godina</td>
		<td width="75">Prisustvo</td>
		<td width="75">Zadaće</td>
		<td width="75">I parcijalni</td>
		<td width="75">II parcijalni</td>
		<td width="75">Integralni</td>
		<td width="75">UKUPNO</td>
		<td width="75">Ocjena</td>
	</tr>
	<?
	$rbr=1;
	$q310 = myquery("select id,naziv from akademska_godina order by naziv");
	while ($r310 = mysql_fetch_row($q310)) {
		$q311 = myquery("select pk.id, p.naziv, l.id from predmet as p, ponudakursa as pk, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=$r310[0] and pk.predmet=p.id order by p.naziv");
		while ($r311 = mysql_fetch_row($q311)) {
			print "<tr><td>".($rbr++)."</td><td>".$r311[1]."</td><td>".$r310[1]."</td>";
			$ukupno=0;

			$q312 = myquery("select count(*) from prisustvo as p,cas as c where p.student=$student and p.cas=c.id and c.labgrupa=$r311[2] and p.prisutan=0");
			if (mysql_result($q312,0,0)<=3) {
				print "<td>10</td>";
				$ukupno += 10;
			} else
				print "<td>0</td>";

			$q313 = myquery("select id, zadataka from zadaca where predmet=$r311[0]");
			$zadaca=0;
			while ($r313 = mysql_fetch_row($q313)) {
				for ($i=1; $i<=$r313[1]; $i++) {
					$q314 = myquery("select status,bodova from zadatak where zadaca=$r313[0] and redni_broj=$i and student=$student order by id desc limit 1");
					if ($r314 = mysql_fetch_row($q314))
						if ($r314[0] == 5)
							$zadaca += $r314[1];
				}
			}
			print "<td>$zadaca</td>";
			$ukupno += $zadaca;

			$q315 = myquery("select io.ocjena,i.tipispita,i.datum from ispitocjene as io, ispit as i where io.student=$student and io.ispit=i.id  and i.predmet=$r311[0] order by i.datum");

			$ispis = array();
			$ispis[1] = $ispis[2] = $ispis[3] = "";
			$max = array();
			$max[1] = $max[2] = $max[3] = "&nbsp;";
			
			if (mysql_num_rows($q315)>0) {
				while ($r315 = mysql_fetch_row($q315)) {
					if ($r315[0] == -1) continue; // skip
					if ($razdvoji == 1) {
						list ($g,$m,$d) = explode("-",$r315[2]);
						$ispis[$r315[1]] .= "$r315[0] ($d.$m.)<br/>";
					}
					if ($r315[0]>$max[$r315[1]])
						$max[$r315[1]]=$r315[0];
				}
				if ($max[3] > ($max[1]+$max[2]))
					$ukupno += $max[3];
				else
					$ukupno += ($max[1] + $max[2]);
			}

			if ($razdvoji == 0) {
				print "<td>$max[1]</td><td>$max[2]</td><td>$max[3]</td>\n";
			} else {
				for ($i=1; $i<4; $i++)
					if ($ispis[$i] == "")
						print "<td>&nbsp;</td>\n";
					else
						print "<td>".$ispis[$i]."</td>\n";
			}

			print "<td>$ukupno</td>";

			$q317 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$r311[0]");
			if ($r317 = mysql_fetch_row($q317))
				if ($r317[0] > 5)
					print "<td>$r317[0] (".$imena_ocjena[$r317[0]-5].")</td>";
				else
					print "<td>5 (".$imena_ocjena(0).")</td>";
			else
				print "<td>Nije ocijenjen</td>";

			print "</tr>";
		}
	}
	print "</table>";
}



// GRUPE - Spisak studenata po grupama

if ($tip == "grupedouble") {
	if ($predmetadmin == -1 && $siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	$q399 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
	print "<p>&nbsp;</p><h1>".mysql_result($q399,0,0)."</h1><p>Spisak grupa:</p>\n";
	print '<table width="100%" border="0">'."\n";

	$q400 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id");
	$parni=0;
	while ($r400 = mysql_fetch_row($q400)) {
		if ($parni == 0) 
			print "<tr>";
		else
			print "</td>";
		?>
		<td width="13%">&nbsp;</td><td width="30%" valign="top">
			<table width="100%" border="2" cellspacing="0">
				<tr><td colspan="2"><b><?=$r400[1]?></b></td></tr>
				<tr><td>
		<?

		$imeprezime=array();
		$brindexa=array();
		$q401 = myquery("select s.id, s.prezime, s.ime, s.brindexa from student as s, student_labgrupa as sl where sl.labgrupa=$r400[0] and sl.student=s.id");
		while ($r401 = mysql_fetch_row($q401)) {
			$imeprezime[$r401[0]] = "$r401[1] $r401[2]";
			$brindexa[$r401[0]] = $r401[3];
		}
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			print "$n. $stud_imepr<br/>";
			$n++;
		}
		print "</td><td>";
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			print $brindexa[$stud_id]."<br/>";
		}
		print "</td></tr></table>";

		if ($parni==1) {
			$parni=0;
			?>
		</td><td width="13%">&nbsp;</td></tr>
		<tr><td colspan="5">&nbsp;</td></tr>
			<?
		} else $parni=1;
	}
}

if ($tip == "grupe") {
	if ($predmetadmin == -1 && $siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	$komentari = intval($_REQUEST['komentari']);
	if ($komentari==0) $nr=3; else $nr=4;

	$q399 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
	print "<p>&nbsp;</p><h1>".mysql_result($q399,0,0)."</h1><p>Spisak grupa:</p>\n";

	?>
	<table width="100%" border="0"><tr>
		<td width="20%">&nbsp;</td>
		<td width="60%">
	<?

	$q400 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id");
	while ($r400 = mysql_fetch_row($q400)) {
		?>
			<table width="100%" border="2" cellspacing="0">
				<tr><td colspan="<?=$nr?>"><b><?=$r400[1]?></b></td></tr>
				<tr><td>&nbsp;</td><td>Prezime i ime</td><td>Br. indeksa</td>
		<?
		if ($komentari>0) print "<td>Komentari</td>";
		print "</tr>\n";

		$imeprezime=array();
		$brindexa=array();
		$komentar=array();
		$q401 = myquery("select s.id, s.prezime, s.ime, s.brindexa from student as s, student_labgrupa as sl where sl.labgrupa=$r400[0] and sl.student=s.id");
		while ($r401 = mysql_fetch_row($q401)) {
			$imeprezime[$r401[0]] = "$r401[1] $r401[2]";
			$brindexa[$r401[0]] = $r401[3];
			if ($komentari>0) {
				$q402 = myquery("select UNIX_TIMESTAMP(datum),komentar from komentar where student=$r401[0] and labgrupa=$r400[0] order by id");
				$i=0;
				while ($r402 = mysql_fetch_row($q402)) {
					if ($i>0) $komentar[$r401[0]] .= "<br/>\n";
					$i=1;
					$komentar[$r401[0]] .= "(".date("d. m. Y.",$r402[0]).") ".$r402[1];
				}
				if (mysql_num_rows($r402)<1) $komentar[$r401[0]] .= "&nbsp;";
			}
		}
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik

		$n=1;
		foreach($imeprezime as $stud_id => $stud_imepr) {
			?>
				<tr>
					<td><?=$n++?></td>
					<td><?=$stud_imepr?></td>
					<td><?=$brindexa[$stud_id]?></td>
			<?
			if ($komentari>0) {
				print "<td>".$komentar[$stud_id]."</td>\n";
			}
			print "</tr>\n";
		}

		?>
				<!--/table></td></tr-->
			</table>
			<p>&nbsp;</p>
		<?
	}
	?>
		</td>
		<td width="20%">&nbsp;</td>
	</tr></table>
	<?
}



// PREDMET_FULL - izvjestaj koji profesori salju Nihadi

if ($tip == "predmet_full") {
	if ($predmetadmin == -1 && $siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	$skrati = intval($_REQUEST['skrati']);

	$q500 = myquery("select p.naziv,ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag where pk.id=$predmet and ag.id=pk.akademska_godina and pk.predmet=p.id");
	print "<p>&nbsp;</p><h1>".mysql_result($q500,0,0)." ".mysql_result($q500,0,1)."</h1>\n";

	$grupa = intval($_REQUEST['grupa']);
	if ($grupa>0)
		$q501 = myquery("select id,naziv from labgrupa where predmet=$predmet and id=$grupa");
	else
		$q501 = myquery("select id,naziv from labgrupa where predmet=$predmet order by id");

	while ($r501 = mysql_fetch_row($q501)) {
		?>
		<table <? if ($skrati!=1) { ?> width="100%"<? } ?> border="2" cellspacing="0" cellpadding="2">
			<tr><td colspan="<? if ($skrati!=1) print "28"; else print "9"; ?>" align="center"><b><?=strtoupper($r501[1])?></b></td></tr>
			<tr><td align="center">R.br.</td>
				<td align="center">Br. indexa</td>
				<td align="center">Prezime i ime</td><? if ($skrati!=1) { ?>
				<td colspan="7" align="center">Prisustvo tutorijalima 1</td>
				<td colspan="7" align="center">Prisustvo tutorijalima 2</td>
				<td colspan="5" align="center">Zadaće i lab vježbe</td><? } ?>
				<td align="center">I parc.</td>
				<td align="center">II parc.</td>
				<td align="center">Prisustvo</td>
				<td align="center">Zadaće</td>
				<td align="center"><b>Ukupno</b></td>
				<td align="center">Ocjena</td>
			</tr>
		<?

		// Ucitavamo studente u array radi sortiranja
		$imeprezime=array();
		$brindexa=array();
		$q502 = myquery("select s.id, s.prezime, s.ime, s.brindexa from student as s, student_labgrupa as sl where sl.labgrupa=$r501[0] and sl.student=s.id");
		while ($r502 = mysql_fetch_row($q502)) {
			$imeprezime[$r502[0]] = "$r502[1] $r502[2]";
			$brindexa[$r502[0]] = $r502[3];
		}
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik

		$redni_broj=0;

		// Ucitavamo casove i zadace u array, radi brzeg kasnijeg referenciranja
		$casoviar = array();
		$q503 = myquery("select id from cas where labgrupa=$r501[0] order by datum");
		while ($r503 = mysql_fetch_row($q503))
			array_push($casoviar, $r503[0]);

		$zadacear = array();
		$q504 = myquery("select id,zadataka from zadaca where predmet=$predmet order by id");
		while ($r504 = mysql_fetch_row($q504)) {
			$zadacear[$r504[0]] = $r504[1];
		}

		foreach ($imeprezime as $stud_id => $stud_imepr) {
			$redni_broj++;
			?>
			<tr>
				<td><?=$redni_broj?>.</td>
				<td><?=$brindexa[$stud_id]?></td>
				<td><?=$stud_imepr?></td>
			<?

			$n = 0;
			foreach ($casoviar as $cas) {
				$q505 = myquery("select prisutan from prisustvo where student=$stud_id and cas=$cas");

				if (mysql_num_rows($q505)<1) {
					if ($skrati!=1) print "<td>/</td>\n";
				} else if (mysql_result($q505,0,0) == 0) {
					if ($skrati!=1) print "<td>0</td>\n";
					$n++;
				} else {
					if ($skrati!=1) print "<td>1</td>\n";
				}
			}
			if ($skrati!=1)
				for ($i=count($casoviar); $i<14; $i++) {
					print "<td>&nbsp;</td>\n";
				}
			if ($n>3) $prisustvo=0; else $prisustvo=10;
			
			// Jos jedan array...... optimizacija
			$q506 = myquery("select z.id,zc.id,z.redni_broj,z.status,z.bodova from zadatak as z, zadaca as zc where z.zadaca=zc.id and z.student=$stud_id and zc.predmet=$predmet order by z.id desc");
			$bilo = array();
			$bodova = array();
			while ($r506 = mysql_fetch_row($q506)) {
				$zadaca_rbr = "$r506[1]-$r506[2]";
				if ($bilo[$zadaca_rbr] != 1) {
					$bilo[$zadaca_rbr] = 1;
					if ($r506[3] == 5)
						$bodova[$r506[1]] += $r506[4];
				}
			}

			$zadace=0;
			foreach ($zadacear as $zid => $zadataka) {
				if ($bodova[$zid]) {
					if ($skrati!=1) print "<td>".$bodova[$zid]."</td>";
					$zadace += $bodova[$zid];
				} else {
					if ($skrati!=1) print "<td>&nbsp;</td>";
				}
			}
			if ($skrati!=1) 
				for  ($i=count($zadacear); $i<5; $i++)
					print "<td>&nbsp;</td>\n";


			// Ispiti
			$parc1 = $parc2 = "/";
			$integralni = 0;

			$q507 = myquery("select io.ocjena, i.tipispita from ispitocjene as io, ispit as i where io.student=$stud_id and io.ispit=i.id and i.predmet=$predmet order by i.datum");
			while ($r507 = mysql_fetch_row($q507)) {
				if ($r507[1]==1 && $r507[0] > $parc1 && $r507[0] != "-1")
					$parc1=$r507[0];
				if ($r507[1]==2 && $r507[0] > $parc2 && $r507[0] != "-1")
					$parc2=$r507[0];
				if ($r507[1]==3 && $r507[0] > $integralni && $r507[0] != "-1") 
					$integralni=$r507[0];
			}

			$total = $prisustvo+$zadace;

			if ($integralni > ($parc1+$parc2)) {
				print "<td colspan=\"2\" align=\"center\">$integralni</td>\n";
				$total += $integralni;
			} else {
				print "<td>$parc1</td><td>$parc2</td>\n";
				$total += ($parc1 + $parc2);
			}

			print "<td>$prisustvo</td>";
			print "<td>$zadace</td>";
			print "<td><b>$total</b></td>";

			// Konacna ocjena
			$q508 = myquery("select ocjena from konacna_ocjena where student=$stud_id and predmet=$predmet");
			if (mysql_num_rows($q508)>0) {
				print "<td>".mysql_result($q508,0,0)."</td>\n";
			} else {
				print "<td>/</td>\n";
			}

			print "</tr>\n";
		}
		print "</table><p>&nbsp;</p>";

	}
	
}



// PROLAZNOST - izvjestaj koji Nihada daje NNVu

if ($tip == "prolaznost") {
	if ($siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	// parametri izvjestaja
	$akgod = intval($_REQUEST['_lv_column_akademska_godina']);
	$studij = intval($_REQUEST['_lv_column_studij']);
	$period = intval($_REQUEST['period']);
	$semestar = intval($_REQUEST['semestar']);
	$godina = intval($_REQUEST['godina']);
	$ispit = intval($_REQUEST['ispit']);
	$cista_gen = intval($_REQUEST['cista_gen']);
	$studenti = intval($_REQUEST['studenti']);
	$sortiranje = intval($_REQUEST['sortiranje']);

	// Naslov
	print "<h2>Prolaznost</h2>\n";
	$q598 = myquery("select naziv from studij where id=$studij");
	$q599 = myquery("select naziv from akademska_godina where id=$akgod");

	?><p>Studij: <b><?=mysql_result($q598,0,0)?></b><br/>
	Akademska godina: <b><?=mysql_result($q599,0,0)?></b><br/>
	Godina/semestar studija: <b><?
	if ($period==0) {
		if ($semestar==0) $semestar=1;
		print "$semestar. semestar";
	} else {
		if ($godina==0) $godina=1;
		print "$godina. godina, ";
	}
	?></b><br/>
	Obuhvaćeni studenti: <b><?
	if ($cista_gen==0) print "Redovni, Ponovci, Preneseni predmeti";
	elseif ($cista_gen==1) print "Redovni, Ponovci";
	elseif ($cista_gen==2) print "Redovni studenti";
	elseif ($cista_gen==3) print "Čista generacija";?></b><br/><br/>
	Vrsta izvještaja: <b><?
	if ($ispit==1) print "I parcijalni ispit";
	elseif ($ispit==2) print "II parcijalni ispit";
	elseif ($ispit==3) print "Ukupni bodovi";
	elseif ($ispit==4) print "Konačna ocjena";
	?></b><br/>
	</p><?


	// Spisak predmeta na studij-semestru
	if ($period==0) {
		$semestar_upit = "pk.semestar=$semestar";
		$sem_stud_upit = "semestar=$semestar";
	} else {
		$semestar_upit = "(pk.semestar=".($godina*2-1)." or pk.semestar=".($godina*2).")";
		$sem_stud_upit = "semestar=".($godina*2-1); // blazi kriterij za studente koji slusaju
	}
	$q600 = myquery("select pk.id, p.naziv, pk.obavezan from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit order by pk.obavezan desc, p.naziv");

	// Dodatak upitu za studente
	$upit_studenti="";
	if ($cista_gen>=1) {
		$upit_studenti="and ss.studij=$studij and ss.$sem_stud_upit and ss.akademska_godina=$akgod";
	}
	if ($cista_gen==2) {
		$upit_studenti .= " and (select count(*) from student_studij as ss2 where ss2.student=io.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0";
	}

	// PODIZVJESTAJ 1: Statistika za ispit, bez pojedinacnih studenata
	// 1 = I parc., 2 = II parc.
	if ($studenti==0 && ($ispit == 1 || $ispit == 2)) {
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><td><b>Predmet</b></td>
			<td><b>Izašlo</b></td>
			<td><b>Položilo</b></td>
			<td><b>%</b></td>
		</tr><?
		while ($r600 = mysql_fetch_row($q600)) {
			$naziv = $r600[1];
			if ($r600[2]==0) $naziv .= " *";
			$q601 = myquery("select count(distinct io.student) from ispitocjene as io, ispit as i, student as s, student_studij as ss where io.student=s.id and ss.student=s.id and io.ispit=i.id and i.predmet=$r600[0] and i.tipispita=$ispit $upit_studenti");
			$izaslo=mysql_result($q601,0,0);
			$q602 = myquery("select count(distinct io.student) from ispitocjene as io, ispit as i, student as s, student_studij as ss where io.student=s.id and ss.student=s.id and io.ispit=i.id and i.predmet=$r600[0] and i.tipispita=$ispit and io.ocjena>=10 $upit_studenti");
			$polozilo=mysql_result($q602,0,0);
			if ($izaslo>0) {
				$procenat = intval($polozilo/$izaslo*10000)/100;
				$procenat = "$procenat %";
			} else {
				$procenat = "-";
			}
			?><tr><td><?=$naziv?></td>
			<td><?=$izaslo?></td>
			<td><?=$polozilo?></td>
			<td><?=$procenat?></td>
			</tr><?
		}
		print "</table>\n* Predmet je izborni<br/>\n";

		// Sumarni podaci
		if ($period==0) {
			$sem2_upit = "semestar=$semestar";
		} else {
			$sem2_upit = "semestar=".($godina*2-1);
		}

		$q603 = myquery("select count(distinct io.student) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and i.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");
		$uk_studenata = mysql_result($q603,0,0);
		print "<p>Na ispite izašlo ukupno: <b>$uk_studenata</b> studenata<br/>";

		if ($cista_gen==0) {

//			$q604 = myquery("");
//			while ($r604 = mysql_fetch_row($q604)) {
//				$polozilo = $r604[0];
//				$procenat = intval($polozilo/$uk_studenata*10000)/100;
//				print "Položilo $r604[1] ispita: <b>$polozilo</b> studenata ($procenat%)<br/>";
//			}

		} else if ($cista_gen==1) {
			$q603 = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem2_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem2_upit and ss2.akademska_godina<$akgod)=0");
			$uk2_studenata = mysql_result($q603,0,0);

			$q603 = myquery("select count(*) from student_studij where studij=$studij and akademska_godina=$akgod and $sem2_upit");
			$uk_studenata = mysql_result($q603,0,0);
			print "<p>Semestar upisalo: <b>$uk2_studenata</b> redovnih studenata + <b>".($uk_studenata-$uk2_studenata)."</b> ponovaca<br/>";

			$q604 = myquery("select count(*),(select count(*) from ispitocjene as io, ispit as i, ponudakursa as pk where io.student=ss.student and io.ocjena>=10 and io.ispit=i.id and i.tipispita=$ispit and i.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit) as broj_ispita from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem2_upit group by broj_ispita order by broj_ispita desc");
			while ($r604 = mysql_fetch_row($q604)) {
				$polozilo = $r604[0];
				$procenat = intval($polozilo/$uk_studenata*10000)/100;
				print "Položilo $r604[1] ispita: <b>$polozilo</b> studenata ($procenat%)<br/>";
			}

		} else if ($cista_gen==2) {
			$q603 = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem2_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem2_upit and ss2.akademska_godina<$akgod)=0");
			$uk_studenata = mysql_result($q603,0,0);
			print "<p>Semestar upisalo: <b>$uk_studenata</b> studenata<br/>";

			$q604 = myquery("select count(*),(select count(*) from ispitocjene as io, ispit as i, ponudakursa as pk where io.student=ss.student and io.ocjena>=10 and io.ispit=i.id and i.tipispita=$ispit and i.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit) as broj_ispita from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem2_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem2_upit and ss2.akademska_godina<$akgod)=0 group by broj_ispita order by broj_ispita desc");
			while ($r604 = mysql_fetch_row($q604)) {
				$polozilo = $r604[0];
				$procenat = intval($polozilo/$uk_studenata*10000)/100;
				print "Položilo $r604[1] ispita: <b>$polozilo</b> studenata ($procenat%)<br/>";
			}
		}
	}


	// PODIZVJESTAJ 2: Ukupan zbir bodova, bez pojedinacnih studenata
	else if ($studenti==0 && $ispit == 3) {
		// Ovo će biti komplikovano....
	}



	// PODIZVJESTAJ 3: Konacna ocjena, bez pojedinacnih studenata
	else if ($studenti==0 && $ispit == 4) {
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><td><b>Predmet</b></td>
			<td><b>Deset (10)</b></td>
			<td><b>%</b></td>
			<td><b>Devet (9)</b></td>
			<td><b>%</b></td>
			<td><b>Osam (8)</b></td>
			<td><b>%</b></td>
			<td><b>Sedam (7)</b></td>
			<td><b>%</b></td>
			<td><b>Šest (6)</b></td>
			<td><b>%</b></td>
			<td><b>Nisu položili</b></td>
			<td><b>%</b></td>
		</tr><?

		// Ukupan broj studenata na semestru/odsjeku
		if ($period==0) {
			$sem2_upit = "semestar=$semestar";
		} else {
			$sem2_upit = "semestar=".($godina*2-1);
		}

		if ($cista_gen==0) {
			$q603 = myquery("select count(distinct io.student) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and i.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");
			$uk_studenata = mysql_result($q603,0,0);
		} else if ($cista_gen==1) {
			$q603 = myquery("select count(distinct io.student) from ispitocjene as io, ispit as i, ponudakursa as pk where io.ispit=i.id and i.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");
			$uk_studenata = mysql_result($q603,0,0);
			// ?? isto kao gore
		} else if ($cista_gen==2) {
			$q603 = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem2_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem2_upit and ss2.akademska_godina<$akgod)=0");
			$uk_studenata = mysql_result($q603,0,0);
		}

		while ($r600 = mysql_fetch_row($q600)) {
			$naziv = $r600[1];
			if ($r600[2]==0) $naziv .= " *";
			$q630 = myquery("select ocjena,count(*) from konacna_ocjena where predmet=$r600[0] group by ocjena order by ocjena");
			$ocjene = array();
			for ($i=6; $i<=10; $i++) $ocjene[$i]=0;
			while ($r630 = mysql_fetch_row($q630)) {
				$ocjene[$r630[0]] = $r630[1];
			}

			$palo = $uk_studenata - $ocjene[10] - $ocjene[9] - $ocjene[8] - $ocjene[7] - $ocjene[6];
			print "<tr><td>$naziv</td><td>$ocjene[10]</td><td>".procenat($ocjene[10],$uk_studenata)."</td><td>$ocjene[9]</td><td>".procenat($ocjene[9],$uk_studenata)."</td><td>$ocjene[8]</td><td>".procenat($ocjene[8],$uk_studenata)."</td><td>$ocjene[7]</td><td>".procenat($ocjene[7],$uk_studenata)."</td><td>$ocjene[6]</td><td>".procenat($ocjene[6],$uk_studenata)."</td><td>$palo</td><td>".procenat($palo,$uk_studenata)."</td></tr>";
		}

		// 
	}


	// PODIZVJESTAJ 4: Statistika za ispit, pojedinacni studenti
	else if ($studenti==1 && $ispit!=3) {

		// Kreiranja niza kurseva i ispis zaglavlja tabele
		$kursevi = array();
		
		print "<p>Pregled po studentima.";
		if ($sortiranje==1) print " Spisak je sortiran po broju položenih ispita.</p>\n";
		print " Spisak je sortiran po prezimenu.</p>\n";

		?>
		<table  border="1" cellspacing="0" cellpadding="2">
		<tr>
			<td>R. br.</td>
			<td>Student</td>
			<td>Broj indexa</td>
		<?
		while ($r600 = mysql_fetch_row($q600)) {
			$kursevi[$r600[0]] = $r600[1];
			$naziv = $r600[1];
			if ($r600[2]==0) $naziv .= " *";
			print "<td>$naziv</td>\n";
		}
		print "<td>UKUPNO:</td></tr>\n";

		// Upit koji vraca sve rezultate
		$imeprezime = array();
		$brind = array();
		$rezultati = array();
		$izasao = array();

		global $polozio;
		$polozio = array(); // ne znam kako bez global :(
		global $suma_bodova;
		$suma_bodova = array();

		if ($ispit==1 || $ispit==2) {
			if ($cista_gen == 0)
				$q620 = myquery("select s.id, s.ime, s.prezime, s.brindexa, i.predmet, io.ocjena from student as s, ispit as i, ispitocjene as io, ponudakursa as pk where s.id=io.student and io.ispit=i.id and i.predmet=pk.id and i.tipispita=$ispit and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");
			else if ($cista_gen == 1)
				$q620 = myquery("select s.id, s.ime, s.prezime, s.brindexa, i.predmet, io.ocjena from student as s, ispit as i, ispitocjene as io, ponudakursa as pk, student_studij as ss where s.id=io.student and io.ispit=i.id and i.predmet=pk.id and i.tipispita=$ispit and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit and ss.student=s.id and ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit");
			else if ($cista_gen == 2)
				$q620 = myquery("select s.id, s.ime, s.prezime, s.brindexa, i.predmet, io.ocjena from student as s, ispit as i, ispitocjene as io, ponudakursa as pk, student_studij as ss where s.id=io.student and io.ispit=i.id and i.predmet=pk.id and i.tipispita=$ispit and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit and ss.student=s.id and ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=s.id and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");

			while ($r620 = mysql_fetch_row($q620)) {
				$imeprezime[$r620[0]] = "$r620[2] $r620[1]";
				$brind[$r620[0]] = $r620[3];
				if ($izasao[$r620[0]][$r620[4]] == 1) {
					if ($rezultati[$r620[0]][$r620[4]]<10 && $r620[5]>=10) $polozio[$r620[0]]++;
					if ($r620[5]>$rezultati[$r620[0]][$r620[4]]) {
						$rezultati[$r620[0]][$r620[4]] = $r620[5];
						$suma_bodova[$r620[0]] += $r620[5] - $rezultati[$r620[0]][$r620[4]];
					}
				} else {
					if ($r620[5]>$rezultati[$r620[0]][$r620[4]]) $rezultati[$r620[0]][$r620[4]] = $r620[5];
					$izasao[$r620[0]][$r620[4]] = 1;
					if ($r620[5]>=10) $polozio[$r620[0]]++;
					$suma_bodova[$r620[0]] += $r620[5];
				}
			}
		} else {
			// konacna ocjena
		}


		// Sortiranje tabele

		if ($sortiranje==0) {
			uasort($imeprezime,"bssort"); // bssort - bosanski jezik
		} else {
			function tablica_sort($a, $b) {
				global $polozio,$suma_bodova;
				if ($polozio[$a]>$polozio[$b]) return -1;
				else if ($polozio[$a]<$polozio[$b]) return 1;
				else if ($suma_bodova[$a]>$suma_bodova[$b]) return -1;
				return 1;
			}
			uksort($imeprezime,"tablica_sort");
		}


		// Ispis tablice

		$rbr=1;
		$slusalo = array();
		$polozilo = array();
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			?>
			<tr>
				<td><?=$rbr++?></td>
				<td><?=$brind[$stud_id]?></td>
				<td><?=$stud_imepr?></td>
			<?

			foreach ($kursevi as $kurs_id => $kurs) {
				if ($rezultati[$stud_id][$kurs_id] >= 10) $polozilo[$kurs_id]++;
				if ($izasao[$stud_id][$kurs_id]==1) {
					$slusalo[$kurs_id]++;
					print "<td>".$rezultati[$stud_id][$kurs_id]."</td>\n";
				} else {
					print "<td>/</td>\n";
				}
			}
			if (intval($polozio[$stud_id])==0) $polozio[$stud_id]=0;
			print "<td>".$polozio[$stud_id]."</td></tr>\n";
		}
		if ($ispit==1 || $ispit==2)
			print '<tr><td colspan="3" align="right">PRISTUPILO ISPITU</td>';
		else
			print '<tr><td colspan="3" align="right">SLUŠALO</td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			if (intval($slusalo[$kurs_id])==0) $slusalo[$kurs_id]="0";
			print '<td>'.$slusalo[$kurs_id]."</td>\n";
		}
		print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">POLOŽILO</td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			if (intval($polozilo[$kurs_id])==0) $polozilo[$kurs_id]="0";
			print '<td>'.$polozilo[$kurs_id]."</td>\n";
		}
		print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">PROCENAT</td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			if ($slusalo[$kurs_id]>0) {
				$proc = intval(($polozilo[$kurs_id]/$slusalo[$kurs_id])*10000)/100;
				print '<td>'.$proc."%</td>\n";
			} else {
				print "<td>-</td>\n";
			}
		}
		print "<td>&nbsp;</td></tr></table>\n* Predmet je izborni<br/>\n";
	}


	// PODIZVJESTAJ 5: Ukupan broj bodova, pojedinacni studenti
	// NEOPTIMIZOVANO
	else if ($studenti==1 && $ispit==3) {
	
	
		// tabela kurseva i studenata
		$kursevi = array();
		$imeprezime = array();
		$brind = array();
		$sirina = 200;
		while ($r600 = mysql_fetch_row($q600)) {
			$kursevi[$r600[0]] = $r600[1];
	
			$q601 = myquery("select s.id, s.ime, s.prezime, s.brindexa from student as s, student_labgrupa as sl, labgrupa as l where sl.student=s.id and sl.labgrupa=l.id and l.predmet=$r600[0]");
			while ($r601 = mysql_fetch_row($q601)) {
				$imeprezime[$r601[0]] = "$r601[2] $r601[1]";
				$brind[$r601[0]] = $r601[3];
			}
			$sirina += 200;
		}
	
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik
	
		// array zadaća - optimizacija
		$kzadace = array();
		foreach ($kursevi as $kurs_id => $kurs) {
			$q600a = myquery("select id, zadataka from zadaca where predmet=$kurs_id");
			$tmpzadaca = array();
			while ($r600a = mysql_fetch_row($q600a)) {
				$tmpzadaca[$r600a[0]] = $r600a[1];
			}
			$kzadace[$kurs_id] = $tmpzadaca;
		}
	
		?>
		<table width="<?=$sirina?>" border="1" cellspacing="0" cellpadding="2">
		<tr>
			<td rowspan="2" valign="center">R. br.</td>
			<td rowspan="2" valign="center">Broj indeksa</td>
			<td rowspan="2" valign="center">Prezime i ime</td>
		<?
		foreach ($kursevi as $kurs) {
			print '<td colspan="6" align="center">'.$kurs."</td>\n";
		}
		?>
			<td rowspan="2" valign="center" align="center">UKUPNO</td>
		</tr>
		<tr>
		<?
		for ($i=0; $i<count($kursevi); $i++) {
			?>
			<td align="center">I</td>
			<td align="center">II</td>
			<td align="center">Int</td>
			<td align="center">P</td>
			<td align="center">Z</td>
			<td align="center">Ocjena</td>
			<?
		}
		print "</tr>\n";
		$rbr=1;
	
		// Slušalo / položilo predmet
		$slusalo = array();
		$polozilo = array();
	
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			?>
			<tr>
				<td><?=$rbr++?></td>
				<td><?=$brind[$stud_id]?></td>
				<td><?=$stud_imepr?></td>
			<?
			$polozio = 0;
			foreach ($kursevi as $kurs_id => $kurs) {
				$slusalo[$kurs_id]++;
				$q602 = myquery("select io.ocjena,i.tipispita from ispit as i, ispitocjene as io where io.ispit=i.id and io.student=$stud_id and i.predmet=$kurs_id");
				$ispit = array();
				$ispit[1] = $ispit[2] = $ispit[3] = -1;
				while ($r602 = mysql_fetch_row($q602)) {
					if ($r602[0] > $ispit[$r602[1]]) 
						$ispit[$r602[1]] = $r602[0];
				}
				for ($i=1; $i<4; $i++) {
					if ($ispit[$i] >= 0)
						print "<td>$ispit[$i]</td>\n";
					else
						print "<td>&nbsp;</td>\n";
				}
	
				$q603 = myquery("select count(*) from prisustvo as p,cas as c, labgrupa as l where p.student=$stud_id and p.cas=c.id and c.labgrupa=l.id and l.predmet=$kurs_id and p.prisutan=0");
				if (mysql_result($q603,0,0)<=3) {
					print "<td>10</td>\n";
					$ukupno += 10;
				} else
					print "<td>0</td>\n";
	
				$zadaca = 0;
				foreach ($kzadace[$kurs_id] as $zid => $zadataka) {
					for ($i=1; $i<=$zadataka; $i++) {
						$q605 = myquery("select status,bodova from zadatak where zadaca=$zid and redni_broj=$i and student=$stud_id order by id desc limit 1");
						if ($r605 = mysql_fetch_row($q605))
							if ($r605[0] == 5)
								$zadaca += $r605[1];
	//					$zadaca .= $i." ";
					}
				}
				print "<td>$zadaca</td>\n";
	
				$q606 = myquery("select ocjena from konacna_ocjena where student=$stud_id and predmet=$kurs_id");
				if (mysql_num_rows($q606)>0) {
					$ocj = mysql_result($q606,0,0);
					print "<td>$ocj</td>\n";
					if ($ocj >= 6) $polozio++;
					$polozilo[$kurs_id]++;
				} else
					print "<td>&nbsp;</td>\n";
			}
			print "<td>$polozio</td></tr>\n";
			$i++;
		}
		print '<tr><td colspan="3" align="right">SLUŠALO</td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			print '<td colspan="5">'.$slusalo[$kurs_id]."</td>\n";
		}
		print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">POLOŽILO</td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			if (intval($polozilo[$kurs_id])==0) $polozilo[$kurs_id]="0";
			print '<td colspan="5">'.$polozilo[$kurs_id]."</td>\n";
		}
		print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">PROCENAT</td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			$proc = intval(($polozilo[$kurs_id]/$slusalo[$kurs_id])*100)/100;
			print '<td colspan="5">'.$proc."%</td>\n";
		}
		print '<td>&nbsp;</td></tr></table>';
	}
}


if ($tip == "odustali") {
	if ($siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	print "<h3>Spisak studenata koji više nisu na fakultetu</h3>\n";
	
	$q700 = myquery("select s.id, s.ime, s.prezime, ss.studij from student as s left join student_studij as ss on s.id=ss.student where ss.studij is null");
	$imeprezime = array();
	while ($r700 = mysql_fetch_row($q700)) {
		$imeprezime[$r700[0]] = "$r700[2] $r700[1]";
	}
	
	uasort($imeprezime,"bssort"); // bssort - bosanski jezik

	$rbr=1;
	foreach($imeprezime as $stud_id => $stud_imepr) {
		$q702 = myquery("select count(*) from akademska_godina as ag, student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.student=$stud_id and sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=ag.id and pk.predmet=5");
		if (mysql_result($q702,0,0)>0) continue;
		$q701 = myquery("select ag.naziv from akademska_godina as ag, student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.student=$stud_id and sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=ag.id group by ag.naziv order by ag.naziv");
		$aghtml = "";
		while ($r701 = mysql_fetch_row($q701)) {
			if ($aghtml != "") $aghtml .= ", ";
			$aghtml .= $r701[0];
		}
		print $rbr++.". $stud_imepr ($aghtml)<Br/>";
	}
}




// ISPIT - statistika ispita

if ($tip == "ispit") {
	if ($predmetadmin == -1 && $siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}
	
	$ispit = intval($_REQUEST['ispit']);
	if ($_REQUEST['ispit'] == "svi") $ispit=-1;

	// Naziv predmeta, akademska godina
	$q800 = myquery("select p.naziv,ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag where pk.id=$predmet and ag.id=pk.akademska_godina and pk.predmet=p.id");
	print "<p>&nbsp;</p><h1>".mysql_result($q800,0,0)." ".mysql_result($q800,0,1)."</h1>\n";

	// Tip ispita, datum i opis
	if ($ispit==-1) {
		print "<h3>Sumarna statistika za sve ispite</h3>\n";
	} else {
		$q801 = myquery("select UNIX_TIMESTAMP(i.datum),ti.naziv,i.naziv from ispit as i, tipispita as ti where i.id=$ispit and i.tipispita=ti.id");
		print "<h3>".mysql_result($q801,0,1).", ".date("d. m. Y.", mysql_result($q801,0,0));
		$opis = mysql_result($q801,0,2);
		if (preg_match("/\w/",$opis)) print " ($opis)";
		print "</h3>\n";
	}

	// Opste statistike (sumarno za predmet)
	if ($ispit==-1) {
		// Potreban MySQL v4.0+ !!!
		$q804 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet");
		$slusa_predmet = mysql_result($q804,0,0);
		$q820 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet and (select count(*) from konacna_ocjena as ko where ko.student=sl.student and ko.predmet=$predmet and ko.ocjena>5)>0");
		$polozilo = mysql_result($q820,0,0);
		$q821 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.tipispita=1 and i.id=io.ispit and io.student=sl.student and io.ocjena>=10)>0");
		$prvaparc = mysql_result($q821,0,0);
		$q822 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.tipispita=2 and i.id=io.ispit and io.student=sl.student and io.ocjena>=10)>0");
		$drugaparc = mysql_result($q822,0,0);
		$q823 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.tipispita=3 and i.id=io.ispit and io.student=sl.student and io.ocjena>=10)>0");
		$intparc = mysql_result($q823,0,0);
		$q824 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet and (((select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.tipispita=1 and i.id=io.ispit and io.student=sl.student and io.ocjena>=10)>0) and ((select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.tipispita=2 and i.id=io.ispit and io.student=sl.student and io.ocjena>=10)>0) or ((select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.tipispita=3 and i.id=io.ispit and io.student=sl.student and io.ocjena>=10)>0))");
		$parcijale = mysql_result($q824,0,0);
		$q825 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet and (select count(*) from ispitocjene as io, ispit as i where i.predmet=$predmet and i.id=io.ispit and io.student=sl.student)=0");
		$nisu_izlazili = mysql_result($q825,0,0);

		print "<p>Ukupno upisalo predmet: <b>$slusa_predmet</b> studenata.<br/>";
		print "Nije izašlo ni na jedan ispit (pretpostavka je da ne slušaju predmet, biće isključeni iz daljnjih statistika): <b>$nisu_izlazili</b> studenata.<br/>";
		$slusa_predmet -= $nisu_izlazili;
		print "Položilo (konačna ocjena 6 ili više): <b>$polozilo</b> studenata (<b>".procenat($polozilo,$slusa_predmet)."</b>).<br/>";
		print "Zadovoljili uslove za usmeni: <b>$parcijale</b> studenata (<b>".procenat($parcijale,$slusa_predmet)."</b>).<br/><br/>";
		print "Položilo I parcijalni ispit: <b>$prvaparc</b> studenata  (<b>".procenat($prvaparc,$slusa_predmet)."</b>).<br/>";
		print "Položilo II parcijalni ispit: <b>$drugaparc</b> studenata  (<b>".procenat($drugaparc,$slusa_predmet)."</b>).<br/>";
		print "Položilo ispit integralno: <b>$intparc</b> studenata  (<b>".procenat($intparc,$slusa_predmet)."</b>).</p>";

		return;
	}

	// Opste statistike - pojedinacni ispit
	$q802 = myquery("select count(*) from ispitocjene where ispit=$ispit");
	$ukupno_izaslo = mysql_result($q802,0,0);
	$q803 = myquery("select count(*) from ispitocjene where ispit=$ispit and ocjena>=10");
	$polozilo = mysql_result($q803,0,0);
	$q804 = myquery("select count(*) from student_labgrupa as sl, labgrupa as l where sl.labgrupa=l.id and l.predmet=$predmet");
	$slusa_predmet = mysql_result($q804,0,0);
	$prolaznost = intval(($polozilo/$ukupno_izaslo)*10000+0.5)/100;
	print "<p>Ukupno izašlo studenata: <b>$ukupno_izaslo</b><br/>Položilo: <b>$polozilo</b><br/>Prolaznost: <b>$prolaznost %</b></p>\n";
	print "<p>Od studenata koji slušaju predmet, nije izašlo: <b>".($slusa_predmet-$ukupno_izaslo)."</b></p>";

	// Po broju bodova
	print "<p>Distribucija po broju bodova:<br/>(Svaki stupac predstavlja broj studenata sa određenim brojem bodova. Rezolucija je 0.5 bodova)</p>";
	// Odredjivanje max. broja studenata po koloni radi skaliranja grafa
	$max = 0;
	for ($i=0; $i<=20; $i+=0.5) {
		$q805 = myquery("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+0.5));
		$studenata = mysql_result($q805,0,0);
		if ($studenata>$max) $max=$studenata;
	}
	$koef = 80/$max;

	?><table border="0" cellspacing="0" cellpadding="0"><tr><?
	for ($i=0; $i<=20; $i+=0.5) {
		$q806 = myquery("select COUNT( * ) FROM ispitocjene WHERE ispit=$ispit and ocjena>=$i and ocjena<".($i+0.5));
		$height = intval(mysql_result($q806,0,0) * $koef);
		?><td width="10">
			<table width="10" border="0" cellspacing="0" cellpadding="0">
				<tr><td>
					<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
				</td></tr><tr><td bgcolor="#FF0000">
					<img src="images/fnord.gif" width="1" height="<?=$height?>">
				</td></tr>
				<!--tr><td align="center"><?=$r806[0]?><br/><?=$r806[1]?><br/>
				</td></tr-->
			</table>
		</td><td>&nbsp;</td><?
	}
	print "</tr></table>\n";


	// Prolaznost po grupama
	$ukupno = array(); $polozilo = array(); $prosjek = array(); $grupe = array();
	$maxprol = 0; $maxprosj = 0;
	$q807 = myquery("select l.id,io.ocjena,l.naziv FROM ispitocjene as io, student_labgrupa as sl, labgrupa as l, ispit as i WHERE io.ispit=$ispit and io.student=sl.student and sl.labgrupa=l.id and l.predmet=i.predmet and i.id=io.ispit order by l.id");
	while ($r807 = mysql_fetch_row($q807)) {
		$ukupno[$r807[0]]++;
		if ($r807[1]>=10) $polozilo[$r807[0]]++;
		$prosjek[$r807[0]] = ($prosjek[$r807[0]]*($ukupno[$r807[0]]-1) + $r807[1]) / $ukupno[$r807[0]];
		$grupe[$r807[0]] = $r807[2];
		if ($prosjek[$r807[0]]>$maxprosj) $maxprosj=$prosjek[$r807[0]];
		$prolaznost = $polozilo[$r807[0]]/$ukupno[$r807[0]];
		if ($prolaznost>$maxprol) $maxprol=$prolaznost;
	}
	
	print "<p>Prolaznost po grupama:</p>";
	$koef = 80/$maxprol;
	?><table border="0" cellspacing="0" cellpadding="0"><tr><?
	foreach ($grupe as $id => $naziv) {
		$height = intval($polozilo[$id]/$ukupno[$id] * $koef);
		$label = intval($polozilo[$id]/$ukupno[$id] * 100) . "%";
		?><td width="50" valign="top">
			<table width="50" border="0" cellspacing="0" cellpadding="0">
				<tr><td align="center"><?=$label?></td></tr>
				<tr><td>
					<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
				</td></tr><tr><td bgcolor="#FF0000">
					<img src="images/fnord.gif" width="1" height="<?=$height?>">
				</td></tr>
				<tr><td align="center"><?=$naziv?></td></tr>
			</table>
		</td><td width="10">&nbsp;</td><?
	}
	print "</tr></table>\n";


	// Broj bodova po grupama
	print "<p>Prosječan broj bodova po grupama:</p>";
	$koef = 80/$maxprosj;
	?><table border="0" cellspacing="0" cellpadding="0"><tr><?
	foreach ($grupe as $id => $naziv) {
		$height = intval($prosjek[$id] * $koef);
		$label = intval($prosjek[$id]*10) / 10;
		?><td width="50" valign="top">
			<table width="50" border="0" cellspacing="0" cellpadding="0">
				<tr><td align="center"><?=$label?></td></tr>
				<tr><td>
					<img src="images/fnord.gif" width="1" height="<?=(100-$height)?>">
				</td></tr><tr><td bgcolor="#FF0000">
					<img src="images/fnord.gif" width="1" height="<?=$height?>">
				</td></tr>
				<tr><td align="center"><?=$naziv?></td></tr>
			</table>
		</td><td width="10">&nbsp;</td><?
	}
	print "</tr></table>\n";
}


return;

}

function procenat($dio,$total) {
	return (intval($dio/$total*10000)/100)."%";
}

?>
