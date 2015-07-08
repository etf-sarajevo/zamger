
	<h2>Izbori, imenovanja, nastavni ansambl</h2>
	<p>Za ažuriranje podataka u tabelama datim ispod zadužene su službe <?=$conf_skr_naziv_institucije_genitiv?>. Molimo Vas da sve eventualne propuste i omaške prijavite nadležnim službama! Hvala.</p>

	<?


	if ($_REQUEST['subakcija'] == "arhiva_izbora") {
		?>
		<h3>Historijski pregled izbora u zvanja</h3>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Zvanje</th><th>Datum izbora</th><th>Datum isteka</th><th>Oblast</th><th>Podoblast</th><th>Radni odnos</th><th>Druga VŠO?</th>
		</tr>
		<?

		$q500 = myquery("select zvanje, UNIX_TIMESTAMP(datum_izbora), UNIX_TIMESTAMP(datum_isteka), oblast, podoblast, dopunski, druga_institucija from izbor WHERE osoba=$osoba order by datum_isteka, datum_izbora");
		if (mysql_num_rows($q500) < 1) {
			?>
			<tr><td colspan="7">Nemamo nikakvih podataka o vašim izborima.</td></tr>
			<?
		}
		while ($r500 = mysql_fetch_row($q500)) {
			$q510 = myquery("select naziv from zvanje where id=$r500[0]");
			$nzvanje = mysql_result($q510,0,0);

			$datum_izbora = date("d. m. Y", $r500[1]);
			if ($r500[1] == 0)
				$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
			$datum_isteka = date("d. m. Y", $r500[2]);
			if ($r500[2] == 0)
				$datum_isteka = "Neodređeno";
			$oblast = $r500[3];
			if ($oblast<1)
				$oblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q520 = myquery("select naziv from oblast where id=$oblast");
				if (mysql_num_rows($q520)<1)
					$oblast = "<font color=\"red\">GREŠKA</font>";
				else
					$oblast = mysql_result($q520,0,0);
			}
			$uza_oblast = $r500[4];
			if ($uza_oblast<1)
				$uza_oblast = "<font color=\"red\">(nepoznato)</font>";
			else {
				$q530 = myquery("select naziv from podoblast where id=$uza_oblast");
				if (mysql_num_rows($q530)<1)
					$uza_oblast = "<font color=\"red\">GREŠKA</font>";
				else
					$uza_oblast = mysql_result($q530,0,0);
			}
			if ($r500[5]==0) $radniodnos = "Stalni";
			else $radniodnos = "Dopunski";

			if ($r500[6]==1) $druga_vso = "DA";

			?>
			<tr><td><?=ucfirst($nzvanje)?></td><td><?=$datum_izbora?></td><td><?=$datum_isteka?></td><td><?=$oblast?></td><td><?=$uza_oblast?></td><td><?=$radniodnos?></td><td><?=$druga_vso?></td></tr>
			<?
		}

		?>
		</table>
		<br>
		<a href="?sta=common/profil&akcija=izbori">&lt; &lt; Nazad</a>
		<?


		return;
	}



	if ($_REQUEST['subakcija'] == "arhiva_angazman") {
		?>
		<h3>Historijski pregled angažmana u nastavnom ansamblu</h3>
		<table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Akademska godina</th><th>Predmet</th><th>Status</th>
		</tr>
		<?

		$q540 = myquery("select p.id, p.naziv, angs.naziv, i.kratki_naziv, ag.naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i, akademska_godina as ag where a.osoba=$osoba and a.akademska_godina=ag.id and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by ag.naziv desc, angs.id, p.naziv");
		if (mysql_num_rows($q540) < 1) {
			?>
			<tr><td colspan="7">Nemamo nikakvih podataka o vašem angažmanu u nastavi.</td></tr>
			<?
		}
		while ($r540 = mysql_fetch_row($q540)) {
			?>
			<tr><td><?=$r540[4]?></td><td><?="$r540[1] ($r540[3])"?></td><td><?=$r540[2]?></td></tr>
			<?
		}

		?>
		</table>
		<br>
		<a href="?sta=common/profil&akcija=izbori">&lt; &lt; Nazad</a>
		<?


		return;
	}


	// Akademsko zvanje i naučni stepen

	?>
	<table border="0" width="600">
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">DOSTIGNUTO AKADEMSKO ZVANJE I NAUČNI STEPEN:</font></td></tr>
	<?

	$q430 = myquery("select strucni_stepen, naucni_stepen from osoba where id=$osoba");
	$akademsko_zvanje = "Nepoznato / Bez akademskog zvanja";
	$naucni_stepen = "Nepoznato / Bez naučnog stepena";
	if (mysql_result($q430,0,0)!=0) {
		$q440 = myquery("select naziv from strucni_stepen where id=".mysql_result($q430,0,0));
		$akademsko_zvanje = ucfirst(mysql_result($q440,0,0));
	}
	if (mysql_result($q430,0,1)!=0) {
		$q450 = myquery("select naziv from naucni_stepen where id=".mysql_result($q430,0,1));
		$naucni_stepen = ucfirst(mysql_result($q450,0,0));
	}

	?>
	<tr><td>Akademsko zvanje:</td><td><b><?=$akademsko_zvanje?></b></td></tr>
	<tr><td>Naučni stepen:</td><td><b><?=$naucni_stepen?></b></td></tr>
	<?


	// Izbori u nn zvanja

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">IZBORI U NAUČNONASTAVNA ZVANJA:</font></td></tr>
	<tr>
	<?

	$q400 = myquery("select z.naziv, UNIX_TIMESTAMP(i.datum_izbora), UNIX_TIMESTAMP(i.datum_isteka), i.oblast, i.podoblast, i.dopunski, i.druga_institucija from izbor as i, zvanje as z WHERE i.osoba=$osoba and i.zvanje=z.id order by i.datum_isteka DESC, i.datum_izbora DESC");
	if (mysql_num_rows($q400)==0) {
		?>
		<tr><td colspan="2">Nema podataka o izboru ili nikada niste bili izabrani u zvanje.</td></tr>
		<?
	} else {
		$datum_izbora = date("d. m. Y", mysql_result($q400,0,1));
		if (mysql_result($q400,0,1)==0)
			$datum_izbora = "<font color=\"red\">(nepoznato)</font>";
		$datum_isteka = date("d. m. Y", mysql_result($q400,0,2));
		if (mysql_result($q400,0,2)==0)
			$datum_isteka = "Neodređeno";
		$oblast = mysql_result($q400,0,3);
		if ($oblast<1)
			$oblast = "<font color=\"red\">(nepoznato)</font>";
		else {
			$q410 = myquery("select naziv from oblast where id=$oblast");
			if (mysql_num_rows($q410)<1)
				$oblast = "<font color=\"red\">GREŠKA</font>";
			else
				$oblast = mysql_result($q410,0,0);
		}
		$uza_oblast = mysql_result($q400,0,4);
		if ($uza_oblast<1)
			$uza_oblast = "<font color=\"red\">(nepoznato)</font>";
		else {
			$q420 = myquery("select naziv from podoblast where id=$uza_oblast");
			if (mysql_num_rows($q420)<1)
				$uza_oblast = "<font color=\"red\">GREŠKA</font>";
			else
				$uza_oblast = mysql_result($q420,0,0);
		}
		if (mysql_result($q400,0,5)==0) $radniodnos = "Stalni";
		else $radniodnos = "Dopunski";
		
		?>
		<tr><td>Naučnonastavno zvanje:</td><td><b><?=ucfirst(mysql_result($q400,0,0))?></b></td></tr>
		<tr><td>Datum izbora:</td><td><b><?=$datum_izbora?></b></td></tr>
		<tr><td>Datum isteka:</td><td><b><?=$datum_isteka?></b></td></tr>
		<tr><td>Naučna oblast:</td><td><b><?=$oblast?></b></td></tr>
		<tr><td>Uža naučna oblast:</td><td><b><?=$uza_oblast?></b></td></tr>
		<tr><td>Radni odnos:</td><td><b><?=$radniodnos?></b></td></tr>
		<?
		if (mysql_result($q400,0,6)==1) print "<tr><td colspan=\"2\"><b>Biran/a na drugoj VŠO</b></td></tr>\n";

		?>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><a href="?sta=common/profil&akcija=izbori&subakcija=arhiva_izbora">Historijski pregled izbora u zvanja</a></td></tr>
		<?
	}


	// Nastavni ansambl

	$q455 = myquery("select naziv from akademska_godina where aktuelna=1");
	$naziv_ag = mysql_result($q455, 0, 0);

	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" bgcolor="#999999"><font color="#FFFFFF">UČEŠĆE U NASTAVNOM ANSAMBLU <?=$naziv_ag?> GODINE:</font></td></tr>
	<?


	$q460 = myquery("select p.id, p.naziv, angs.naziv, i.kratki_naziv from angazman as a, angazman_status as angs, predmet as p, institucija as i, akademska_godina as ag where a.osoba=$osoba and a.akademska_godina=ag.id and ag.aktuelna=1 and a.predmet=p.id and a.angazman_status=angs.id and p.institucija=i.id order by angs.id, p.naziv");
	if (mysql_num_rows($q460) == 0) {
		?>
		<tr><td colspan="2">Niste angažovani niti na jednom predmetu u ovoj godini.</td></tr>
		<?
	}
	else {
		?>
		<tr><td valign="top">Predmeti:</td><td>
		<?
		while ($r460 = mysql_fetch_row($q460)) {
			print "$r460[1] ($r460[3]) - <b>$r460[2]</b><br>\n";
		}
		?>
		</td></tr>
		<?
	}
	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td><a href="?sta=common/profil&akcija=izbori&subakcija=arhiva_angazman">Podaci o angažmanu u nastavi ranijih godina</a></td></tr>
