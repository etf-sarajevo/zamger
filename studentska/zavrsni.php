<?php

// STUDENTSKA/ZAVRSNI - modul za definisanje zavrsnih radova, parametara, komisije, termina odbrane i konacne ocjene

function studentska_zavrsni()  {
	require("lib/manip.php"); // radi ispisa studenata sa predmeta
	global $userid, $user_nastavnik, $user_siteadmin;
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
		$q100 = myquery("SELECT DISTINCT pk.predmet, pk.akademska_godina, p.naziv FROM ponudakursa as pk, akademska_godina as ag, predmet as p WHERE pk.akademska_godina = ag.id AND ag.aktuelna=1 AND pk.predmet=p.id AND SUBSTRING(p.naziv, 1, 12)='Završni rad' ORDER BY p.naziv");
		if (mysql_num_rows($q100) == 0) {
			niceerror("Nije definisan niti jedan predmet za završni rad.");
		}
		while ($r100 = mysql_fetch_row($q100)) {
			?><li><a href="?sta=studentska/zavrsni&predmet=<?=$r100[0]?>&ag=<?=$r100[1]?>"><?=$r100[2]?></a></li><?
		}
		print "</ul>";
		return;
	} else {
		$q110 = myquery("SELECT naziv FROM predmet WHERE id=$predmet");
		if (mysql_num_rows($q110)<1) {
			biguglyerror("Nepostojeći predmet");
			return;
		}
		?>
		<h2><?=mysql_result($q110,0,0)?></h2>
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
				<li class="last"><a href="<?=$linkPrefix."&akcija=dodaj_zavrsni" ?>">Nova tema završnog rada</a></li>
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
		$q901 = myquery("SELECT id, naziv, kratki_pregled, mentor, student, predsjednik_komisije, clan_komisije, UNIX_TIMESTAMP(termin_odbrane), konacna_ocjena FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
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
				<td>Kratki pregled</td>
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

			$predsjednik_komisije = dajIme($r901[5], $naucni_stepen);
			if ($predsjednik_komisije=="") $predsjednik_komisije = "<font color=\"gray\">(nije definisan)</font>";

			$clan_komisije = dajIme($r901[6], $naucni_stepen);
			if ($clan_komisije=="") $clan_komisije = "<font color=\"gray\">(nije definisan)</font>";

			$termin_odbrane = date("d.m.Y h:i",$r901[7]);
			if ($r901[7] == 0) $termin_odbrane = "<font color=\"gray\">(nije definisan)</font>";

			$konacna_ocjena = $r901[8];
			if ($r901[8] == 5) $konacna_ocjena = "<font color=\"gray\">(nije odbranjen)</font>";

			?>
			<tr>
				<td><?=$rbr?>.</td>
				<td><?=$naziv_teme?></td>
				<td><?=$kratki_pregled?></td>
				<td><?=$mentor?></td>
				<td><?=$student?></td>
				<td><?=$predsjednik_komisije?></td>
				<td><?=$clan_komisije?></td>
				<td><?=$termin_odbrane?></td>
				<td><?=$konacna_ocjena?></td>
				<td><a href="?sta=studentska/zavrsni&akcija=izmjena_zavrsni&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">(izmijeni)</a>
				<a href="?sta=studentska/zavrsni&akcija=obrisi_zavrsni&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">(obriši)</a></td>
			</tr>
			<?
		} // while ($r901...

		?></table><?
	} // if (!isset($akcija) - lista završnih radova


	// Akcija DODAJ TEMU ZAVRSNOG RADA

	elseif ($akcija == 'dodaj_zavrsni')  {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$predmet = intval($_REQUEST['predmet']);
			$ag = intval($_REQUEST['ag']);
			$smjer = intval($_REQUEST['smjer']);

			$naziv = my_escape(trim($_REQUEST['naziv']));
			$kratki_pregled  = my_escape(trim($_REQUEST['kratki_pregled']));
			$mentor = intval($_REQUEST['mentor']);
			$predsjednik_komisije = intval($_REQUEST['predsjednik_komisije']);
			$clan_komisije = intval($_REQUEST['clan_komisije']);
			$literatura = my_escape(trim($_REQUEST['literatura']));
	
			$id = intval($_REQUEST['id']);
	
			if (empty($naziv)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Generišemo jedinstven ID
			$znesta = myquery("select id from zavrsni order by id desc limit 1");
			if (mysql_num_rows($znesta)<1)
				$id = 1;
			else
				$id = mysql_result($znesta,0,0)+1;

			$zr = "Završni rad";
			$np = "nastavnik";
			$q906 = myquery("INSERT INTO zavrsni (id, naziv, predmet, akademska_godina, kratki_pregled, literatura, mentor, predsjednik_komisije, clan_komisije) VALUES ($id, '$naziv', '$predmet', '$ag',  '$kratki_pregled', '$literatura', $mentor, $predsjednik_komisije, $clan_komisije)");
			nicemessage('Nova tema završnog rada je uspješno dodana.');
			zamgerlog("dodana nova tema završnog rada na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}
		

		$q99 = myquery("select id, titula from naucni_stepen");
		while ($r99 = mysql_fetch_row($q99))
			$naucni_stepen[$r99[0]]=$r99[1];

		?>	
		<h2>Nova tema završnog rada</h2>
		<?=genform("POST", "addForm");?>
			<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
				
				<div class="row">
					<span class="label">Smjer studija: *</span>
					<span class="formw">
			 			<select name="smjer"><?
							$cnt3 = 0;
							$q950 = myquery("SELECT s.id, s.naziv FROM studij as s, ponudakursa as pk WHERE pk.studij=s.id AND pk.predmet=$predmet AND pk.akademska_godina=$ag ORDER BY s.naziv");
							$rowcounter3 = 0;
							while ($r950 = mysql_fetch_row($q950)) {
								$cnt3 = $cnt3 + 1;
								?>
								<option value="<?=$r950[0]?>"><?=$r950[1]?></option>
								<?
							}
							?>
						</select>
					</span> 
				</div>
				<div class="row">
					<span class="label">Mentor *</span>
					<span class="formw">
						<select name="mentor"><?
							$cnt5 = 0;
							$q952 = myquery("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = mysql_fetch_row($q952)) {
								$cnt5 = $cnt5 + 1;
								?>
								<option value="<?=$r952[0]?>"><?=$r952[2]?> <?=$naucni_stepen[$r952[3]]?> <?=$r952[1]?></option>
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
								?>
								<option value="<?=$r952[0]?>"><?=$r952[2]?> <?=$naucni_stepen[$r952[3]]?> <?=$r952[1]?></option>
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
								?>
								<option value="<?=$r952[0]?>"><?=$r952[2]?> <?=$naucni_stepen[$r952[3]]?> <?=$r952[1]?></option>
								<?
							}
						?>
						</select>
					</span> 
				</div>
				<div class="row">
					<span class="label">Naziv teme *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
				</div>
				<div class="row">
					<span class="label">Kratki pregled</span>
					<span class="formw"><textarea name="kratki_pregled" cols="60" rows="15" wrap="physical" id="kratki_pregled"></textarea></span> 
				</div>
				<div class="row">
					<span class="label">Preporučena literatura</span>
					<span class="formw"><textarea name="literatura" cols="60" rows="15" wrap="physical" id="literatura"></textarea></span> 
				</div>
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			</div><!--formDiv-->
		</form>
		<?
	}

	// Akcija IZMJENA TEME ZAVRSNOG RADA

	elseif ($akcija == 'izmjena_zavrsni')  {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token())  {
			// Poslana forma za izmjenu teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$kratki_pregled  = my_escape(trim($_REQUEST['kratki_pregled']));
			$literatura  = my_escape(trim($_REQUEST['literatura']));
			$nastavnik = my_escape(trim($_REQUEST['nastavnik']));
			$student = my_escape(trim($_REQUEST['student']));
	
			if (empty($naziv) || empty($kratki_pregled) || empty($nastavnik) || empty($student) || empty($literatura))  {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q907 = myquery("select count(*) from zavrsni where id=$id");
			if (mysql_result($q907,0,0)==0) {
				niceerror("Završni rad sa IDom $id ne postoji.");
				nicemessage('<a href="'.$linkPrefix.'">Povratak.</a>');
				return;
			}

			$q908 = myquery("UPDATE zavrsni SET naziv='$naziv', kratki_pregled='$kratki_pregled', literatura='$literatura', nastavnik='$nastavnik', student='$student' WHERE id='$id'");

			nicemessage('Uspješno ste izmijenili temu završnog rada.');
			zamgerlog("izmijenio temu završnog rada $id na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		// Prikaz forme
		$q909 = myquery("SELECT naziv, mentor, student, kratki_pregled, literatura FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");

		?>
		<h1>Izmijeni temu završnog rada</h1>
		<?=genform("POST", "editForm");?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?=mysql_result($q900,0,0)?>" /></span> 
				</div> 
                <div class="row">
					<span class="label">Nastavnik *</span>
					<span class="formw"><input name="nastavnik" type="text" id="nastavnik" size="70" value="<?=mysql_result($q900,0,1)?>"  /></span>
				</div>
                <div class="row">
					<span class="label">Student *</span>
					<span class="formw"><input name="student" type="text" id="student" size="70" value="<?=mysql_result($q900,0,2)?>"  /></span>
				</div>
                <div class="row">
					<span class="label">Kratki pregled *</span>
					<span class="formw"><textarea name="kratki_pregled" cols="60" rows="15" wrap="physical" id="kratki_pregled"><?=mysql_result($q900,0,3)?></textarea></span>
				</div>
                <div class="row">
					<span class="label">Preporučena literatura *</span>
					<span class="formw"><textarea name="literatura" cols="60" rows="15" wrap="physical" id="literatura"><?=mysql_result($q900,0,4)?></textarea></span>
				</div>
				
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			
			</div><!--formDiv-->
		</form>
		<?
	} //akcija == izmjena_zavrsni


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
			
			// Ispis studenata sa teme zavrsnog rada
			$q918 = myquery("DELETE FROM student_zavrsni WHERE zavrsni=$id");

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

	// Akcija STRANICE ZAVRSNIH RADOVA

	elseif ($akcija == 'zavrsni_stranica') {
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica
	
} // function

?>
