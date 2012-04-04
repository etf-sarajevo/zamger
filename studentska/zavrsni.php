<?php

// STUDENTSKA/ZAVRSNI - modul za definisanje zavrsnih radova, parametara, komisije, termina odbrane i konacne ocjene

function studentska_zavrsni()  {
	require("lib/manip.php"); // radi ispisa studenata sa predmeta
	global $userid, $user_nastavnik, $user_studentska, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}

	$linkPrefix = "?sta=studentska/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
/*	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<h2>Završni rad</h2>
	<?*/

	// Da li je odabran predmet
	if ($predmet == 0) {
		?>
		<h2>Završni rad</h2>
		<p>Izaberite predmet:</p>
		<ul><?
		$q100 = myquery("SELECT DISTINCT pk.predmet, pk.akademska_godina, p.naziv, s.kratkinaziv FROM ponudakursa as pk, akademska_godina as ag, predmet as p, studij AS s WHERE pk.akademska_godina = ag.id AND ag.aktuelna=1 AND pk.predmet=p.id AND SUBSTRING(p.naziv, 1, 12)='Završni rad' AND pk.studij=s.id ORDER BY p.naziv, s.naziv");
		if (mysql_num_rows($q100) == 0) {
			niceerror("Nije definisan niti jedan predmet za završni rad.");
		}
		while ($r100 = mysql_fetch_row($q100)) {
			?><li><a href="?sta=studentska/zavrsni&predmet=<?=$r100[0]?>&ag=<?=$r100[1]?>"><?=$r100[2]?> (<?=$r100[3]?>)</a></li><?
		}
		print "</ul>";
		return;
	} else {
		$q110 = myquery("SELECT p.naziv, s.kratkinaziv FROM predmet as p, ponudakursa as pk, studij as s WHERE p.id=$predmet AND p.id=pk.predmet AND pk.akademska_godina=$ag AND pk.studij=s.id");
		if (mysql_num_rows($q110)<1) {
			biguglyerror("Nepostojeći predmet");
			return;
		}
		?>
		<h2><?=mysql_result($q110,0,0)?> (<?=mysql_result($q110,0,1)?>)</h2>
		<?
	}
	
	// Preuzimanje parametara završnih radova

	$q900 = myquery("SELECT naziv, mentor, student, kratki_pregled, literatura FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag");
	if (mysql_num_rows($q900)<1)
		$nema_parametara = true;
	else {
		$nema_parametara = false;
		$param_naziv = mysql_result($q900,0,0);
		$param_nastavnik = mysql_result($q900,0,1);
		$param_student = mysql_result($q900,0,2);
		$param_kratki_pregled = mysql_result($q900,0,3);
		$param_literatura = mysql_result($q900,0,4);
	}
	
	// Glavni meni
	if ($akcija == 'dodaj_zavrsni')  {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="?sta=studentska/zavrsni">Nazad na spisak studija/predmeta</a></li>
				<li><a href="<?=$linkPrefix ?>">Lista tema završnih radova</a></li>
				<li class="last">Nova tema završnog rada</li>
			</ul>
		</div>	
		<?
	}
	else if (!isset($akcija))  {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="?sta=studentska/zavrsni">Nazad na spisak studija/predmeta</a></li>
				<li>Lista tema završnih radova</li>
				<li class="last"><a href="<?=$linkPrefix."&akcija=izmjena_zavrsni" ?>">Nova tema završnog rada</a></li>
			</ul>
		</div>	
		<?
	}
	
	// Default akcija - LISTA ZAVRSNIH RADOVA
	if (!isset($akcija)) {
		?>
		<h2>Lista tema završnih radova</h2>
		<?

		// Početne informacije
		$q901 = myquery("SELECT id, naziv, kratki_pregled, mentor, student, predsjednik_komisije, clan_komisije, UNIX_TIMESTAMP(termin_odbrane), kandidat_potvrdjen FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
		$broj_tema = mysql_num_rows($q901);
		if ($broj_tema == 0) {
			?>
			<span class="notice">Nema kreiranih tema završnih radova.</span>	
			<?
			return;
		}

		$nema = "<font color=\"gray\">(nema)</font>";

		$q99 = myquery("select id, titula from naucni_stepen");
		while ($r99 = mysql_fetch_row($q99))
			$naucni_stepen[$r99[0]]=$r99[1];

		function dajIme($osoba, $naucni_stepen) {
			$q902 = myquery("SELECT ime, prezime, naucni_stepen FROM osoba WHERE id=$osoba");
			if (mysql_num_rows($q902)>0) {
				$r902 = mysql_fetch_row($q902);
				$ime = $r902[1]." ".$naucni_stepen[$r902[2]]." ".$r902[0];
			} else
				$ime = "";
			return $ime;
		}

		?>
		<table border="1" cellspacing="0" cellpadding="4">
			<tr bgcolor="#CCCCCC">
				<td>R.br.</td>
				<td>Naziv teme</td>
				<td>Mentor</td>
				<td>Student</td>
				<td>Predsjednik komisije</td>
				<td>Član komisije</td>
				<td>Termin odbrane</td>
				<td>Konačna ocjena</td>
				<td>Akcije</td>
			</tr>
		<?
	
		$rbr=0;
		while ($r901 = mysql_fetch_row($q901)) {
			$rbr++;
			$id_zavrsni = $r901[0];
			$naziv_teme = $r901[1];

			$kratki_pregled = $r901[2];
			if ($kratki_pregled == "") $kratki_pregled = $nema;
			else $kratki_pregled = substr($kratki_pregled, 0, 200)."...";
			
			$mentor = dajIme($r901[3], $naucni_stepen);
			if ($mentor=="") $mentor = "<font color=\"red\">(nije definisan)</font>";

			$student = dajIme($r901[4], $naucni_stepen);
			if ($student=="") $student = "<font color=\"gray\">niko nije izabrao temu</font>";
			else if ($r901[8]==0) // Kandidat nije potvrđen
				$student .= "<br>(<a href=\"$linkPrefix&akcija=potvrdi_kandidata&id=$id_zavrsni\">potvrdi kandidata</a>)";

			$predsjednik_komisije = dajIme($r901[5], $naucni_stepen);
			if ($predsjednik_komisije=="") $predsjednik_komisije = "<font color=\"gray\">(nije definisan)</font>";

			$clan_komisije = dajIme($r901[6], $naucni_stepen);
			if ($clan_komisije=="") $clan_komisije = "<font color=\"gray\">(nije definisan)</font>";

			$termin_odbrane = date("d.m.Y h:i",$r901[7]);
			if ($r901[7] == 0) $termin_odbrane = "<font color=\"gray\">(nije definisan)</font>";

			$konacna_ocjena = "<font color=\"gray\">(nije ocijenjen)</font>";
			if ($student>0) {
				$q903 = myquery("SELECT ocjena FROM konacna_ocjena WHERE student=$student AND predmet=$predmet AND akademska_godina=$ag");
				if (mysql_num_rows($q903)>0 && mysql_result($q903,0,0)>5)
					$konacna_ocjena = mysql_result($q903,0,0);
			}

			?>
			<tr>
				<td><?=$rbr?>.</td>
				<td><?=$naziv_teme?></td>
				<td><?=$mentor?></td>
				<td><?=$student?></td>
				<td><?=$predsjednik_komisije?></td>
				<td><?=$clan_komisije?></td>
				<td><?=$termin_odbrane?></td>
				<td><?=$konacna_ocjena?></td>
				<td><a href="?sta=studentska/zavrsni&akcija=izmjena_zavrsni&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">izmijeni</a> *
				<a href="?sta=studentska/zavrsni&akcija=obrisi_zavrsni&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">obriši</a> *
				<a href="?sta=studentska/zavrsni&akcija=zavrsni_stranica&zavrsni=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">stranica</a>
				</td>
			</tr>
			<?
		} // while ($r901...

		?></table><?
	} // if (!isset($akcija) - lista završnih radova


	// Akcija dodavanje ili izmjena završnog rada

	elseif ($akcija == 'izmjena_zavrsni')  {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$predmet = intval($_REQUEST['predmet']);
			$ag = intval($_REQUEST['ag']);

			$naziv = my_escape(trim($_REQUEST['naziv']));
			$kratki_pregled  = my_escape(trim($_REQUEST['kratki_pregled']));
			$mentor = intval($_REQUEST['mentor']);
			$predsjednik_komisije = intval($_REQUEST['predsjednik_komisije']);
			$clan_komisije = intval($_REQUEST['clan_komisije']);
			$student = intval($_REQUEST['student']);
			if ($student > 0) $kandidat_potvrdjen=1; else $kandidat_potvrdjen = 0;
			$literatura = my_escape(trim($_REQUEST['literatura']));
	
			if (empty($naziv)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Pronalazimo labgrupu za mentora
			if ($student > 0) {
				$q908 = myquery("select prezime, ime FROM osoba WHERE id=$mentor");
				$ime_mentora = mysql_result($q908,0,0)." ".mysql_result($q908,0,1);

				$q907 = myquery("SELECT id FROM labgrupa WHERE naziv='$ime_mentora' AND predmet=$predmet AND akademska_godina=$ag");
				if (mysql_num_rows($q907)<1) {
					$q909 = myquery("INSERT INTO labgrupa SET naziv='$ime_mentora', predmet=$predmet, akademska_godina=$ag, virtualna=0");
					$q920 = myquery("SELECT id FROM labgrupa WHERE naziv='$ime_mentora' AND predmet=$predmet AND akademska_godina=$ag");
					$id_labgrupe = mysql_result($q920,0,0);

					$q921 = myquery("SELECT COUNT(*) FROM nastavnik_predmet WHERE nastavnik=$mentor AND predmet=$predmet AND akademska_godina=$ag");
					if (mysql_result($q921,0,0)==0) {
						$q922 = myquery("INSERT INTO nastavnik_predmet SET nastavnik=$mentor, predmet=$predmet, akademska_godina=$ag, admin=0, nivo_pristupa='asistent'");
					}
					$q922 = myquery("INSERT INTO ogranicenje SET nastavnik=$mentor, labgrupa=$id_labgrupe");
				} else
					$id_labgrupe = mysql_result($q907,0,0);

				// Stavljamo studenta u grupu određenog profesora
				if ($id>0) {
					$q911 = myquery("SELECT l.id FROM student_labgrupa AS sl, labgrupa AS l WHERE sl.student=$student AND sl.labgrupa=l.id AND l.predmet=$predmet AND l.akademska_godina=$ag AND l.virtualna=0");
					while ($r911 = mysql_fetch_row($q911)) {
						$q912 = myquery("DELETE FROM student_labgrupa WHERE student=$student AND labgrupa=".$r911[0]);
					}
				}

				$q910 = myquery("INSERT INTO student_labgrupa SET student=$student, labgrupa=$id_labgrupe");
			}


	
			if ($id > 0) { // Izmjena teme
				$q905 = myquery("UPDATE zavrsni SET naziv='$naziv', kratki_pregled='$kratki_pregled', literatura='$literatura', mentor=$mentor, predsjednik_komisije=$predsjednik_komisije, clan_komisije=$clan_komisije, student=$student, kandidat_potvrdjen=$kandidat_potvrdjen WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
				nicemessage('Tema završnog rada je uspješno izmijenjena.');
				zamgerlog("izmijenjena tema završnog rada $id na predmetu pp$predmet", 2);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

			} else { // Dodavanje teme
				// Generišemo jedinstven ID
				$znesta = myquery("select id from zavrsni order by id desc limit 1");
				if (mysql_num_rows($znesta)<1)
					$id = 1;
				else
					$id = mysql_result($znesta,0,0)+1;

				$q906 = myquery("INSERT INTO zavrsni (id, naziv, predmet, akademska_godina, kratki_pregled, literatura, mentor, student, kandidat_potvrdjen, predsjednik_komisije, clan_komisije) VALUES ($id, '$naziv', $predmet, $ag,  '$kratki_pregled', '$literatura', $mentor, $student, $kandidat_potvrdjen, $predsjednik_komisije, $clan_komisije)");

				nicemessage('Nova tema završnog rada je uspješno dodana.');
				zamgerlog("dodana nova tema završnog rada $id na predmetu pp$predmet", 2);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			}
			return;
		}
		

		$q99 = myquery("select id, titula from naucni_stepen");
		while ($r99 = mysql_fetch_row($q99))
			$naucni_stepen[$r99[0]]=$r99[1];

		// Ako je definisan ID, onda je u pitanju izmjena
		if ($id>0) {
			$tekst = "Izmjena teme završnog rada";
			$q98 = myquery("SELECT student, mentor, predsjednik_komisije, clan_komisije, naziv, kratki_pregled, literatura FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
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
			$kratki_pregled = mysql_result($q98, 0, 5);
			$literatura = mysql_result($q98, 0, 6);

		} else {
			$tekst = "Nova tema završnog rada";
			$id_studenta = $id_mentora = $id_predkom = $id_clankom = 0;
			$naslov = $kratki_pregled = $literatura = "";
		}

		?>	
		<h2>Nova tema završnog rada</h2>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>
		<?=genform("POST", "addForm");?>
			<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
				
				<div class="row">
					<span class="label">Mentor *</span>
					<span class="formw">
						<select name="mentor"><?
							$cnt5 = 0;
							$q952 = myquery("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = mysql_fetch_row($q952)) {
								$cnt5 = $cnt5 + 1;
								if ($r952[0] == $id_mentora) $opcija = " SELECTED";
								else $opcija = "";
								?>
								<option value="<?=$r952[0]?>" <?=$opcija?>><?=$r952[2]?> <?=$naucni_stepen[$r952[3]]?> <?=$r952[1]?></option>
								<?
							}
						?>
						</select>
					</span> 
				</div>
				<div class="row">
					<span class="label">Predsjednik komisije *</span>
					<span class="formw">
						<select name="predsjednik_komisije"><?
							$cnt5 = 0;
							$q952 = myquery("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = mysql_fetch_row($q952)) {
								$cnt5 = $cnt5 + 1;
								if ($r952[0] == $id_predkom) $opcija = " SELECTED";
								else $opcija = "";
								?>
								<option value="<?=$r952[0]?>" <?=$opcija?>><?=$r952[2]?> <?=$naucni_stepen[$r952[3]]?> <?=$r952[1]?></option>
								<?
							}
						?>
						</select>
					</span> 
				</div>
				<div class="row">
					<span class="label">Član komisije *</span>
					<span class="formw">
						<select name="clan_komisije"><?
							$cnt5 = 0;
							$q952 = myquery("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = mysql_fetch_row($q952)) {
								$cnt5 = $cnt5 + 1;
								if ($r952[0] == $id_clankom) $opcija = " SELECTED";
								else $opcija = "";
								?>
								<option value="<?=$r952[0]?>" <?=$opcija?>><?=$r952[2]?> <?=$naucni_stepen[$r952[3]]?> <?=$r952[1]?></option>
								<?
							}
						?>
						</select>
					</span> 
				</div>
				<div class="row">
					<span class="label">Naslov teme *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?=$naslov?>"></span> 
				</div>
				<div class="row">
					<span class="label">Student</span>
					<span class="formw">
						<select name="student">
							<option value="0" CHECKED>(niko nije preuzeo temu)</option><?
							$q954 = myquery("SELECT o.id, o.ime, o.prezime, o.brindexa FROM student_predmet AS sp, ponudakursa AS pk, osoba AS o WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.id=sp.predmet AND sp.student=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r954 = mysql_fetch_row($q954)) {
								$cnt5 = $cnt5 + 1;
								if ($r954[0] == $id_studenta) $opcija = " SELECTED";
								else $opcija = "";
								?>
								<option value="<?=$r954[0]?>" <?=$opcija?>><?=$r954[2]?> <?=$r954[1]?> (<?=$r954[3]?>)</option>
								<?
							}
						?>
						</select>
					</span> 
				</div>
				<div class="row">
					<span class="label">Kratki pregled</span>
					<span class="formw"><textarea name="kratki_pregled" cols="60" rows="15" wrap="physical" id="kratki_pregled"><?=$kratki_pregled?></textarea></span> 
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
	}


	// Akcija OBRIŠI TEMU ZAVRSNOG RADA

	elseif ($akcija == 'obrisi_zavrsni')  {
		$q999 = myquery("SELECT naziv FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
		if (mysql_num_rows($q999)==0) {
			niceerror("Nepoznat rad");
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token())  {
			// Brisanje teme zavrsnog rada

			// Brisanje fajlova
			$lokacijafajlova = "$conf_files_path/zavrsni/fajlovi/";
			// ??? fali još nešto
			
			// Brisanje same teme zavrsnog
			$q919 = myquery("DELETE FROM zavrsni WHERE id=$id");

			nicemessage('Uspješno ste obrisali temu završnog rada.');	
			zamgerlog("izbrisana tema završnog rada $id na predmetu pp$predmet", 4);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		<h3>"<?=mysql_result($q999,0,0)?>"</h3>
		Da li ste sigurni da želite obrisati ovu temu završnog rada? Svi podaci vezani za aktivnosti na ovoj temi će biti nepovratno izgubljeni.<br />
		<?=genform('POST'); ?>
		<input type="hidden" name="subakcija" value="potvrda">
		<input type="submit" value="Potvrda brisanja">
		<input type="button" onclick="location.replace('<?=$linkPrefix?>');" value="Odustani">
		</form>
		<?
	
	} //akcija == obrisi_temu

	// Akcija potvrda kandidata

	elseif ($akcija == 'potvrdi_kandidata') {
		$q1000 = myquery("SELECT student FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
		if (mysql_num_rows($q1000)<1 || mysql_result($q1000,0,0)==0) {
			niceerror("Nije definisan kandidat za ovaj rad");
			zamgerlog("spoofing zavrsnog rada $id kod potvrde kandidata", 3);
			return;
		} else {
			$q1010 = myquery("UPDATE zavrsni SET kandidat_potvrdjen=1 WHERE id=$id");
			?>
			<script>window.location = '<?=$linkPrefix?>';</script>
			<?
		}
	} //akcija == potvrdi_kandidata

	// Akcija STRANICE ZAVRSNIH RADOVA

	elseif ($akcija == 'zavrsni_stranica') {
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica
	
} // function

?>
