<?

// IZVJESTAJ/GRUPE - spisak studenata po grupama

// v3.9.1.0 (2008/02/26) + Preimenovan bivsi admin_izvjestaj(), spojeni izvjestaji grupe i grupedouble
// v3.9.1.1 (2008/06/16) + Dodan prikaz studenata koji nisu ni u jednoj grupi (upit je malo spor)


function izvjestaj_grupe() {

global $userid,$user_siteadmin,$user_studentska;


$predmet = intval($_REQUEST['predmet']);
$double = intval($_REQUEST['double']);
$komentari = intval($_REQUEST['komentari']);


// Naziv predmeta - ovo ujedno provjerava da li predmet postoji

$q10 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	niceerror("Traženi predmet ne postoji");
	return;
}


// Prava pristupa

$q20 = myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet");
if (mysql_result($q20,0,0)<1 && !$user_siteadmin && !$user_studentska) {
	zamgerlog("permisije (predmet $predmet)",3);
	niceerror("Nemate permisije za pristup ovom izvještaju");
	return;
}



// Dvije kolone

if ($double == 1) {
	print "<p>&nbsp;</p><h1>".mysql_result($q10,0,0)."</h1><p>Spisak grupa:</p>\n";
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
		$q401 = myquery("select a.id, a.prezime, a.ime, a.brindexa from auth as a, student_labgrupa as sl where sl.labgrupa=$r400[0] and sl.student=a.id");
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

	$q410 = myquery("select a.id, a.prezime, a.ime, a.brindexa from auth as a, student_predmet as sp where sp.student=a.id and sp.predmet=$predmet and (select count(*) from student_labgrupa as sl, labgrupa as l where sl.student=sp.student and sl.labgrupa=l.id and l.predmet=$predmet)=0");
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

else {
	if ($komentari==0) $nr=3; else $nr=4;

	print "<p>&nbsp;</p><h1>".mysql_result($q10,0,0)."</h1><p>Spisak grupa:</p>\n";

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
		$q401 = myquery("select a.id, a.prezime, a.ime, a.brindexa from auth as a, student_labgrupa as sl where sl.labgrupa=$r400[0] and sl.student=a.id");
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

	$q410 = myquery("select a.id, a.prezime, a.ime, a.brindexa from auth as a, student_predmet as sp where sp.student=a.id and sp.predmet=$predmet and (select count(*) from student_labgrupa as sl, labgrupa as l where sl.student=sp.student and sl.labgrupa=l.id and l.predmet=$predmet)=0");
	if (mysql_num_rows($q410)>0) {
		?>
			<table width="100%" border="2" cellspacing="0">
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

	?>
		</td>
		<td width="20%">&nbsp;</td>
	</tr></table>
	<?
}

}

?>