<?

// IZVJESTAJ/REZULTATI_ISPITA - rezultati jednog ispita



function izvjestaj_rezultati_ispita() {

global $userid,$user_nastavnik,$user_studentska,$user_siteadmin;

require_once("lib/utility.php"); // bssort


$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);
$ispit = intval($_REQUEST['ispit']);
$imena = intval($_REQUEST['imena']);

$grupa = intval($_REQUEST['grupa']); // Za samo jednu grupu
if ($grupa>0) $sql_dodaj = "and id=$grupa"; else $sql_dodaj = "and virtualna=0"; // U suprotnom sakrivamo virtualnu grupu

if (!$user_nastavnik && !$user_studentska && !$user_siteadmin) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	return;
}

if ($user_nastavnik && !$user_studentska && !$user_siteadmin) {
	$q10 = db_query("select count(*) from nastavnik_predmet where nastavnik=$userid and akademska_godina=$ag and predmet=$predmet");
	if (db_result($q10,0,0) == 0) {
		biguglyerror("Nemate pravo pristupa ovom izvještaju");
		return;
	}
}

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?


$q10 = db_query("select UNIX_TIMESTAMP(i.datum), k.gui_naziv, k.maxbodova, k.prolaz, i.predmet, i.akademska_godina from ispit as i, komponenta as k where i.id=$ispit and i.komponenta=k.id");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat ispit!");
	zamgerlog ("nepoznat ispit $ispit",3);
	zamgerlog2 ("nepoznat ispit", $ispit);
	return;
}

$finidatum = date("d. m. Y.", db_result($q10,0,0));
$naziv = db_result($q10,0,1);
$maxbodova = db_result($q10,0,2);
$prolaz = db_result($q10,0,3);
if ($predmet != db_result($q10,0,4) || $ag != db_result($q10,0,5)) {
	biguglyerror("Nepoznat ispit!");
	zamgerlog ("spoofing id ispita $ispit",3);
	zamgerlog2 ("spoofing id ispita", $ispit);
	return;
}

// Naziv predmeta, akademska godina
$q21 = db_query("select naziv from predmet where id=$predmet");
$naziv_predmeta = db_result($q21,0,0);

$q22 = db_query("select naziv from akademska_godina where id=$ag");
$naziv_ag = db_result($q22,0,0);

// Predmetni nastavnik
$q25 = db_query("SELECT osoba FROM angazman WHERE predmet=$predmet AND akademska_godina=$ag AND angazman_status=1");
$nastavnik = "";
if (db_num_rows($q25) == 1)
	$nastavnik = tituliraj(db_result($q25,0,0));


?>
<h2><center>Rezultati ispita <?=$naziv?> ispit iz predmeta <?=$naziv_predmeta?><br>
održanog <?=$finidatum?></center></h2>
<?



// Spisak studenata
// (iz kojeg ćemo vaditi članove grupa, tako da će na kraju ostati oni van svih grupa)

