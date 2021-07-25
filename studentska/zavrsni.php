<?php

// STUDENTSKA/ZAVRSNI - modul za definisanje zavrsnih radova, parametara, komisije, termina odbrane i konacne ocjene



function studentska_zavrsni()  {

	require("lib/manip.php"); // radi ispisa studenata sa predmeta
	
	global $userid, $user_nastavnik, $user_studentska, $user_siteadmin, $user_sefodsjeka;
	global $conf_files_path, $conf_jasper, $conf_jasper_url;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$order_by = db_escape($_REQUEST['order_by']);
	if ($order_by == "") $order_by = "naslov";
	$dir = db_escape($_REQUEST['dir']);
	if ($dir != "desc" && $dir != "asc") $dir="";

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin && !$user_sefodsjeka) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska");
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}

	$linkPrefix = "?sta=studentska/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);

	// Da li je odabran predmet
	if ($predmet == 0) {
		?>
		<form action="index.php" method="GET">
		<input type="hidden" name="sta" value="studentska/zavrsni">
		<h2>Završni rad</h2>
		<p>Akademska godina: <select name="ag">
		<?
		
		$ag = intval($_REQUEST['ag']);
		$q95 = db_query("SELECT id, naziv, aktuelna FROM akademska_godina ORDER BY id DESC");
		while ($r95 = db_fetch_row($q95)) {
			if ($r95[0] == $ag) {
				$add = "SELECTED";
			} else if ($ag == 0 && $r95[2] == 1) {
				$add = "SELECTED";
				$ag = $r95[0];
			} else
				$add = "";
			print "<option value=\"$r95[0]\" $add>$r95[1]</option>\n";
		}

		?>
		</select> <input type="submit" value=" Ok ">
		</form>
		
		<p>Izaberite predmet:</p>
		<ul><?
		
		$q100 = db_query("SELECT DISTINCT pk.predmet, pk.akademska_godina, p.naziv, s.kratkinaziv, s.naziv
		FROM ponudakursa as pk, predmet as p, studij AS s, akademska_godina_predmet as agp 
		WHERE pk.akademska_godina = $ag AND pk.predmet=p.id AND agp.predmet=p.id and agp.akademska_godina=$ag and (agp.tippredmeta=1000 OR agp.tippredmeta=1001) AND pk.studij=s.id 
		ORDER BY p.naziv, s.naziv"); // 1000 ili 1001 = završni rad
		if (db_num_rows($q100) == 0) {
			niceerror("Nije definisan niti jedan predmet sa tipom predmeta Završni rad.");
		}
		while ($r100 = db_fetch_row($q100)) {
			?><li><a href="?sta=studentska/zavrsni&predmet=<?=$r100[0]?>&ag=<?=$r100[1]?>"><?=$r100[2]?> (<?=$r100[3]?>)</a></li><?
		}
		?>
		</ul>
		<?

		// Izvještaj "spisak završenih studenata"
		?>
		<h3>Izvještaji</h3>
		<p>- <a href="?sta=izvjestaj/zavrsni_spisak&ciklus=1&ag=<?=$ag?>">Spisak završenih studenata 1. ciklusa</a><br>
		- <a href="?sta=izvjestaj/zavrsni_spisak&ciklus=2&ag=<?=$ag?>">Spisak završenih studenata 2. ciklusa</a></p>
		<?

		return;
	} else {
		$q110 = db_query("SELECT p.naziv, s.kratkinaziv FROM predmet as p, ponudakursa as pk, studij as s WHERE p.id=$predmet AND p.id=pk.predmet AND pk.akademska_godina=$ag AND pk.studij=s.id");
		if (db_num_rows($q110)<1) {
			biguglyerror("Nepostojeći predmet");
			return;
		}
		?>
		<h2><?=db_result($q110,0,0)?> (<?=db_result($q110,0,1)?>)</h2>
		<?
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
		<h3>Lista tema završnih radova</h3>
		<?

		// Početne informacije
		if ($order_by == "student") $order_by="o.prezime $dir, o.ime $dir";
		if ($order_by == "mentor") $order_by="o2.prezime $dir, o2.ime $dir";
		if ($order_by == "naslov") $order_by="z.naslov $dir";
		if ($order_by == "termin") $order_by="z.termin_odbrane $dir";

		$q900 = db_query("SELECT z.id, z.naslov, z.kratki_pregled, z.mentor, z.student, z.predsjednik_komisije, z.clan_komisije, UNIX_TIMESTAMP(z.termin_odbrane), z.kandidat_potvrdjen, z.drugi_mentor
		FROM zavrsni as z
		LEFT JOIN osoba as o ON z.student=o.id 
		LEFT JOIN osoba as o2 ON z.mentor=o2.id 
		WHERE z.predmet=$predmet AND z.akademska_godina=$ag
		ORDER BY $order_by"); // Prikazati i drugog mentora na spisku?
		$broj_tema = db_num_rows($q900);
		if ($broj_tema == 0) {
			?>
			<span class="notice">Nema kreiranih tema završnih radova.</span>	
			<?
			return;
		}

		$nema = "<font color=\"gray\">(nema)</font>";
		
		$ocjene = array("Šest", "Sedam", "Osam", "Devet", "Deset");
		
		?>
		<table border="1" cellspacing="0" cellpadding="4">
			<tr bgcolor="#CCCCCC">
				<td>R.br.</td>
				<td><a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=naslov&amp;dir=asc"><img src="static/images/up_red.png" width="10" height="10" border="0"></a> Naslov <a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=naslov&amp;dir=desc"><img src="static/images/down_red.png" width="10" height="10" border="0"></a></td>
				<td><a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=mentor&amp;dir=asc"><img src="static/images/up_red.png" width="10" height="10" border="0"></a> Mentor <a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=mentor&amp;dir=desc"><img src="static/images/down_red.png" width="10" height="10" border="0"></a></td>
				<td><a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=student&amp;dir=asc"><img src="static/images/up_red.png" width="10" height="10" border="0"></a> Student <a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=student&amp;dir=desc"><img src="static/images/down_red.png" width="10" height="10" border="0"></a></td>
				<td>Predsjednik komisije</td>
				<td>Član komisije</td>
				<td><a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=termin&amp;dir=asc"><img src="static/images/up_red.png" width="10" height="10" border="0"></a> Termin odbrane <a href="?sta=studentska/zavrsni&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;order_by=termin&amp;dir=desc"><img src="static/images/down_red.png" width="10" height="10" border="0"></a></td>
				<td>Konačna ocjena</td>
				<td>Akcije</td>
			</tr>
		<?
	
		$rbr=0;
		while ($r900 = db_fetch_row($q900)) {
			$rbr++;
			$id_zavrsni = $r900[0];
			$naslov_teme = $r900[1];

			$kratki_pregled = $r900[2];
			if ($kratki_pregled == "") $kratki_pregled = $nema;
			else $kratki_pregled = substr($kratki_pregled, 0, 200)."...";
			
			$mentor = tituliraj($r900[3], false);
			if ($mentor=="") $mentor = "<font color=\"red\">(nije definisan)</font>";
			
			if ($r900[9]) {
				$mentor .= "<br>" . tituliraj($r900[9], false);
			}

			$student_id = intval($r900[4]);
			$q910 = db_query("select prezime, ime from osoba where id=$student_id");
			if ($student_id == 0 || db_num_rows($q910)<0) $student = "<font color=\"gray\">niko nije izabrao temu</font>";
			else {
				$student = db_result($q910,0,0)." ".db_result($q910,0,1);
				if ($r900[8]==0) // Kandidat nije potvrđen
					$student .= "<br>(<a href=\"$linkPrefix&akcija=potvrdi_kandidata&id=$id_zavrsni\">potvrdi kandidata</a>)";
			}

			$predsjednik_komisije = tituliraj($r900[5], false);
			if ($predsjednik_komisije=="") $predsjednik_komisije = "<font color=\"gray\">(nije definisan)</font>";

			$clan_komisije = tituliraj($r900[6], false);
			if ($clan_komisije=="") $clan_komisije = "<font color=\"gray\">(nije definisan)</font>";

			$termin_odbrane = date("d.m.Y H:i",$r900[7]);
			if ($r900[7] == 0) $termin_odbrane = "<font color=\"gray\">(nije definisan)</font>";

			$konacna_ocjena = "<font color=\"gray\">(nije ocijenjen)</font>";
			if ($student_id>0) {
				$ocjena = db_get("SELECT ocjena FROM konacna_ocjena WHERE student=$student_id AND predmet=$predmet AND akademska_godina=$ag");
				if ($ocjena == 12)
					$konacna_ocjena = "Uspješno odbranio";
				else if ($ocjena > 5)
					$konacna_ocjena = $ocjena . " (" . $ocjene[$ocjena-6] . ")";
			}
			
			$url = "?sta=studentska/zavrsni&amp;id=$id_zavrsni&amp;predmet=$predmet&amp;ag=$ag";

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
				<td><a href="<?=$url?>&amp;akcija=izmjena_zavrsni">izmijeni</a> *
				<a href="<?=$url?>&amp;akcija=obrisi_zavrsni">obriši</a> *
				<a href="<?=$url?>&amp;akcija=zavrsni_stranica&amp;zavrsni=<?=$id_zavrsni?>">stranica</a><?
				if ($student_id > 0) {
					?> *
					<a href="<?=$url?>&amp;akcija=izvjestaji&amp;id=<?=$id_zavrsni?>">izvještaji</a>
					<?
				}
				?>
				</td>
			</tr>
			<?
		} // while ($r901...

		?></table>
		
		<h3>Izvještaji</h3>
		
		
		<ul>
			<li><a href="?sta=izvjestaj/zavrsni_teme&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Prijedlog tema za završne radove po mentoru</a></li>
			<li>Spisak tema sa kandidatima i komisijama
				<ul>
					<li><a href="?sta=izvjestaj/zavrsni_nnv&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>&amp;svi=da">Svi uneseni kandidati</a></li>
					<li><a href="?sta=izvjestaj/zavrsni_nnv&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Samo oni koji su odbranili završni rad</a></li>
					<li><a href="?sta=izvjestaj/zavrsni_po_clanu_komisije&amp;predmet=<?=$predmet?>&amp;ag=<?=$ag?>">Po članstvu u komisijama</a></li>
				</ul>
			</li>
		</ul>
		
		<?
	} // if (!isset($akcija) - lista završnih radova


	// Akcija dodavanje ili izmjena završnog rada

	elseif ($akcija == 'izmjena_zavrsni')  {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$predmet = intval($_REQUEST['predmet']);
			$ag = intval($_REQUEST['ag']);

			$naslov = db_escape(trim($_REQUEST['naslov']));
			$podnaslov = db_escape(trim($_REQUEST['podnaslov']));
			$kratki_pregled  = db_escape_string(trim($_REQUEST['kratki_pregled']));
			$mentor = intval($_REQUEST['mentor']);
			$drugi_mentor = intval($_REQUEST['drugi_mentor']);
			if ($drugi_mentor==0) $drugi_mentor="NULL";
			$predsjednik_komisije = intval($_REQUEST['predsjednik_komisije']);
			if ($predsjednik_komisije==0) $predsjednik_komisije="NULL";
			$clan_komisije = intval($_REQUEST['clan_komisije']);
			if ($clan_komisije==0) $clan_komisije="NULL";
			$clan_komisije2 = intval($_REQUEST['clan_komisije2']);
			if ($clan_komisije2==0) $clan_komisije2="NULL";
			$student = intval($_REQUEST['student']);
			if ($student==0) { $kandidat_potvrdjen=0; $student="NULL"; } else $kandidat_potvrdjen=1;
			$rad_na_predmetu = intval($_REQUEST['rad_na_predmetu']);
			if ($rad_na_predmetu==0) $rad_na_predmetu="NULL";
			$literatura = db_escape_string(trim($_REQUEST['literatura']));
			$broj_diplome = db_escape($_REQUEST['broj_diplome']);
			$sala = db_escape($_REQUEST['sala']);
			
			// Kontrola termina odbrane
			if ($_REQUEST['termin_odbrane'] != "") {
				if (preg_match("/(\d+).*?(\d+).*?(\d+).*?\s+(\d+).*?(\d+)/", $_REQUEST['termin_odbrane'], $matches)) {
					$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
					$sat=$matches[4]; $minuta=$matches[5];
					if (!checkdate($mjesec,$dan,$godina)) {
						niceerror("Datum za termin odbrane je kalendarski nemoguć ($dan. $mjesec. $godina)");
						nicemessage('<a href="javascript:history.back();">Povratak.</a>');
						return;
					}
					if ($sat < 0 || $sat>23 || $minuta < 0 || $minuta > 59) {
						niceerror("Vrijeme za termin odbrane je neispravno ($sat sati, $minuta minuta)");
						nicemessage('<a href="javascript:history.back();">Povratak.</a>');
						return;
					}
					$termin_odbrane = mktime($sat, $minuta, 0, $mjesec, $dan, $godina);
				} else {
					niceerror("Termin odbrane nije u ispravnom formatu.");
					print "Potrebno je koristiti format: DD. MM. GGGG. HH:MM<br>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
			} else { // Polje forme prazno
				$termin_odbrane = 0;
			}

			// Kontrola datuma odluke
			if ($_REQUEST['datum_odluke_komisija'] != "") {
				if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_odluke_komisija'], $matches)) {
					$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
					if (!checkdate($mjesec,$dan,$godina)) {
						niceerror("Datum za odluku o imenovanju komisije je kalendarski nemoguć ($dan. $mjesec. $godina)");
						nicemessage('<a href="javascript:history.back();">Povratak.</a>');
						return;
					}
					$datum_odluke_komisija = mktime(0, 0, 0, $mjesec, $dan, $godina);
				} else {
					niceerror("Datum za odluku o imenovanju komisije nije u ispravnom formatu.");
					print "Potrebno je koristiti format: DD. MM. GGGG.<br>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				if ($_REQUEST['broj_odluke_komisija'] == "") {
					niceerror("Unijeli ste datum odluke o imenovanju komisije a niste unijeli broj odluke!");
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				$broj_odluke_komisija = db_escape($_REQUEST['broj_odluke_komisija']);
				$q009 = db_query("SELECT id FROM odluka WHERE datum=FROM_UNIXTIME($datum_odluke_komisija) AND broj_protokola='$broj_odluke_komisija'");
				if (db_num_rows($q009) > 0) {
					$odluka_komisija = db_result($q009, 0, 0);
				} else {
					$q001 = db_query("INSERT INTO odluka SET datum=FROM_UNIXTIME($datum_odluke_komisija), broj_protokola='$broj_odluke_komisija', student=$student");
					$odluka_komisija = db_insert_id();
				}
			} else $odluka_komisija = 0;

			// Kontrola datuma odluke
			if ($_REQUEST['datum_odluke_tema'] != "") {
				if (preg_match("/(\d+).*?(\d+).*?(\d+)/", $_REQUEST['datum_odluke_tema'], $matches)) {
					$dan=$matches[1]; $mjesec=$matches[2]; $godina=$matches[3];
					if (!checkdate($mjesec,$dan,$godina)) {
						niceerror("Datum za odluku o imenovanju komisije je kalendarski nemoguć ($dan. $mjesec. $godina)");
						nicemessage('<a href="javascript:history.back();">Povratak.</a>');
						return;
					}
					$datum_odluke_tema = mktime(0, 0, 0, $mjesec, $dan, $godina);
				} else {
					niceerror("Datum za odluku o imenovanju komisije nije u ispravnom formatu.");
					print "Potrebno je koristiti format: DD. MM. GGGG.<br>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				if ($_REQUEST['broj_odluke_tema'] == "") {
					niceerror("Unijeli ste datum odluke o imenovanju komisije a niste unijeli broj odluke!");
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
				$broj_odluke_tema = db_escape($_REQUEST['broj_odluke_tema']);
				$q009 = db_query("SELECT id FROM odluka WHERE datum=FROM_UNIXTIME($datum_odluke_tema) AND broj_protokola='$broj_odluke_tema'");
				if (db_num_rows($q009) > 0) {
					$odluka_tema = db_result($q009, 0, 0);
				} else {
					$q001 = db_query("INSERT INTO odluka SET datum=FROM_UNIXTIME($datum_odluke_tema), broj_protokola='$broj_odluke_tema', student=$student");
					$odluka_tema = db_insert_id();
				}
			} else $odluka_tema = 0;
	
			if (empty($naslov)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Pronalazimo labgrupu za mentora
			if ($student > 0) {
				$q908 = db_query("select prezime, ime FROM osoba WHERE id=$mentor");
				$ime_mentora = db_result($q908,0,0)." ".db_result($q908,0,1);

				$q907 = db_query("SELECT id FROM labgrupa WHERE naziv='$ime_mentora' AND predmet=$predmet AND akademska_godina=$ag");
				if (db_num_rows($q907)<1) {
					$q909 = db_query("INSERT INTO labgrupa SET naziv='$ime_mentora', predmet=$predmet, akademska_godina=$ag, virtualna=0");
					$q920 = db_query("SELECT id FROM labgrupa WHERE naziv='$ime_mentora' AND predmet=$predmet AND akademska_godina=$ag");
					$id_labgrupe = db_result($q920,0,0);

					$q921 = db_query("SELECT COUNT(*) FROM nastavnik_predmet WHERE nastavnik=$mentor AND predmet=$predmet AND akademska_godina=$ag");
					if (db_result($q921,0,0)==0) {
						$q922 = db_query("INSERT INTO nastavnik_predmet SET nastavnik=$mentor, predmet=$predmet, akademska_godina=$ag, nivo_pristupa='nastavnik'");
					}
					$q922 = db_query("INSERT INTO ogranicenje SET nastavnik=$mentor, labgrupa=$id_labgrupe");
				} else
					$id_labgrupe = db_result($q907,0,0);

				// Stavljamo studenta u grupu određenog profesora
				if ($id_labgrupe>0) {
					$q911 = db_query("SELECT l.id FROM student_labgrupa AS sl, labgrupa AS l WHERE sl.student=$student AND sl.labgrupa=l.id AND l.predmet=$predmet AND l.akademska_godina=$ag AND l.virtualna=0");
					while ($r911 = db_fetch_row($q911)) {
						$q912 = db_query("DELETE FROM student_labgrupa WHERE student=$student AND labgrupa=".$r911[0]);
					}
				}

				$q910 = db_query("INSERT INTO student_labgrupa SET student=$student, labgrupa=$id_labgrupe");
			}


	
			if ($id > 0) { // Izmjena teme
				$q905 = db_query("UPDATE zavrsni SET naslov='$naslov', podnaslov='$podnaslov', kratki_pregled='$kratki_pregled', literatura='$literatura', mentor=$mentor, drugi_mentor=$drugi_mentor, predsjednik_komisije=$predsjednik_komisije, clan_komisije=$clan_komisije, clan_komisije2=$clan_komisije2, student=$student, kandidat_potvrdjen=$kandidat_potvrdjen, termin_odbrane=FROM_UNIXTIME($termin_odbrane), rad_na_predmetu=$rad_na_predmetu, broj_diplome='$broj_diplome', sala='$sala', odluka_komisija=$odluka_komisija, odluka_tema=$odluka_tema WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
				nicemessage('Tema završnog rada je uspješno izmijenjena.');
				zamgerlog("izmijenjena tema zavrsnog rada $id na predmetu pp$predmet", 2);
				zamgerlog2("izmijenio temu zavrsnog rada", $id);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');

			} else { // Dodavanje teme
				// Generišemo jedinstven ID
				$znesta = db_query("select id from zavrsni order by id desc limit 1");
				if (db_num_rows($znesta)<1)
					$id = 1;
				else
					$id = db_result($znesta,0,0)+1;

				$q906 = db_query("INSERT INTO zavrsni (id, naslov, podnaslov, predmet, akademska_godina, kratki_pregled, literatura, mentor, drugi_mentor, student, kandidat_potvrdjen, predsjednik_komisije, clan_komisije, clan_komisije2, termin_odbrane, rad_na_predmetu, broj_diplome, sala, odluka_komisija, odluka_tema) VALUES ($id, '$naslov', '$podnaslov', $predmet, $ag,  '$kratki_pregled', '$literatura', $mentor, $drugi_mentor, $student, $kandidat_potvrdjen, $predsjednik_komisije, $clan_komisije, $clan_komisije2, FROM_UNIXTIME($termin_odbrane), $rad_na_predmetu, '$broj_diplome', '$sala', $odluka_komisija, $odluka_tema)");

				nicemessage('Nova tema završnog rada je uspješno dodana.');
				zamgerlog("dodana nova tema zavrsnog rada $id na predmetu pp$predmet", 2);
				zamgerlog2("dodana tema zavrsnog rada", $id);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			}
			return;
		}
		

		$q99 = db_query("select id, titula from naucni_stepen");
		while ($r99 = db_fetch_row($q99))
			$naucni_stepen[$r99[0]]=$r99[1];

		// Ako je definisan ID, onda je u pitanju izmjena
		if ($id>0) {
			$tekst = "Izmjena teme završnog rada";
			$q98 = db_query("SELECT student, mentor, drugi_mentor, predsjednik_komisije, clan_komisije, clan_komisije2, naslov, podnaslov, kratki_pregled, literatura, UNIX_TIMESTAMP(termin_odbrane), rad_na_predmetu, broj_diplome, sala, odluka_komisija, odluka_tema FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
			if (db_num_rows($q98)<1) {
				niceerror("Nepostojeći završni rad");
				zamgerlog("spoofing zavrsnog rada $id kod izmjene teme", 3);
				zamgerlog2("id zavrsnog rada i predmeta se ne poklapaju", $id, $predmet, $ag);
				return;
			}
			$id_studenta = db_result($q98, 0, 0);
			$id_mentora = db_result($q98, 0, 1);
			$id_mentora2 = db_result($q98, 0, 2);
			$id_predkom = db_result($q98, 0, 3);
			$id_clankom = db_result($q98, 0, 4);
			$id_clankom2 = db_result($q98, 0, 5);
			$naslov = db_result($q98, 0, 6);
			$podnaslov = db_result($q98, 0, 7);
			$kratki_pregled = db_result($q98, 0, 8);
			$literatura = db_result($q98, 0, 9);
			$termin_odbrane = date("d. m. Y. H:i", db_result($q98, 0, 10));
			if (db_result($q98, 0, 10) == 0) $termin_odbrane = "";
			$rad_na_predmetu = db_result($q98, 0, 11);
			$broj_diplome = db_result($q98, 0, 12);
			$sala = db_result($q98, 0, 13);
			$odluka_komisija = db_result($q98, 0, 14);
			if ($odluka_komisija > 0) {
				$q99 = db_query("SELECT UNIX_TIMESTAMP(datum), broj_protokola FROM odluka WHERE id=$odluka_komisija");
				$datum_odluke_komisija = date("d.m.Y.", db_result($q99,0,0));
				$broj_odluke_komisija = db_result($q99,0,1);
			} else {
				$broj_odluke_komisija = $datum_odluke_komisija = "";
			}
			$odluka_tema = db_result($q98, 0, 15);
			if ($odluka_tema > 0) {
				$q99 = db_query("SELECT UNIX_TIMESTAMP(datum), broj_protokola FROM odluka WHERE id=$odluka_tema");
				$datum_odluke_tema = date("d.m.Y.", db_result($q99,0,0));
				$broj_odluke_tema = db_result($q99,0,1);
			} else {
				$broj_odluke_tema = $datum_odluke_tema = "";
			}

		} else {
			$tekst = "Nova tema završnog rada";
			$id_studenta = $id_mentora = $id_mentora2 = $id_predkom = $id_clankom = $id_clankom2 = $rad_na_predmetu = 0;
			$naslov = $kratki_pregled = $literatura = $broj_diplome = $sala = $broj_odluke = $datum_odluke = "";
			
			$broj_odluke = "04-1-"; // FIXME !!!
			
			$q99 = db_query("SELECT count(*) FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
			if (db_result($q99,0,0) < 1) {
				niceerror("Nije moguće kreirati novi završni rad pošto se ne može izabrati mentor.");
				print "<p>Potrebno je pridružiti barem jednog nastavnika kao odgovornog nastavnika na predmetu ".db_result($q110,0,0)." (".db_result($q110,0,1).").</p>";
				print "<p><a href=\"$linkPrefix\">Nazad na spisak tema</a></p>";
				return;
			}
		}

		?>	
		<style>
		span.label { display: inline-block; width: 200px; height: 20px; }
		</style>
		<h2><?=$tekst?></h2>
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
							$q952 = db_query("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = db_fetch_row($q952)) {
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
					<span class="label">Drugi mentor</span>
					<span class="formw">
						<select name="drugi_mentor">
							<option value="0">(nije definisan)</option><?
							$cnt5 = 0;
							$q952 = db_query("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = db_fetch_row($q952)) {
								$cnt5 = $cnt5 + 1;
								if ($r952[0] == $id_mentora2) $opcija = " SELECTED";
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
					<span class="label">Predsjednik komisije</span>
					<span class="formw">
						<select name="predsjednik_komisije">
							<option value="0">(nije definisan)</option><?
							$cnt5 = 0;
							$q952 = db_query("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND a.angazman_status=1 AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = db_fetch_row($q952)) {
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
					<span class="label">Član komisije</span>
					<span class="formw">
						<select name="clan_komisije">
							<option value="0">(nije definisan)</option><?
							$cnt5 = 0;
							$q952 = db_query("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND (a.angazman_status=1 OR a.angazman_status=2) AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = db_fetch_row($q952)) {
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
					<span class="label">Član komisije</span>
					<span class="formw">
						<select name="clan_komisije2">
							<option value="0">(nije definisan)</option><?
							$cnt5 = 0;
							$q952 = db_query("SELECT o.id, o.ime, o.prezime, o.naucni_stepen FROM angazman AS a, osoba AS o WHERE a.predmet=$predmet AND a.akademska_godina=$ag AND (a.angazman_status=1 OR a.angazman_status=2) AND a.osoba=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r952 = db_fetch_row($q952)) {
								$cnt5 = $cnt5 + 1;
								if ($r952[0] == $id_clankom2) $opcija = " SELECTED";
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
					<span class="label">Tema odobrena odlukom broj </span>
					<span class="formw">
						<input name="broj_odluke_tema" type="text" id="broj_odluke_tema" size="20" value="<?=$broj_odluke_tema?>">
						datum Vijeća
						<input name="datum_odluke_tema" type="text" id="datum_odluke_tema" size="20" value="<?=$datum_odluke_tema?>">
					</span> 
				</div>
				<div class="row">
					<span class="label">Komisija imenovana odlukom broj </span>
					<span class="formw">
						<input name="broj_odluke_komisija" type="text" id="broj_odluke_komisija" size="20" value="<?=$broj_odluke_komisija?>">
						od datuma
						<input name="datum_odluke_komisija" type="text" id="datum_odluke_komisija" size="20" value="<?=$datum_odluke_komisija?>">
					</span> 
				</div>
				<div class="row">
					<span class="label">Rad je u okviru predmeta</span>
					<span class="formw"><select name="rad_na_predmetu">
						<option value="0">(nije definisan)</option><?
						$q953 = db_query("select p.id, p.naziv from predmet as p where p.id!=$predmet order by naziv");
						while ($r953 = db_fetch_row($q953)) {
							if ($r953[0] == $rad_na_predmetu) $opcija = " SELECTED";
							else $opcija = "";
							?>
							<option value="<?=$r953[0]?>" <?=$opcija?>> <?=$r953[1]?></option>
							<?
						}
					?></select></span> 
				</div>
				<div class="row">
					<span class="label">Naslov teme *</span>
					<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$naslov?>"></span> 
				</div>
				<div class="row">
					<span class="label">Podnaslov</span>
					<span class="formw"><input name="podnaslov" type="text" id="podnaslov" size="70" value="<?=$podnaslov?>"></span> 
				</div>
				<div class="row">
					<span class="label">Broj diplome</span>
					<span class="formw"><input name="broj_diplome" type="text" id="broj_diplome" size="20" value="<?=$broj_diplome?>"></span> 
				</div>
				<div class="row">
					<span class="label">Termin odbrane<br> (format: dd. mm. gggg. hh:mm)</span>
					<span class="formw"><input name="termin_odbrane" type="text" id="termin_odbrane" size="20" value="<?=$termin_odbrane?>"></span> 
				</div>
				<div class="row">
					<span class="label">Sala</span>
					<span class="formw"><input name="sala" type="text" id="sala" size="10" value="<?=$sala?>"></span> 
				</div>
				<div class="row">
					<span class="label">Student</span>
					<span class="formw">
						<select name="student">
							<option value="0" SELECTED>(niko nije preuzeo temu)</option><?
							$q954 = db_query("SELECT o.id, o.ime, o.prezime, o.brindexa FROM student_predmet AS sp, ponudakursa AS pk, osoba AS o WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.id=sp.predmet AND sp.student=o.id ORDER BY o.prezime, o.ime");
							$rowcounter5 = 0;
							while ($r954 = db_fetch_row($q954)) {
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
					<span class="formw"><textarea name="kratki_pregled" cols="60" rows="10" id="kratki_pregled"><?=htmlentities($kratki_pregled)?></textarea></span> 
				</div>
				<div class="row">
					<span class="label">Preporučena literatura</span>
					<span class="formw"><textarea name="literatura" cols="60" rows="15" id="literatura"><?=htmlentities($literatura)?></textarea></span> 
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
		$q999 = db_query("SELECT naslov FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
		if (db_num_rows($q999)==0) {
			niceerror("Nepoznat rad");
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token())  {
			// Brisanje teme zavrsnog rada

			// Brisanje fajlova
			$lokacijafajlova = "$conf_files_path/zavrsni/fajlovi/";
			// ??? fali još nešto
			
			// Brisanje same teme zavrsnog
			$q919 = db_query("DELETE FROM zavrsni WHERE id=$id");

			nicemessage('Uspješno ste obrisali temu završnog rada.');	
			zamgerlog("izbrisana tema zavrsnog rada $id na predmetu pp$predmet", 4);
			zamgerlog2("izbrisana tema zavrsnog rada", $id);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		<h3>"<?=db_result($q999,0,0)?>"</h3>
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
		$q1000 = db_query("SELECT student FROM zavrsni WHERE id=$id AND predmet=$predmet AND akademska_godina=$ag");
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

	// Akcija STRANICE ZAVRSNIH RADOVA

	elseif ($akcija == 'zavrsni_stranica') {
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica

	elseif ($akcija == 'izvjestaji') {
		$q2000 = db_query("SELECT naslov, student, mentor, clan_komisije, odluka_tema, odluka_komisija FROM zavrsni WHERE id=$id");
		db_fetch6($q2000, $naslov, $student, $mentor, $clan_komisije, $odluka_tema, $odluka_komisija);
		if ($student) {
			$kandidat = db_get("SELECT CONCAT(ime, ' ', prezime) FROM osoba WHERE id=$student");
		} else {
			$kandidat = "(nije definisan - izvještaji neće raditi)";
		}
		
		$ocjena = db_get("SELECT ocjena FROM konacna_ocjena WHERE student=$student AND predmet=$predmet AND akademska_godina=$ag");
		
		$ciklus = db_get("SELECT ts.ciklus FROM tipstudija ts, studij s, student_studij ss WHERE ss.student=$student AND ss.akademska_godina=$ag AND ss.semestar MOD 2 = 1 AND ss.studij=s.id AND s.tipstudija=ts.id");
		
		$url = "id=$id&amp;zavrsni=$id&amp;predmet=$predmet&amp;ag=$ag";
		
		?>
		<h1><?=$naslov?></h1>
		<p>Kandidat: <b><?=$kandidat?></b></p>
		<h2>Izvještaji</h2>
		<ul>
			<li><a href="?sta=studentska/zavrsni&amp;akcija=izvjestaj_jasper&amp;tip=1&amp;<?=$url?>" target="_blank">Obrazac ZR1: Prijava teme završnog rada</a></li>
			<? if ($mentor && $clan_komisije) { ?>
			<li><a href="?sta=studentska/zavrsni&amp;akcija=izvjestaj_jasper&amp;tip=2&amp;<?=$url?>" target="_blank">Obrazac ZR2: Prijedlog Komisije za ocjenu i odbranu završnog rada</a></li>
			<? } else { 
			?>
			<li>Obrazac ZR2: Prijedlog Komisije za ocjenu i odbranu završnog rada - <font color="red"><? 
			if (!$mentor) print "nije definisan mentor za završni rad, ";
			if (!$clan_komisije) print "nije definisana komisija za završni rad, ";
			?></font></li>
			<? }?>
			<? if ($odluka_tema) { ?>
			<li><a href="?sta=studentska/zavrsni&amp;akcija=izvjestaj_jasper&amp;tip=3&amp;<?=$url?>" target="_blank">Obrazac ZR3: Zahtjev za ocjenu i odbranu završnog rada</a></li>
			<? } else { 
			?>
			<li>Obrazac ZR3: Zahtjev za ocjenu i odbranu završnog rada - <font color="red">nije unesen broj i datum odluke o odobrenju teme</font></li>
			<? }?>
			<? if ($ciklus == 1 || $ciklus == 99 || !$conf_jasper) { ?>
			<li><a href="?sta=izvjestaj/zavrsni_zapisnik&amp;<?=$url?>">Zapisnik sa odbrane završnog rada</a></li>
			<? } ?>
			<? if ($mentor) { ?>
			<li><a href="?sta=studentska/zavrsni&amp;akcija=izvjestaj_jasper&amp;tip=4&amp;<?=$url?>" target="_blank">Obrazac ZR4: Saglasnost mentora</a></li>
			<? } else { 
			?>
			<li>Obrazac ZR4: Saglasnost mentora - <font color="red"><? 
			if (!$mentor) print "nije definisan mentor za završni rad, ";
			?></font></li>
			<? }?>
			<? if ($mentor && $clan_komisije && $odluka_komisija) { ?>
			<li><a href="?sta=studentska/zavrsni&amp;akcija=izvjestaj_jasper&amp;tip=5&amp;<?=$url?>" target="_blank">Obrazac ZR5: Izvještaj Komisije za ocjenu i odbranu završnog rada</a></li>
			<? } else { 
			?>
			<li>Obrazac ZR5: Izvještaj Komisije za ocjenu i odbranu završnog rada - <font color="red"><? 
			if (!$mentor) print "nije definisan mentor za završni rad, ";
			if (!$clan_komisije) print "nije definisana komisija za završni rad, ";
			if (!$odluka_komisija) print "nije unesen broj i datum odluke o imenovanju komisije";
			?></font></li>
			<? }?>
			<? if ($ciklus == 2 && $mentor && $clan_komisije && $odluka_komisija) { ?>
			<li><a href="?sta=studentska/zavrsni&amp;akcija=izvjestaj_jasper&amp;tip=6&amp;<?=$url?>" target="_blank">Obrazac ZR6: Zapisnik sa odbrane završnog rada</a></li>
			<? } else if ($ciklus == 2) { 
			?>
			<li>Obrazac ZR6: Zapisnik sa odbrane završnog rada - <font color="red"><? 
			//if (!$ocjena) print "nije unesena ocjena, ";
			if (!$mentor) print "nije definisan mentor za završni rad, ";
			if (!$clan_komisije) print "nije definisana komisija za završni rad, ";
			if (!$odluka_komisija) print "nije unesen broj i datum odluke o imenovanju komisije, ";
			?></font></li>
			<? } ?>
		</ul>
		<?
	} //akcija == zavrsni_stranica
	
	elseif ($akcija == 'izvjestaj_jasper') {
		if (!$conf_jasper) {
			niceerror("Jasper server nije aktivan");
			print "Odabrani izvještaj je dostupan samo putem JasperReports servera. Kontaktirajte vašeg administratora.";
			return;
		}
		$token = rand(100000, 999999);
		
		$dbname = "Obrazac ZR" . param('tip');
		$reportUnit = "%2Freports%2FObrazac_ZR" . param('tip');
		$uriParams = "&id_zavrsnog=$id&token=$token";
		$param2 = "''";
		
		db_query("DELETE FROM jasper_token WHERE NOW()-vrijeme>1500");
		db_query("INSERT INTO jasper_token SET token=$token, report='$dbname', vrijeme=NOW(), param1=$id, param2=$param2");
		
		?>
		<script>window.location = '<?=$conf_jasper_url?>/flow.html?_flowId=viewReportFlow&_flowId=viewReportFlow&ParentFolderUri=%2Freports&reportUnit=<?=$reportUnit?>&standAlone=true<?=$uriParams?>&decorate=no&output=pdf';</script>
		<?
		
	}
} // function

?>
