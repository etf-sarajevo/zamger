<?

// IZVJESTAJ/GRUPE - spisak studenata po grupama

// v3.9.1.0 (2008/02/26) + Preimenovan bivsi admin_izvjestaj(), spojeni izvjestaji grupe i grupedouble
// v3.9.1.1 (2008/06/16) + Dodan prikaz studenata koji nisu ni u jednoj grupi (upit je malo spor)
// v3.9.1.2 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.3 (2008/09/17) + Sortiraj grupe po nazivu; dodana tablica za prisustvo; dodano polje tip; dodan page break (vidljiv prilikom stampanja) izmedju grupa
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/02/25) + Popravljena sirina kolone za tabelu "Studenti koji nisu niti u jednoj grupi" kod jednokolonskog ispisa bez prisustva
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet


// TODO: Ubaciti strverscmp u libvedran?


function izvjestaj_grupe() {

global $userid,$user_siteadmin,$user_studentska;


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>

<?


$predmet = intval($_REQUEST['predmet']);
$tip = $_REQUEST['tip'];
if (intval($_REQUEST['double'])==1 && $tip=="") $tip = "double"; // kompatibilnost unazad
$komentari = intval($_REQUEST['komentari']);
$prisustvo = intval($_REQUEST['prisustvo']);

if ($tip=="") $tip="single";

// Naziv predmeta - ovo ujedno provjerava da li predmet postoji

$q10 = myquery("select p.naziv, ag.naziv from predmet as p, ponudakursa as pk, akademska_godina as ag where pk.id=$predmet and pk.predmet=p.id and pk.akademska_godina=ag.id");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	niceerror("Traženi predmet ne postoji");
	return;
}

?>
<h1><?=mysql_result($q10,0,0)?></h1>
<h3>Akademska <?=mysql_result($q10,0,1)?> godina - Spisak grupa</h3>
<?



// Prava pristupa

$q20 = myquery("select count(*) from nastavnik_predmet as np, ponudakursa as pk where np.nastavnik=$userid and np.predmet=pk.predmet and np.akademska_godina=pk.akademska_godina and pk.id=$predmet");
if (mysql_result($q20,0,0)<1 && !$user_siteadmin && !$user_studentska) {
	zamgerlog("permisije (predmet $predmet)",3);
	niceerror("Nemate permisije za pristup ovom izvještaju");
	return;
}



// Dvije kolone