$imeprezime=array();
$brindexa=array();
$q30 = db_query("select o.id, o.prezime, o.ime, o.brindexa from osoba as o, student_predmet as sp, ponudakursa as pk where o.id=sp.student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
while ($r30 = db_fetch_row($q30)) {
	$imeprezime[$r30[0]] = "$r30[1] $r30[2]";
	$brindexa[$r30[0]] = $r30[3];
}
uasort($imeprezime,"bssort"); // bssort - bosanski jezik

// Rezultati ispita
$rezultati = array();
$q40 = db_query("SELECT student, ocjena FROM ispitocjene WHERE ispit=$ispit");
while ($r40 = db_fetch_row($q40)) {
	$rezultati[$r40[0]] = $r40[1];
}


// Spisak grupa
$q400 = db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag $sql_dodaj");
$grupe = array();
while ($r400 = db_fetch_row($q400)) $grupe[$r400[0]] = $r400[1];

natsort($grupe);


$kolone = intval($_REQUEST['kolone']);

if ($kolone == 2) {

	?>
	<table width="100%" border="0">
	<?

	$parni=0;

	foreach ($grupe as $id => $naziv) {
		if ($parni == 0) 
			print "<tr>\n";
		else
			print "</td>\n";

		if ($imena == 1) $colspan=4; else $colspan=3;

		?>
		<td width="12%">&nbsp;</td>
		<td width="31%" valign="top">
			<table width="100%" border="1" cellspacing="0" cellpadding="2">
				<tr><td colspan="<?=$colspan?>"><b><?=$naziv?></b></td></tr>
				<?

		$idovi = array();
		$q405 = db_query("select student from student_labgrupa where labgrupa=$id");
		while ($r405 = db_fetch_row($q405)) $idovi[]=$r405[0];

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if (!in_array($stud_id,$idovi)) continue;
			unset($imeprezime[$stud_id]);

			if (array_key_exists($stud_id, $rezultati))
				$rezultat = $rezultati[$stud_id];
			else
				$rezultat = "/";

			if ($imena == 1) {
				?>
				<tr><td width="10%"><?=$n?>.</td><td width="50%"><?=$imeprezime[$stud_id]?></td><td width="20%"><?=$brindexa[$stud_id]?></td><td width="20%"><?=$rezultat?></td></tr>
				<?
			} else {
				?>
				<tr><td width="10%"><?=$n?>.</td><td width="40%"><?=$brindexa[$stud_id]?></td><td width="50%"><?=$rezultat?></td></tr>
				<?
			}

			unset($brindexa[$stud_id]);
			$n++;
		}

		?>
			</table>
		<?
		$imena=$brojevi_indexa="";

		if ($parni==1) {
			$parni=0;
			?>
		</td>
		<td width="12%">&nbsp;</td></tr>
	</table>
	<div class="breakafter"></div>
	<table width="100%" border="0">
		<tr><td colspan="5">&nbsp;</td></tr>
		<?
		} else $parni=1;
	}


	if ($grupa==0 && count($imeprezime)>0) {
		if ($parni == 0) 
			print "<tr>\n";
		else
			print "</td>\n";

		if ($imena == 1) $colspan=4; else $colspan=3;


		?>
		<td width="12%">&nbsp;</td>
		<td width="31%" valign="top">
			<table width="100%" border="1" cellspacing="0" cellpadding="2">
				<?

		if (!empty($grupe)) {
?>
				<tr><td colspan="<?=$colspan?>"><b>Nisu ni u jednoj grupi</b></td></tr>
				<?
		}

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {

			if (array_key_exists($stud_id, $rezultati))
				$rezultat = $rezultati[$stud_id];
			else
				$rezultat = "/";

			if ($imena == 1) {
				?>
				<tr><td width="10%"><?=$n?>.</td><td width="50%"><?=$imeprezime[$stud_id]?></td><td width="20%"><?=$brindexa[$stud_id]?></td><td width="20%"><?=$rezultat?></td></tr>
				<?
			} else {
				?>
				<tr><td width="10%"><?=$n?>.</td><td width="40%"><?=$brindexa[$stud_id]?></td><td width="50%"><?=$rezultat?></td></tr>
				<?
			}
			$n++;
			if ($n % 49 == 0) {
				?>
			</table>
		</td>
				<?
				if ($parni==1) {
					$parni=0;
					?>
		<td width="12%">&nbsp;</td></tr>
	</table>
	<div class="breakafter"></div>
	<table width="100%" border="0">
		<tr><td colspan="5">&nbsp;</td></tr>
		<?
				} else $parni=1;
				?>
		<td width="12%">&nbsp;</td>
		<td width="31%" valign="top">
			<table width="100%" border="1" cellspacing="0" cellpadding="2">
				<?

				if (!empty($grupe)) {
				?>
				<tr><td colspan="<?=$colspan?>"><b>Nisu ni u jednoj grupi</b></td></tr>
				<?
				}
			}
		}
		?>
			</table>
		<?

		if ($parni==1) {
			$parni=0;
			?>
		</td>
		<td width="12%">&nbsp;</td></tr>
		<tr><td colspan="5">&nbsp;</td></tr>
		<?
		} else $parni=1;
	}

	if ($parni==1) {
		?>
		</td>
		<td width="12%">&nbsp; </td>
		<td width="31%" valign="top">&nbsp; </td>
		<td width="12%">&nbsp; </td>
	</tr>
	<?
	}
	print "</table>\n";

} else {

	if ($imena == 1) {
		$sirina_tabele="70%"; 
		$colspan=4;
	} else {
		$sirina_tabele="40%";
		$colspan=3;
	}

	print "<center>";

	foreach ($grupe as $id => $naziv) {
		?>
			<table width="<?=$sirina_tabele?>" border="2" cellspacing="0">
				<tr><td colspan="<?=$colspan?>" align="center"><b><?=$naziv?></b></td></tr>
		<?

		if ($imena == 1) {
			?>
				<tr><td width="40"><b>R. br.</b></td><td><b>Prezime i ime</b></td><td width="80"><b>Br. indeksa</b></td><td width="80"><b>Bodova</b></td></tr>
			<?
		} else {
			?>
				<tr><td width="40"><b>R. br.</b></td><td width="80"><b>Br. indeksa</b></td><td width="80"><b>Bodova</b></td></tr>
			<?
		}

		$idovi = array();
		$q405 = db_query("select student from student_labgrupa where labgrupa=$id");
		while ($r405 = db_fetch_row($q405)) $idovi[]=$r405[0];

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if (!in_array($stud_id,$idovi)) continue;
			unset($imeprezime[$stud_id]);

			if ($brindexa[$stud_id]=="") $brindexa[$stud_id]="&nbsp;";


			if (array_key_exists($stud_id, $rezultati))
				$rezultat = $rezultati[$stud_id];
			else
				$rezultat = "/";

			if ($imena == 1) {
				?>
				<tr>
					<td><?=$n++?>.</td>
					<td><?=$imeprezime[$stud_id]?></td>
					<td><?=$brindexa[$stud_id]?></td>
					<td><?=$rezultat?></td>
				</tr>
				<?
			} else {
				?>
				<tr>
					<td><?=$n++?>.</td>
					<td><?=$brindexa[$stud_id]?></td>
					<td><?=$rezultat?></td>
				</tr>
				<?
			}
		}

		?>
				<!--/table></td></tr-->
			</table>
			<p>&nbsp;</p>
			<?
			if ($grupa == 0) {
			?>
			<div class="breakafter"></div>
			<? } ?>
			<p>&nbsp;</p>
		<?
	}

	if ($grupa==0 && count($imeprezime)>0) {

		?>
			<table width="<?=$sirina_tabele?>" border="2" cellspacing="0">
				<?

		if (!empty($grupe)) {
				?>
				<tr><td colspan="<?=$colspan?>"><b>Nisu ni u jednoj grupi</b></td></tr>
				<?
		}

		if ($imena == 1) {
			?>
				<tr><td width="40"><b>R. br.</b></td><td><b>Prezime i ime</b></td><td width="80"><b>Br. indeksa</b></td><td width="80"><b>Bodova</b></td></tr>
			<?
		} else {
			?>
				<tr><td width="40"><b>R. br.</b></td><td width="80"><b>Br. indeksa</b></td><td width="80"><b>Bodova</b></td></tr>
			<?
		}

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if ($brindexa[$stud_id]=="") $brindexa[$stud_id]="&nbsp;";


			if (array_key_exists($stud_id, $rezultati))
				$rezultat = $rezultati[$stud_id];
			else
				$rezultat = "/";

			if ($imena == 1) {
				?>
				<tr>
					<td><?=$n++?>.</td>
					<td><?=$imeprezime[$stud_id]?></td>
					<td><?=$brindexa[$stud_id]?></td>
					<td><?=$rezultat?></td>
				</tr>
				<?
			} else {
				?>
				<tr>
					<td><?=$n++?>.</td>
					<td><?=$brindexa[$stud_id]?></td>
					<td><?=$rezultat?></td>
				</tr>
				<?
			}
		}

		?>
				<!--/table></td></tr-->
			</table>
			<p>&nbsp;</p>
		<?
		
	}

	print "</center>";

}


$obavijest_uvid = str_replace("\n", "<br>\n", htmlentities($_REQUEST['obavijest_uvid'], ENT_COMPAT | ENT_HTML401, "UTF-8"));



?>

<p><?=$obavijest_uvid?></p>

<p>Sarajevo, <?=date("d. m. Y.");?></p>


<table border="0" width="100%">
<tr>
	<td width="60%">&nbsp;</td>
	<td width="40%" align="center"><p>Predmetni nastavnik:<br /><br /><br /><?=$nastavnik?></p></td>
</tr>
</table>
<?

}

?>
