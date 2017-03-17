<?

// IZVJESTAJ/GRUPE - spisak studenata po grupama



function izvjestaj_grupe() {

global $userid,$user_siteadmin,$user_studentska;

require_once("lib/utility.php"); // bssort


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>

<?


$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']); // akademska godina

$tip = $_REQUEST['tip'];
if (intval($_REQUEST['double'])==1 && $tip=="") $tip = "double"; // kompatibilnost unazad
$komentari = intval($_REQUEST['komentari']);
$prisustvo = intval($_REQUEST['prisustvo']);

$grupa = intval($_REQUEST['grupa']); // Za samo jednu grupu
if ($grupa>0) $sql_dodaj = "and id=$grupa"; else $sql_dodaj = "and virtualna=0"; // U suprotnom sakrivamo virtualnu grupu

if ($tip=="") $tip="single";


// Naziv predmeta - ovo ujedno provjerava da li predmet postoji

$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet); // nivo 3: greska
	biguglyerror("Traženi predmet ne postoji");
	return;
}
$q15 = db_query("select naziv from akademska_godina where id=$ag");
if (db_num_rows($q15)<1) {
	zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
	zamgerlog2("nepoznata akademska godina", $ag); // nivo 3: greska
	biguglyerror("Tražena godina ne postoji");
	return;
}

?>
<h1><?=db_result($q10,0,0)?></h1>
<h3>Akademska <?=db_result($q15,0,0)?> godina - Spisak grupa</h3>
<?



// Prava pristupa