if ($tip=="double") {
	print '<table width="100%" border="0">'."\n";

	$parni=0;

	$q400 = myquery("select id,naziv from labgrupa where predmet=$predmet order by naziv");
	$grupe = array();
	while ($r400 = mysql_fetch_row($q400)) $grupe[$r400[0]] = $r400[1];

	uasort($grupe, strverscmp);

	foreach ($grupe as $id => $naziv) {
		if ($parni == 0) 
			print "<tr>";
		else
			print "</td>";
		?>
		<td width="13%">&nbsp;</td><td width="30%" valign="top">
			<table width="100%" border="2" cellspacing="0">
				<tr><td colspan="2"><b><?=$naziv?></b></td></tr>
				<tr><td>
		<?

		$imeprezime=array();
		$brindexa=array();
		$q401 = myquery("select a.id, a.prezime, a.ime, a.brindexa from osoba as a, student_labgrupa as sl where sl.labgrupa=$id and sl.student=a.id");
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

	$q410 = myquery("select a.id, a.prezime, a.ime, a.brindexa from osoba as a, student_predmet as sp where sp.student=a.id and sp.predmet=$predmet and (select count(*) from student_labgrupa as sl, labgrupa as l where sl.student=sp.student and sl.labgrupa=l.id and l.predmet=$predmet)=0");
	if (mysql_num_rows($q410)>0) {
		if ($parni == 0) 
			print "<tr>";
		else
			print "</td>";
		?>
		<td width="13%">&nbsp;</td><td width="30%" valign="top">
			<table width="100%" border="2" cellspacing="0">
				<tr><td colspan="2"><b>Nisu ni u jednoj grupi</b></td></tr>
				<tr><td>
		<?
		$imeprezime=array();
		$brindexa=array();

		while ($r410 = mysql_fetch_row($q410)) {
			$imeprezime[$r410[0]] = "$r410[1] $r410[2]";
			$brindexa[$r410[0]] = $r410[3];
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


// Jedna kolona

else if ($tip=="single") {
	$nr=3;
	if ($komentari==1) $nr++;
	if ($prisustvo==1) $nr+=14;

	$sirina_tabele = "70%";
	if ($prisustvo==1) $sirina_tabele = "100%";


/*	?>
	<table width="100%" border="0"><tr>
		<td width="20%">&nbsp;</td>
		<td width="60%">
	<?*/
	print "<center>\n";

	$q400 = myquery("select id,naziv from labgrupa where predmet=$predmet");
	$grupe = array();
	while ($r400 = mysql_fetch_row($q400)) $grupe[$r400[0]] = $r400[1];

	uasort($grupe, strverscmp);

	foreach ($grupe as $id => $naziv) {
		?>
			<table width="<?=$sirina_tabele?>" border="2" cellspacing="0">
				<tr><td colspan="<?=$nr?>" align="center"><b><?=strtoupper($naziv)?></b></td></tr>
				<tr><td width="40"><b>R. br.</b></td><td><b>Prezime i ime</b></td><td width="80"><b>Br. indeksa</b></td>
		<?

		if ($prisustvo>0) { ?><td colspan="7" align="center"><b>I semestar</b></td><td colspan="7" align="center"><b>II semestar</b></td><? }

		if ($komentari>0) { ?><td align="center"><b>Komentari</b></td><? }
		print "</tr>\n";

		$imeprezime=array();
		$brindexa=array();
		$komentar=array();
		$q401 = myquery("select a.id, a.prezime, a.ime, a.brindexa from osoba as a, student_labgrupa as sl where sl.labgrupa=$id and sl.student=a.id");
		while ($r401 = mysql_fetch_row($q401)) {
			$imeprezime[$r401[0]] = "$r401[1] $r401[2]";
			$brindexa[$r401[0]] = $r401[3];
			if ($r401[3]=="") $brindexa[$r401[0]]="&nbsp;";
			if ($komentari>0) {
				$q402 = myquery("select UNIX_TIMESTAMP(datum),komentar from komentar where student=$r401[0] and labgrupa=$id order by id");
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
			if ($prisustvo>0)
				for ($i=0; $i<14; $i++) print "<td>&nbsp;</td>";
			if ($komentari>0)
				print "<td>".$komentar[$stud_id]."</td>\n";
			print "</tr>\n";
		}

		?>
				<!--/table></td></tr-->
			</table>
			<p>&nbsp;</p>
			<div class="breakafter"></div>
			<p>&nbsp;</p>
		<?
	}

	$q410 = myquery("select a.id, a.prezime, a.ime, a.brindexa from osoba as a, student_predmet as sp where sp.student=a.id and sp.predmet=$predmet and (select count(*) from student_labgrupa as sl, labgrupa as l where sl.student=sp.student and sl.labgrupa=l.id and l.predmet=$predmet)=0");
	if (mysql_num_rows($q410)>0) {
		?>
			<table width="<?=$sirina_tabele?>" border="2" cellspacing="0">
				<tr><td colspan="3"><b>Nisu ni u jednoj grupi</b></td></tr>
				<tr><td>&nbsp;</td><td>Prezime i ime</td><td>Br. indeksa</td>
		<?
		$imeprezime=array();
		$brindexa=array();

		while ($r410 = mysql_fetch_row($q410)) {
			$imeprezime[$r410[0]] = "$r410[1] $r410[2]";
			$brindexa[$r410[0]] = $r410[3];
		}
		uasort($imeprezime,"bssort"); // bssort - bosanski jezik

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			?>
				<tr>
					<td><?=$n++?></td>
					<td><?=$stud_imepr?></td>
					<td><?=$brindexa[$stud_id]?></td>
			<?
			print "</tr>\n";
		}

		?>
				<!--/table></td></tr-->
			</table>
			<p>&nbsp;</p>
		<?
		
	}

/*	?>
		</td>
		<td width="20%">&nbsp;</td>
	</tr></table>
	<?*/
	print "</center>";
}

}

function is_number($k) {
	return (ord($k)>=ord("0") && ord($k)<=ord("9"));
}

function give_number($string, $pos) {
	$result = "";
	do {
		$result .= $c;
		$c = substr($string,$pos++,1);
	} while (is_number($c) && $pos<=strlen($string));
	return intval($result);
}
function strverscmp($a, $b) {
	$minlen = (strlen($a)<strlen($b)) ? strlen($a) : strlen($b);
	for ($i=0; $i<$minlen; $i++) {
		if ($i>=strlen($a)) return 1; // a is shorter
		if ($i>=strlen($b)) return -1; // a is longer
		$ca = substr($a,$i,1); $cb = substr($b,$i,1);

		// Numerical comparison
		if (is_number($ca) && is_number($cb)) {
			$na = give_number($a,$i);
			$nb = give_number($b,$i);
			if ($na<$nb) return -1;
			if ($na>$nb) return 1;

		} else {
			if (ord($ca)<ord($cb)) return -1;
			if (ord($ca)>ord($cb)) return 1;
		}
	}
	return 0;
}


?>