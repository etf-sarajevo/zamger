<?php

// NASTAVNIK/ZAVRSNI - nastavnički interfejs za definisanje tema završnih radova



function nastavnik_zavrsni() {

	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	require_once("lib/legacy.php"); // mb_substr

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	

	// Da li korisnik ima pravo ući u modul?
	if (!$user_siteadmin) {
		$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/završni privilegije (predmet pp$predmet)",3);
			zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
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
	$q900 = db_query("SELECT naslov, kratki_pregled, literatura, student FROM  zavrsni WHERE predmet=$predmet AND akademska_godina=$ag");
	if (db_num_rows($q900)<1)
		$nema_parametara = true;
	else {
		$nema_parametara = false;
		$param_naziv = db_result($q900,0,0);
		$param_kratki_pregled = db_result($q900,0,1);
		$param_literatura = db_result($q900,0,2);
	}

	
	// Default akcija - LISTA ZAVRSNIH RADOVA
	if (!isset($akcija)) {
		?>
		<h3>Lista tema završnih radova</h3>

		<p>Teme koje ste ponudili ili ste imenovani za mentora:</p>
		<?

		// Početne informacije
		$q900 = db_query("SELECT id, naslov, kratki_pregled, mentor, student, predsjednik_komisije, clan_komisije, UNIX_TIMESTAMP(termin_odbrane), kandidat_potvrdjen FROM zavrsni WHERE mentor=$userid and predmet=$predmet AND akademska_godina=$ag ORDER BY mentor,naslov");
		if (db_num_rows($q900) == 0) {
			?>
			<span class="notice">Nije definisana niti jedna tema.</span>	
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

	
			while ($r900 = db_fetch_row($q900)) {
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
					$q903 = db_query("SELECT ocjena FROM konacna_ocjena WHERE student=$student_id AND predmet=$predmet AND akademska_godina=$ag");
					if (db_num_rows($q903)>0 && db_result($q903,0,0)>5) {
						$konacna_ocjena = db_result($q903,0,0);
						if ($konacna_ocjena == 12)
							$konacna_ocjena = "Uspješno odbranio";
					}
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
					<td><a href="?sta=nastavnik/zavrsni&akcija=izmjena&id=<?=$id_zavrsni?>&predmet=<?=$predmet?>&ag=<?=$ag?>">izmijeni</a> *
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

		?>
		<p><a href="?sta=nastavnik/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;akcija=izmjena">Predložite novu temu</a></p>
		
		<?
		
		$q910 = db_query("SELECT z.id, z.naslov, o.ime, o.prezime 
		FROM zavrsni as z 
		LEFT JOIN osoba as o ON z.student=o.id 
		WHERE z.predmet=$predmet AND z.akademska_godina=".($ag-1)." AND z.mentor=$userid
		ORDER BY o.prezime, o.ime, z.naslov");
		if (db_num_rows($q910) > 0) {
			?>
			<p><b>Ponavljanje teme od prošle godine:</b></p>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="ponovi_temu">
			<select name="id_teme">
			<?
			while ($r910 = db_fetch_row($q910)) {
				$naslov = $r910[1];
				if (strlen($naslov)>50) $naslov = mb_substr($naslov, 0, 40) . "...";
				?>
				<option value="<?=$r910[0]?>">(<?=$r910[3]?> <?=$r910[2]?>) <?=$naslov?></option>
				<?
			}
			?>
			</select>
			<input type="submit" value=" Ponovi temu ">
			</form>
			<?
		}
	} // if (!isset($akcija) - lista završnih radova
	
	//Otvaranje stranica zavrsnih radova
	elseif ($akcija == 'zavrsni_stranica') {
		?> <p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p> <?
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica

	elseif ($akcija == 'ponovi_temu') {
		$id_teme = intval($_REQUEST['id_teme']);
		$q300 = db_query("SELECT naslov, podnaslov, rad_na_predmetu, kratki_pregled, literatura, sazetak, summary, biljeska, predsjednik_komisije, clan_komisije, student FROM zavrsni WHERE predmet=$predmet AND akademska_godina=".($ag-1)." AND mentor=$userid AND id=$id_teme");
		if (db_num_rows($q300) == 0) {
			biguglyerror("Nepostojeća tema");
			return;
		}
		$naslov = db_escape_string(db_result($q300,0,0));
		$podnaslov = db_escape_string(db_result($q300,0,1));
		$rad_na_predmetu = intval(db_result($q300,0,2));
		if ($rad_na_predmetu == 0) $rad_na_predmetu = "NULL";
		$kratki_pregled = db_escape_string(db_result($q300,0,3));
		$literatura = db_escape_string(db_result($q300,0,4));
		$sazetak = db_escape_string(db_result($q300,0,5));
		$summary = db_escape_string(db_result($q300,0,6));
		$biljeska = db_escape_string(db_result($q300,0,7));
		$predsjednik = intval(db_result($q300,0,8));
		if ($predsjednik == 0) $predsjednik="NULL";
		$clan_komisije = intval(db_result($q300,0,9));
		if ($clan_komisije == 0) $clan_komisije="NULL";
		$student = intval(db_result($q300,0,10));
		if ($student == 0) $student="NULL";
		if ($student > 0) $kandidat_potvrdjen=1; else $kandidat_potvrdjen = 0;
		$q310 = db_query("INSERT INTO zavrsni SET naslov='$naslov', podnaslov='$podnaslov', rad_na_predmetu=$rad_na_predmetu, kratki_pregled='$kratki_pregled', literatura='$literatura', sazetak='$sazetak', summary='$summary', biljeska='$biljeska', predsjednik_komisije=$predsjednik, clan_komisije=$clan_komisije, student=$student, kandidat_potvrdjen=$kandidat_potvrdjen, predmet=$predmet, akademska_godina=$ag, mentor=$userid");
		
		nicemessage('Kopirana tema od prošle godine');
		nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

		return;
	}
	
	//akcija IZMJENA TEME ZAVRŠNOG RADA
	elseif ($akcija == 'izmjena') {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$predmet = intval($_REQUEST['predmet']);
			$ag = intval($_REQUEST['ag']);

			$naslov = db_escape(trim($_REQUEST['naslov']));
			$podnaslov = db_escape(trim($_REQUEST['podnaslov']));
			$kratki_pregled  = db_escape(trim($_REQUEST['kratki_pregled']));
			$literatura = db_escape(trim($_REQUEST['literatura']));

			$kandidat = intval($_REQUEST['kandidat']);
			if ($kandidat == 0) $kandidat = "NULL";
			if ($kandidat > 0) $kandidat_potvrdjen=1; else $kandidat_potvrdjen = 0;
			
			$na_predmetu = intval($_REQUEST['na_predmetu']);
			if ($na_predmetu == 0) $na_predmetu = "NULL";
	
			if (empty($naslov)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			if ($id > 0) {
				$q905 = db_query("UPDATE zavrsni SET naslov='$naslov', podnaslov='$podnaslov', kratki_pregled='$kratki_pregled', literatura='$literatura', student=$kandidat, kandidat_potvrdjen=$kandidat_potvrdjen, rad_na_predmetu=$na_predmetu WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
				nicemessage('Podaci o završnom radu uspješno izmijenjeni.');
				zamgerlog("izmijenjena tema zavrsnog rada $id na predmetu pp$predmet", 2);
				zamgerlog2("izmijenio temu zavrsnog rada", $id);
			} else {
				$q905 = db_query("INSERT INTO zavrsni SET naslov='$naslov', podnaslov='$podnaslov', kratki_pregled='$kratki_pregled', literatura='$literatura', predmet=$predmet, akademska_godina=$ag, mentor=$userid, student=$kandidat, kandidat_potvrdjen=$kandidat_potvrdjen, rad_na_predmetu=$na_predmetu, tema_odobrena=0");
				$id = db_insert_id();
				nicemessage('Uspješno kreirana nova tema završnog rada.');
				zamgerlog("kreirana tema zavrsnog rada $id na predmetu pp$predmet", 2);
				zamgerlog2("dodao temu zavrsnog rada", $id);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

			return;
		}
		

		if ($id > 0) {
			$q98 = db_query("SELECT student, mentor, predsjednik_komisije, clan_komisije, naslov, podnaslov, kratki_pregled, literatura, rad_na_predmetu FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
			if (db_num_rows($q98)<1) {
				niceerror("Nepostojeći završni rad");
				zamgerlog("spoofing zavrsnog rada $id kod izmjene teme", 3);
				zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
				return;
			}
			$id_studenta = db_result($q98, 0, 0);
			$id_mentora = db_result($q98, 0, 1);
			$id_predkom = db_result($q98, 0, 2);
			$id_clankom = db_result($q98, 0, 3);
			$naslov = db_result($q98, 0, 4);
			$podnaslov = db_result($q98, 0, 5);
			$kratki_pregled = db_result($q98, 0, 6);
			$literatura = db_result($q98, 0, 7);
			$na_predmetu = db_result($q98, 0, 8);
			?>	
			<h3>Izmjena teme završnog rada</h3>
			<?
		} else {
			?>	
			<h3>Nova tema završnog rada</h3>
			<?
			$naslov = $podnaslov = $kratki_pregled = $literatura = "";
			$na_predmetu = $id_studenta = 0;
		}

		// Spisak predmeta na kojima je osoba odg. nastavnik iz kojih može biti predmet
		$q99 = db_query("SELECT p.id, p.naziv, i.kratki_naziv FROM predmet as p, angazman as a, institucija as i WHERE a.predmet=p.id AND a.akademska_godina=$ag AND a.osoba=$userid AND a.angazman_status=1 AND p.institucija=i.id ORDER BY p.naziv");
		$pronadjen = false;
		$prof_predmeti = "<option value=0>(nije definisan)</option>\n";
		while ($r99 = db_fetch_row($q99)) {
			if (substr($r99[1], 0, 12) == "Završni rad") continue;
			if ($r99[0] == $na_predmetu) {
				$prof_predmeti .= "<option value=\"$r99[0]\" selected>";
				$pronadjen = true;
			} else
				$prof_predmeti .= "<option value=\"$r99[0]\">";
			$prof_predmeti .= "$r99[1] ($r99[2])</option>\n";
		}
		if ($na_predmetu != 0 && $pronadjen == false) {
			// Ako je ranije već izabran predmet kojeg nema na spisku, dodaćemo ga na spisak
			$q99a = db_query("SELECT p.naziv, i.kratki_naziv FROM predmet as p, institucija as i WHERE p.id=$na_predmetu and p.institucija=i.id");
			$prof_predmeti .= "<option value=\"$na_predmetu\" selected>".db_result($q99a,0,0)." (".db_result($q99a,0,1).")</option>\n";
		}
		
		// Spisak studenata na predmetu Završni rad
		$q100 = db_query("SELECT o.id, o.ime, o.prezime, o.brindexa FROM student_predmet AS sp, ponudakursa AS pk, osoba AS o WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.id=sp.predmet AND sp.student=o.id ORDER BY o.prezime, o.ime");
		$rowcounter5 = 0;
		$studenti_ispis = "<option value=0>(nije definisan)</option>\n";
		while ($r100 = db_fetch_row($q100)) {
			$cnt5 = $cnt5 + 1;
			if ($r100[0] == $id_studenta) $opcija = " SELECTED";
			else $opcija = "";
			
			$studenti_ispis .= "<option value=\"$r100[0]\" $opcija>$r100[2] $r100[1] ($r100[3])</option>\n";
		}


		?>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>

		<style>
		label{
			display: block;
			margin: 0px 0px 15px 0px;
		}
		label > span {
			width: 100px;
			font-weight: bold;
			float: left;
			padding-top: 8px;
			padding-right: 5px;
		}
		</style>

		<?=genform("POST", "addForm");?>
			<input type="hidden" name="subakcija" value="potvrda">
			<label for="naslov"><span>Naslov teme: <font color="red">*</font></span> <input name="naslov" type="text" id="naslov" size="70" value="<?=$naslov?>"></label> 
			<label for="podnaslov"><span>Podnaslov:</span> <input name="podnaslov" type="text" id="podnaslov" size="70" value="<?=$podnaslov?>"></label>  
			<label for="predmet"><span>Predmet:</span> <select name="na_predmetu"><?=$prof_predmeti?></select></label>  
			<label for="kandidat"><span>Kandidat:</span> <select name="kandidat"><?=$studenti_ispis?></select></label>  
			<label for="kratki_pregled"><span>Kratki pregled:</span>
			<textarea name="kratki_pregled" cols="60" rows="10" id="kratki_pregled"><?=$kratki_pregled?></textarea></label> 
			<label for="literatura"><span>Preporučena literatura:</span>
			<textarea name="literatura" cols="60" rows="15" id="literatura"><?=$literatura?></textarea></label>
			<label><span>&nbsp;</span> <input type="submit" id="submit" value="Potvrdi"> <input type="button" id="nazad" value="Nazad" onclick="javascript:history.go(-1)"></label>
		</form>
		
		<p><font color="red">*</font> Polja su obavezna</p>
		<?
	} //akcija == izmjena_zavrsni
	
	// Akcija DODAJ BILJEŠKU
	elseif ($akcija == 'dodaj_biljesku') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje bilješke
			$biljeska = db_escape($_REQUEST['biljeska']);
			$q250 = db_query("UPDATE zavrsni SET biljeska='$biljeska' WHERE id=$id");

			nicemessage('Uspješno ste dodali bilješku.');
			zamgerlog("dodao biljesku na zavrsni rad $id", 2);
			zamgerlog2("dodao biljesku na zavrsni rad", $id);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		$q260 = db_query("SELECT biljeska, naslov FROM zavrsni WHERE id=$id");

		?>
		<h3>Bilješka na završni rad: <?=db_result($q260,0,1)?></h3>
		<p>Ovdje možete ostaviti bilješku koja je samo vama vidljiva.</p>
		<p><a href="<?=$linkPrefix?>">Nazad na spisak tema</a></p>
		<?=genform('POST','addNote'); ?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" id="opis"><?=db_result($q260,0,0)?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj biljesku

	elseif ($akcija == 'potvrdi_kandidata') {
		$q1000 = db_query("SELECT student FROM zavrsni WHERE mentor=$userid and id=$id AND predmet=$predmet AND akademska_godina=$ag");
		if (db_num_rows($q1000)<1 || db_result($q1000,0,0)==0) {
			niceerror("Nije definisan kandidat za ovaj rad");
			zamgerlog("spoofing zavrsnog rada $id kod potvrde kandidata", 3);
			zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
			return;
		} else {
			$q1010 = db_query("UPDATE zavrsni SET kandidat_potvrdjen=1 WHERE id=$id");
			?>
			<script>window.location = '<?=$linkPrefix?>';</script>
			<?
		}
	} //akcija == potvrdi_kandidata

} // function
?>
