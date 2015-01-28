<?php
// NASTAVNIK/ZAVRSNI 

function nastavnik_zavrsni() {

	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	

	// Da li korisnik ima pravo ući u modul?
	if (!$user_siteadmin) {
		$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/završni privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	$linkPrefix = "?sta=nastavnik/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	?>
	<h2>Završni rad</h2>
	<?
	
	//Preuzimanje parametara završnih radova
	$q900 = myquery("SELECT naslov, kratki_pregled, literatura, student FROM  zavrsni WHERE predmet=$predmet AND akademska_godina=$ag");
	if (mysql_num_rows($q900)<1)
		$nema_parametara = true;
	else {
		$nema_parametara = false;
		$param_naziv = mysql_result($q900,0,0);
		$param_kratki_pregled = mysql_result($q900,0,1);
		$param_literatura = mysql_result($q900,0,2);
	}

	
	// Default akcija - LISTA ZAVRSNIH RADOVA
	if (!isset($akcija)) {
		?>
		<h3>Lista tema završnih radova</h3>

		<p>Radovi za koje ste mentor:</p>
		<?

		// Početne informacije
		$q900 = myquery("SELECT id, naslov, kratki_pregled, mentor, student, predsjednik_komisije, clan_komisije, UNIX_TIMESTAMP(termin_odbrane), kandidat_potvrdjen FROM zavrsni WHERE mentor=$userid and predmet=$predmet AND akademska_godina=$ag ORDER BY mentor,naslov");
		if (mysql_num_rows($q900) == 0) {
			?>
			<span class="notice">Niste mentor niti za jedan rad.</span>	
			<?
		} else {

			?>
			<table border="1" cellspacing="0" cellpadding="4">
				<tr bgcolor="#CCCCCC">
					<td>R.br.</td>
					<td>Naslov</td>
					<td>Mentor</td>
					<td>Student</td>
					<td>Predsjednik komisije</td>
					<td>Član komisije</td>
					<td>Termin odbrane</td>
					<td>Konačna ocjena</td>
					<td>Akcije</td>
				</tr>
			<?

	
			while ($r900 = mysql_fetch_row($q900)) {
				$rbr++;
				$id_zavrsni = $r900[0];
				$naslov_teme = $r900[1];

				$kratki_pregled = $r900[2];
				if ($kratki_pregled == "") $kratki_pregled = $nema;
				else $kratki_pregled = substr($kratki_pregled, 0, 200)."...";
				
				$mentor = tituliraj($r900[3], false);
				if ($mentor=="") $mentor = "<font color=\"red\">(nije definisan)</font>";

				$student_id = $r900[4];
				$student = tituliraj($r900[4], false);
				if ($student=="") $student = "<font color=\"gray\">niko nije izabrao temu</font>";
				else if ($r900[8]==0) // Kandidat nije potvrđen
					$student .= "<br>(<a href=\"$linkPrefix&akcija=potvrdi_kandidata&id=$id_zavrsni\">potvrdi kandidata</a>)";

				$predsjednik_komisije = tituliraj($r900[5], false);
				if ($predsjednik_komisije=="") $predsjednik_komisije = "<font color=\"gray\">(nije definisan)</font>";

				$clan_komisije = tituliraj($r900[6], false);
				if ($clan_komisije=="") $clan_komisije = "<font color=\"gray\">(nije definisan)</font>";

				$termin_odbrane = date("d.m.Y h:i",$r900[7]);
				if ($r900[7] == 0) $termin_odbrane = "<font color=\"gray\">(nije definisan)</font>";

				$konacna_ocjena = "<font color=\"gray\">(nije ocijenjen)</font>";
				if ($student_id>0) {
					$q903 = myquery("SELECT ocjena FROM konacna_ocjena WHERE student=$student_id AND predmet=$predmet AND akademska_godina=$ag");
					if (mysql_num_rows($q903)>0 && mysql_result($q903,0,0)>5)
						$konacna_ocjena = mysql_result($q903,0,0);
				}

				?>
				<tr>
					<td><?=$rbr?>.</td>
					<td><?=$naslov_teme?></td>
					<td><?=$mentor?></td>
					<td><?=$student?></td>
					<td><?=$predsjednik_komisije?></td>
					<td><?=$clan_komisije?></td>
					<td><?=$termin_odbrane?></td>
					<td><?=$konacna_ocjena?></td>
					<td><a href="?sta=nastavnik/zavrsni&akcija=izmjena_zavrsni&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">izmijeni</a> *
					<a href="?sta=nastavnik/zavrsni&akcija=zavrsni_stranica&zavrsni=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">stranica</a> *
					<a href="?sta=nastavnik/zavrsni&akcija=dodaj_biljesku&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">bilješka</a>
					</td>
				</tr>
				<?
			} // while

			?>
			</table>
			<?
		} // else
	} // if (!isset($akcija) - lista završnih radova
	
	//Otvaranje stranica zavrsnih radova
	elseif ($akcija == 'zavrsni_stranica') {
		?> <p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p> <?
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica

	//akcija IZMJENA TEME ZAVRŠNOG RADA
	elseif ($akcija == 'izmjena_zavrsni') {
		if ($id<=0) {
			biguglyerror("Niste odabrali temu.");
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$predmet = intval($_REQUEST['predmet']);
			$ag = intval($_REQUEST['ag']);

			$naslov = my_escape(trim($_REQUEST['naslov']));
			$podnaslov = my_escape(trim($_REQUEST['podnaslov']));
			$kratki_pregled  = my_escape(trim($_REQUEST['kratki_pregled']));
			$literatura = my_escape(trim($_REQUEST['literatura']));
	
			if (empty($naslov)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q905 = myquery("UPDATE zavrsni SET naslov='$naslov', podnaslov='$podnaslov', kratki_pregled='$kratki_pregled', literatura='$literatura' WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
			nicemessage('Podaci o završnom radu uspješno izmijenjeni.');
			zamgerlog("izmijenjena tema zavrsnog rada $id na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

			return;
		}
		
		$q98 = myquery("SELECT student, mentor, predsjednik_komisije, clan_komisije, naslov, podnaslov, kratki_pregled, literatura FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
		if (mysql_num_rows($q98)<1) {
			niceerror("Nepostojeći završni rad");
			zamgerlog("spoofing zavrsnog rada $id kod izmjene teme", 3);
			return;
		}
		$id_studenta = mysql_result($q98, 0, 0);
		$id_mentora = mysql_result($q98, 0, 1);
		$id_predkom = mysql_result($q98, 0, 2);
		$id_clankom = mysql_result($q98, 0, 3);
		$naslov = mysql_result($q98, 0, 4);
		$podnaslov = mysql_result($q98, 0, 5);
		$kratki_pregled = mysql_result($q98, 0, 6);
		$literatura = mysql_result($q98, 0, 7);

		?>	
		<h3>Izmjena podataka o završnom radu</h3>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>
		<?=genform("POST", "addForm");?>
			<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				<div class="row">
					<span class="label">Naslov teme</span>
					<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$naslov?>"></span> 
				</div>
				<div class="row">
					<span class="label">Podnaslov</span>
					<span class="formw"><input name="podnaslov" type="text" id="podnaslov" size="70" value="<?=$podnaslov?>"></span> 
				</div>
				<div class="row">
					<span class="label">Kratki pregled</span>
					<span class="formw"><textarea name="kratki_pregled" cols="60" rows="10" wrap="physical" id="kratki_pregled"><?=$kratki_pregled?></textarea></span> 
				</div>
				<div class="row">
					<span class="label">Preporučena literatura</span>
					<span class="formw"><textarea name="literatura" cols="60" rows="15" wrap="physical" id="literatura"><?=$literatura?></textarea></span> 
				</div>
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"> <input type="button" id="nazad" value="Nazad" onclick="javascript:history.go(-1)"></span>
				</div>
			</div><!--formDiv-->
		</form>
		<?
	} //akcija == izmjena_zavrsni
	
	// Akcija DODAJ BILJEŠKU
	elseif ($akcija == 'dodaj_biljesku') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje bilješke
			$biljeska = my_escape($_REQUEST['biljeska']);
			$q250 = myquery("UPDATE zavrsni SET biljeska='$biljeska' WHERE id=$id");

			nicemessage('Uspješno ste dodali bilješku.');
			zamgerlog("dodao biljesku na zavrsni rad $id", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		$q260 = myquery("SELECT biljeska, naslov FROM zavrsni WHERE id=$id");

		?>
		<h3>Bilješka na završni rad: <?=mysql_result($q260,0,1)?></h3>
		<p>Ovdje možete ostaviti bilješku koja je samo vama vidljiva.</p>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>
		<?=genform('POST','addNote'); ?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" wrap="physical" id="opis"><?=mysql_result($q260,0,0)?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj biljesku

} // function
?>
