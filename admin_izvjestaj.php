<?

// v3.0.1.1 (2007/09/20) + Novi modul "Izvjestaj" - izdvojen iz admin_nihada; dodan izvjestaj "grupe"
// v3.0.1.2 (2007/09/25) + Dodan izvjestaj "predmet_full"; optimizacija racunanja bodova na ispitima



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
		$q102 = myquery("select p.naziv,k.ocjena from konacna_ocjena as k,predmet as p where k.student=$student and k.predmet=p.id and p.akademska_godina=$r101[0] order by p.naziv");
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
	<table width="700" border="1" cellspacing="0" cellpadding="3"><tr bgcolor="#AAAAAA">
		<td width="20">&nbsp;</td>
		<td width="155">Predmet</td>
		<td width="75">Ak. godina</td>
		<td width="75">Prisustvo</td>
		<td width="75">Zadaće</td>
		<td width="75">I parcijalni</td>
		<td width="75">II parcijalni</td>
		<td width="75">UKUPNO</td>
		<td width="75">Ocjena</td>
	</tr>
	<?
	$i=1;
	$q310 = myquery("select id,naziv from akademska_godina order by naziv");
	while ($r310 = mysql_fetch_row($q310)) {
		$q311 = myquery("select p.id, p.naziv, l.id from predmet as p, labgrupa as l, student_labgrupa as sl where sl.student=$student and sl.labgrupa=l.id and l.predmet=p.id and p.akademska_godina=$r310[0] order by p.naziv");
		while ($r311 = mysql_fetch_row($q311)) {
			print "<tr><td>".($i++)."</td><td>".$r311[1]."</td><td>".$r310[1]."</td>";
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

			$q315 = myquery("select io.ocjena,io.ocjena2,i.datum from ispitocjene as io, ispit as i where io.student=$student and io.ispit=i.id  and i.predmet=$r311[0] order by i.datum");

			$prvi_ispis = $drugi_ispis = "";
			$max1 = $max2 = "&nbsp;";
			
			if (mysql_num_rows($q315)>0) {
				while ($r315 = mysql_fetch_row($q315)) {
					if ($razdvoji == 1) {
						list ($g,$m,$d) = explode("-",$r315[2]);
						if ($r315[0]>=0) $prvi_ispis .= "$r315[0] ($d.$m.)<br/>";
						if ($r315[1]>=0) $drugi_ispis .= "$r315[1] ($d.$m.)<br/>";
					}
					if ($r315[0]>$max1) $max1=$r315[0];
					if ($r315[1]>$max2) $max2=$r315[1];
				}
				$ukupno += ($max1 + $max2);
			}

			if ($razdvoji == 0) {
				print "<td>$max1</td><td>$max2</td>";
			} else {
				if ($prvi_ispis == "") 
					print "<td>&nbsp;</td>";
				else
					print "<td>$prvi_ispis</td>";
				if ($drugi_ispis == "") 
					print "<td>&nbsp;</td>";
				else
					print "<td>$drugi_ispis</td>";
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

if ($tip == "grupe") {
	if ($predmetadmin == -1 && $siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	$q399 = myquery("select naziv from predmet where id=$predmet");
	print "<p>&nbsp;</p><h1>".mysql_result($q399,0,0)."</h1><p>Spisak grupa:</p>\n";
	print '<table width="100%" border="0">'."\n";

	$q400 = myquery("select id,naziv from labgrupa where predmet=$predmet");
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



// PREDMET_FULL - izvjestaj koji salje Nihada

if ($tip == "predmet_full") {
	if ($predmetadmin == -1 && $siteadmin == 0) {
		niceerror("Nemate permisije za pristup ovom izvještaju");
		return;
	}

	$q500 = myquery("select p.naziv,ag.naziv from predmet as p, akademska_godina as ag where p.id=$predmet and ag.id=p.akademska_godina");
	print "<p>&nbsp;</p><h1>".mysql_result($q500,0,0)." ".mysql_result($q500,0,1)."</h1>\n";

	$grupa = intval($_REQUEST['grupa']);
	if ($grupa>0)
		$q501 = myquery("select id,naziv from labgrupa where predmet=$predmet and id=$grupa");
	else
		$q501 = myquery("select id,naziv from labgrupa where predmet=$predmet");

	while ($r501 = mysql_fetch_row($q501)) {
		?>
		<table width="100%" border="2" cellspacing="0" cellpadding="2">
			<tr><td colspan="27" align="center"><b><?=strtoupper($r501[1])?></b></td></tr>
			<tr><td align="center">R.br.</td>
				<td align="center">Br. indexa</td>
				<td align="center">Prezime i ime</td>
				<td colspan="7" align="center">Prisustvo tutorijalima 1</td>
				<td colspan="7" align="center">Prisustvo tutorijalima 2</td>
				<td colspan="5" align="center">Zadaće i lab vježbe</td>
				<td align="center">Test 1</td>
				<td align="center">Test 2</td>
				<td align="center">Prisustvo</td>
				<td align="center">Zadaće</td>
				<td align="center">Ukupno</td>
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
					print "<td>/</td>\n";
				} else if (mysql_result($q505,0,0) == 0) {
					print "<td>0</td>\n";
					$n++;
				} else {
					print "<td>1</td>\n";
				}
			}
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
/*				$uk_bodova = 0;
				for ($i=1; $i<=$zadataka; $i++) {
					if ($r506 = mysql_fetch_row($q506)) {
						if ($r506[0] == 5)
							$uk_bodova += $r506[1];
					}
				}
				print "<td>$uk_bodova</td>\n";
				$zadace += $uk_bodova;*/
				if ($bodova[$zid]) {
					print "<td>".$bodova[$zid]."</td>";
					$zadace += $bodova[$zid];
				} else {
					print "<td>&nbsp;</td>";
				}
			}
			for  ($i=count($zadacear); $i<5; $i++)
				print "<td>&nbsp;</td>\n";

			$q507 = myquery("select io.ocjena,io.ocjena2 from ispitocjene as io, ispit as i where io.student=$stud_id and io.ispit=i.id  and i.predmet=$predmet order by i.datum");
			$ocjena1 = $ocjena2 = "&nbsp;";
			while ($r507 = mysql_fetch_row($q507)) {
				if ($r507[0] > $ocjena1 && $r507[0] != "-1") $ocjena1=$r507[0];
				if ($r507[1] > $ocjena2 && $r507[1] != "-1") $ocjena2=$r507[1];
			}
			print "<td>$ocjena1</td><td>$ocjena2</td>\n";

			print "<td>$prisustvo</td>";
			print "<td>$zadace</td>";
			print "<td>".($prisustvo+$zadace+$ocjena1+$ocjena2)."</td>";

			print "</tr>\n";
		}
		print "</table><p>&nbsp;</p>";

	}
	
}

return;

}

?>