$q20 = db_query("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
if (db_result($q20,0,0)<1 && !$user_siteadmin && !$user_studentska) {
	zamgerlog("permisije (predmet pp$predmet)",3);
	zamgerlog2("nije saradnik na predmetu", $predmet, $ag);
	biguglyerror("Nemate permisije za pristup ovom izvještaju");
	return;
}


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



// Dvije kolone

if ($tip=="double") {
	print '<table width="100%" border="0">'."\n";

	$parni=0;

	$q400 = db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag $sql_dodaj");
	$grupe = array();
	while ($r400 = db_fetch_row($q400)) $grupe[$r400[0]] = $r400[1];

	natsort($grupe);

	foreach ($grupe as $id => $naziv) {
		if ($parni == 0) 
			print "<tr>\n";
		else
			print "</td>\n";
		?>
		<td width="12%">&nbsp;</td>
		<td width="31%" valign="top">
			<table width="100%" border="1" cellspacing="0" cellpadding="2">
				<tr><td colspan="2"><b><?=$naziv?></b></td></tr>
				<tr><td width="80%"><?

		$idovi = array();
		$q405 = db_query("select student from student_labgrupa where labgrupa=$id");
		while ($r405 = db_fetch_row($q405)) $idovi[]=$r405[0];

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if (!in_array($stud_id,$idovi)) continue;
			unset($imeprezime[$stud_id]);

			$imena .= "$n. $stud_imepr<br/>\n";
			$brojevi_indexa .= $brindexa[$stud_id]."<br/>\n";
			unset($brindexa[$stud_id]);
			$n++;
		}

		?><?=$imena?>
				</td><td width="20%"><?=$brojevi_indexa?></td></tr>
			</table>
		<?
		$imena=$brojevi_indexa="";

		if ($parni==1) {
			$parni=0;
			?>
		</td>
		<td width="12%">&nbsp;</td></tr>
		<tr><td colspan="5">&nbsp;</td></tr>
		<?
		} else $parni=1;
	}

	if ($grupa==0 && count($imeprezime)>0) {
		if ($parni == 0) 
			print "<tr>\n";
		else
			print "</td>\n";
		?>
		<td width="12%">&nbsp;</td>
		<td width="31%" valign="top">
			<table width="100%" border="1" cellspacing="0" cellpadding="2">
				<tr><td colspan="2"><b>Nisu ni u jednoj grupi</b></td></tr>
				<tr><td width="80%"><?

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			$imena .= "$n. $stud_imepr<br/>\n";
			$brojevi_indexa .= $brindexa[$stud_id]."<br/>\n";
			$n++;
		}
		?><?=$imena?>
				</td><td width="20%"><?=$brojevi_indexa?>
				</td></tr>
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

	$q400 = db_query("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag $sql_dodaj");
	$grupe = array();
	while ($r400 = db_fetch_row($q400)) $grupe[$r400[0]] = $r400[1];

	natsort($grupe);

	foreach ($grupe as $id => $naziv) {
		?>
			<table width="<?=$sirina_tabele?>" border="2" cellspacing="0">
				<tr><td colspan="<?=$nr?>" align="center"><b><?=strtoupper($naziv)?></b></td></tr>
				<tr><td width="40"><b>R. br.</b></td><td><b>Prezime i ime</b></td><td width="80"><b>Br. indeksa</b></td>
		<?

		if ($prisustvo>0) { ?><td colspan="7" align="center"><b>I semestar</b></td><td colspan="7" align="center"><b>II semestar</b></td><? }

		if ($komentari>0) { ?><td align="center"><b>Komentari</b></td><? }
		print "</tr>\n";

		$idovi = array();
		$q405 = db_query("select student from student_labgrupa where labgrupa=$id");
		while ($r405 = db_fetch_row($q405)) $idovi[]=$r405[0];

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if (!in_array($stud_id,$idovi)) continue;
			unset($imeprezime[$stud_id]);

			if ($brindexa[$stud_id]=="") $brindexa[$stud_id]="&nbsp;";

			$komentar="";
			if ($komentari>0) {
				$q402 = db_query("select UNIX_TIMESTAMP(datum),komentar from komentar where student=$stud_id and labgrupa=$id order by id");
				$i=0;
				while ($r402 = db_fetch_row($q402)) {
					if ($i>0) $komentar .= "<br/>\n";
					$i=1;
					$komentar .= "(".date("d. m. Y.",$r402[0]).") ".$r402[1];
				}
				if (db_num_rows($q402)<1) $komentar = "&nbsp;";
			}

			?>
				<tr>
					<td><?=$n++?></td>
					<td><?=$stud_imepr?></td>
					<td><?=$brindexa[$stud_id]?></td>
			<?
			if ($prisustvo>0)
				for ($i=0; $i<14; $i++) print "<td>&nbsp;</td>";
			if ($komentari>0)
				print "<td>$komentar</td>\n";
			print "</tr>\n";
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
		$q410 = db_query("select id from labgrupa where predmet=$predmet and akademska_godina=$ag and virtualna=1");
		$id_grupe_svi_studenti = db_result($q410,0,0);

		?>
			<table width="<?=$sirina_tabele?>" border="2" cellspacing="0">
				<tr><td colspan="<?=$nr?>"><b>Nisu ni u jednoj grupi</b></td></tr>
				<tr><td>&nbsp;</td><td>Prezime i ime</td><td>Br. indeksa</td>
		<?

		if ($prisustvo>0) { ?><td colspan="7" align="center"><b>I semestar</b></td><td colspan="7" align="center"><b>II semestar</b></td><? }

		if ($komentari>0) { ?><td align="center"><b>Komentari</b></td><? }
		print "</tr>\n";

		$n=1;
		foreach ($imeprezime as $stud_id => $stud_imepr) {
			if ($brindexa[$stud_id]=="") $brindexa[$stud_id]="&nbsp;";

			$komentar="";
			if ($komentari>0) {
				$q402 = db_query("select UNIX_TIMESTAMP(datum),komentar from komentar where student=$stud_id and labgrupa=$id_grupe_svi_studenti order by id");
				$i=0;
				while ($r402 = db_fetch_row($q402)) {
					if ($i>0) $komentar .= "<br/>\n";
					$i=1;
					$komentar .= "(".date("d. m. Y.",$r402[0]).") ".$r402[1];
				}
				if (db_num_rows($q402)<1) $komentar = "&nbsp;";
			}

			?>
				<tr>
					<td><?=$n++?></td>
					<td><?=$stud_imepr?></td>
					<td><?=$brindexa[$stud_id]?></td>
			<?
			if ($prisustvo>0)
				for ($i=0; $i<14; $i++) print "<td>&nbsp;</td>";
			if ($komentari>0)
				print "<td>$komentar</td>\n";
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



?>
