<?
// COMMON/ZAVRSNISTRANE - stranice zavrsnih radova

function common_zavrsniStrane() {
	//debug mod aktivan
	global $userid, $user_nastavnik, $user_student, $conf_files_path, $user_siteadmin;
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$zavrsni = intval($_REQUEST['zavrsni']);

	$subakcija = $_REQUEST['subakcija'];
	$id = intval($_REQUEST['id']);
	
	$sta = $_REQUEST['sta'];
	
	$linkPrefix = "?sta=$sta&akcija=zavrsni_stranica&zavrsni=$zavrsni&predmet=$predmet&ag=$ag";
	$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/";

	// Osnovne informacije o radu
	$q10 = myquery("SELECT z.naziv, o.ime, o.prezime, o.naucni_stepen, z.student FROM zavrsni AS z, osoba AS o WHERE z.id=$zavrsni AND z.mentor=o.id");
	if (mysql_num_rows($q10)<1) {
		niceerror("Nepostojeći rad");
		zamgerlog("zavrsniStrane: nepostojeci rad $zavrsni", 3);
		return;
	}

	// Cache naučnog stepena
	$q20 = myquery("select id, titula from naucni_stepen");
	while ($r20 = mysql_fetch_row($q20))
		$naucni_stepen[$r20[0]]=$r20[1];

	$naziv_rada = mysql_result($q10,0,0);
	$mentor = mysql_result($q10,0,2)." ".$naucni_stepen[mysql_result($q10,0,3)]." ".mysql_result($q10,0,1);
	if (substr($sta,0,7) == "student") {
		$q30 = myquery("select ime,prezime,brindexa from osoba where id=".mysql_result($q10,0,4));
		$student = "Student: ".mysql_result($q30,0,1)." ".mysql_result($q30,0,0)." (".mysql_result($q30,0,2).")";
	}
	
	// Naslov stranice

	?>
	<h2><?=$naziv_rada?></h2>
	<p>Mentor: <?=$mentor?><br>
	<?=$student?></p>
	<?
	
	

	// Prikaz ako nije odabrana subakcija

	if (!isset($subakcija)) {

	// Spisak fajlova

	?>
	<center><table border="0"><tr><td>
	<p><b>Poslani fajlovi</b></p>
	<table border="1" cellspacing="0" cellpadding="4">
		<tr bgcolor="#CCCCCC">
			<td><b>Naziv</b></th>
			<td><b>Datum kreiranja</b></th>
			<td><b>Revizija</b></th>
			<td><b>Veličina</b></th>
			<td><b>Opcije</b></th>
		</tr>
	<?

	$q100 = myquery("SELECT id, osoba, filename, UNIX_TIMESTAMP(vrijeme), revizija FROM zavrsni_file WHERE zavrsni=$zavrsni ORDER BY vrijeme");
	if (mysql_num_rows($q100) < 1) {
		?>
		<tr><td colspan="5">Nije poslan niti jedan fajl</td></tr>
		<?
	}
	
	while ($r100 = mysql_fetch_row($q100)) {
		$id_fajla = $r100[0];
		$id_osobe = $r100[1];
		$filename = $r100[2];
		$datum = date("d. m. Y.", $r100[3]);
		$revizija = $r100[4];
		
		$filepath = $lokacijafajlova . $filename . "/v" . $revizija . "/" . $filename;
		$filesize = nicesize(filesize($filepath));
		
		?>
		<tr>
			<td><a href="?sta=common/attachment&tip=zavrsni&zavrsni=<?=$zavrsni?>&id=<?=$id_fajla?>"><?=$filename?></a></td>
			<td><?=$datum?></td>
			<td><?=$revizija?></td>
			<td><?=$filesize?></td>
			<td>
				<a href="?sta=common/attachment&tip=zavrsni&zavrsni=<?=$zavrsni?>&id=<?=$id_fajla?>">Preuzmi</a>        
				<a href="<?=$linkPrefix?>&subakcija=izmijeni_fajl&id=<?=$id_fajla?>">Izmijeni</a>
				<a href="<?=$linkPrefix?>&subakcija=obrisi_fajl&id=<?=$id_fajla?>">Obriši</a>
			</td>
		</tr>
		<?
	}
	
	?>
	</table>
	
	<p><a href="<?=$linkPrefix ?>&subakcija=dodaj_fajl">Novi fajl</a></p>
	<?
	
	
	// Forum
	?>
	<hr>
	
	<p><b>Komentari i diskusija</b></p>

	<p><a href="<?=$linkPrefix?>&subakcija=nova_tema">Dodajte novu temu za diskusiju</a></p>
	<?

	// Stylesheet za forum se nažalost nalazi u projekti.css - izbaciti...
	?>
	<LINK href="css/projekti.css" rel="stylesheet" type="text/css">
	<?

	$tema_po_stranici = 20;
	$stranica = 1;
	if (isset($_REQUEST['stranica'])) {
		$stranica = $_REQUEST['stranica'];
	}
	// izračun offseta
	$offset = ($stranica - 1) * $tema_po_stranici;
	
	$q200 = myquery("SELECT t.id, t.pregleda, UNIX_TIMESTAMP(p.vrijeme), o.ime, o.prezime FROM zavrsni_bb_tema AS t, zavrsni_bb_post AS p, osoba AS o WHERE t.zavrsni=$zavrsni AND t.zadnji_post=p.id AND p.osoba=o.id ORDER BY p.vrijeme DESC LIMIT $offset, $tema_po_stranici");
	$broj_tema = mysql_num_rows($q200);

	?>
	<div id="threadList">
		<div class="threadRow caption clearfix">
			<div class="threadInfo">
				<div class="views">Pregleda</div><!--views-->
				<div class="lastReply">Zadnji odgovor</div><!--lastReply-->
				<div class="replies">Odgovora</div><!--replies-->
			</div><!--threadInfo-->
		<div class="title">Teme (<?=$broj_tema ?>)</div><!--title-->		
		</div><!--threadRow caption-->
	<?
	$parni = true;

	while ($r200 = mysql_fetch_row($q200)) {
		$parni = !$parni;

		$id_teme = $r200[0];
		$broj_pregleda = $r200[1];
		$zadnji_odgovor = date('d.m.Y H:i:s', $r200[2]) . "<br />" . $r200[4] . ' ' . $r200[3];

		$q210 = myquery("SELECT COUNT(*) FROM zavrsni_bb_post WHERE tema = $id_teme");
		$broj_odgovora = mysql_result($q210,0,0);

		$q220 = myquery("SELECT p.naslov, o.ime, o.prezime FROM zavrsni_bb_post AS p, osoba AS o WHERE p.tema=$id_teme AND p.osoba=o.id ORDER BY p.id LIMIT 1");
		$naslov = mysql_result($q220,0,0);
		$autor =  mysql_result($q220,0,2)." ". mysql_result($q220,0,1);

		?>
		<div class="threadRow clearfix<? if  ($parni) echo ' pattern'?>">
		<div class="threadInfo">
			<div class="views"><?=$broj_pregleda ?></div><!--views-->
			<div class="lastReply"><?=$zadnji_odgovor?></div><!--lastReply-->
		<div class="replies"><?=$broj_odgovora ?></div><!--replies-->
		</div><!--threadInfo-->
		<div class="title"><a href="<?=$linkPrefix . "&subakcija=vidi_temu&tema=$id_teme" ?>" title="<?=$naslov ?>"><?=$naslov ?></a></div><!--title-->
		<div class="author"><?=$autor ?></div><!--author-->		
		</div><!--threadRow caption-->
		<?
	} //foreach thread


	?>
	</div><!--threadList-->
	<?
	
	$brstranica = ceil($broj_tema/$tema_po_stranici);
	
	if ($brstranica > 0) {
		echo "<span class=\"newsPages\">";
		if ($stranica > 1) {
			$str = $stranica - 1;
			$prev = " <a href=\"$linkPrefix&stranica=$str\">[Prethodna]</a> ";
			
			$prva = " <a href=\"$linkPrefix&stranica=1\">[Prva]</a> ";
		} 
		
		if ($stranica < $brstranica) {
			$str = $stranica + 1;
			$next = " <a href=\"$linkPrefix&stranica=$str\">[Sljedeća]</a> ";
			
			$zadnja = " <a href=\"$linkPrefix&stranica=$str\">[Zadnja]</a> ";
		} 
		
		echo $prva . $prev . " Strana <strong>$stranica</strong> od ukupno <strong>$brstranica</strong> " . $next . $zadnja;
		echo "</span>"; //newsPages span
	}

	// Kraj foruma


	?>
	</td></tr></table></center>
	<?



	} // if (!isset($subakcija))
	
	
	// SUBAKCIJE

	// Akcija dodavanje fajla

	if ($subakcija == 'dodaj_fajl') {
		if (isset($_REQUEST['submit'])) {
	
			if (!check_csrf_token()) {
				zamgerlog("csrf token nije dobar",3);
				niceerror("Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji");
				return;
			}

			// ulazni parametri
			$filename	= $_FILES['filename'];
			$revizija = 1;
			$file = '';
			$errorText = "";

			$zavrsni = intval($_REQUEST['zavrsni']);
			$predmet = intval($_REQUEST['predmet']);

			if ($filename['error'] == 4)
				$errorText = 'Unesite sva obavezna polja.';

			else if ($filename['error'] == 1 || $filename['error'] == 2)
					$errorText .= 'Pokušavate poslati fajl koji je veci od dozvoljene velicine. Probajte sa manjim fajlom.<br />';
			
			else if ($filename['error'] > 0)
				$errorText .= 'Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />';

			else {
				$uploadFile =  trim($filename['name']);
				$uploadDir = $lokacijafajlova . $uploadFile . "/v$revizija/";

				if (!file_exists($uploadDir))
					mkdir ($uploadDir, 0777, true);

				if (move_uploaded_file($filename['tmp_name'], $uploadDir . $uploadFile))
					chmod($uploadDir . $uploadFile, 0777);	
				else
					$errorText .= 'Desila se greška prilikom uploada fajla. Molimo kontaktirajte administratora.<br />AA';
			}
			
			if ($errorText != "") {
				niceerror($errorText);
				zamgerlog("greska prilikom slanja fajla na zavrsni $zavrsni", 3);
			} else {
				$q500 = myquery("SELECT id FROM zavrsni_file ORDER BY id DESC LIMIT 1");
				if (mysql_num_rows($q500)>0)
					$id = mysql_result($q500,0,0)+1;
				else
					$id = 1;
					
				$filename = my_escape($uploadFile);
				$q510 = myquery("INSERT INTO zavrsni_file SET id=$id, filename='$uploadFile', revizija=$revizija, osoba=$userid, zavrsni=$zavrsni, file=0");

				nicemessage("Fajl uspješno poslan");
				zamgerlog("dodao novi fajl na temu zavrsnog rada $zavrsni (pp$predmet)", 2);
			}
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
		} // if isset($_REQUEST['submit'])
	
		else {
			?>
			<h3>Novi fajl</h3>
			<?=genform("POST", "addForm\" enctype=\"multipart/form-data\" "); ?>

			<div id="formDiv">
				Polja sa * su obavezna. <br />
				<b>Limit za upload je 20MB.</b> <br />
				<div class="row">
					<span class="label">Fajl *</span>
					<span class="formw">
						<input name="filename" type="file" id="filename" size="60" />
						<input type="hidden" name="MAX_FILE_SIZE" value="20971520">
					</span>
				</div> 

				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
				</div>

			</div><!--formDiv-->
			</form>

			<?	
		} // prikaz forme za slanje
	} // subakcija "add"


		
	// Akcija izmjena fajla
	if ($subakcija == 'izmijeni_fajl') {
		niceerror("Trenutno nije implementirano.");
		return;
	}
	

	// Subakcija brisanje fajla
	if ($subakcija == 'obrisi_fajl') {
		// Pošto nismo implementirali podršku za editovanje (revizije) brišemo samo jednu reviziju

		$q600 = myquery("SELECT zavrsni, filename, revizija FROM zavrsni_file WHERE id=$id");
		if (mysql_num_rows($q600)<1 || $zavrsni != mysql_result($q600,0,0)) {
			niceerror("Ilegalan završni rad");
			zamgerlog ("spoofing fajla $id za zavrsni rad $zavrsni", 3);
			return;
		}

		$filename = mysql_result($q600,0,1);
		$revizija = mysql_result($q600,0,2);

		$lokacijarevizije = $lokacijafajlova . $filename . "/v$revizija";
		$lokacijafajla = $lokacijarevizije . "/" . $filename;
		if (!unlink($lokacijafajla) || !rmdir($lokacijarevizije)) {
			niceerror("Brisanje datoteke sa datotečnog sistema nije uspjelo.");
			print "Kontaktirajte administratora da vam obriše ovu datoteku.";
			zamgerlog("nije uspjelo brisanje fajla $id za zavrsni rad $zavrsni", 3);
			return;
		}

		$q610 = myquery("DELETE FROM zavrsni_file WHERE id=$id");
		nicemessage("Brisane fajla uspjelo");
		zamgerlog("obrisan fajl $id za zavrsni rad $zavrsni", 2);
		nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
		return;
	}
	
	
	// Subakcija čitanje teme na forumu
	if ($subakcija == 'vidi_temu') {
		$id_teme = intval($_REQUEST['tema']);
		$q700 = myquery("SELECT zavrsni FROM zavrsni_bb_tema WHERE id=$id_teme");
		if (mysql_num_rows($q700)<1 || $zavrsni != mysql_result($q700,0,0)) {
			niceerror("Nepostojeća tema.");
			zamgerlog("spoofing forum teme $id_teme za zavrsni rad $zavrsni", 3);
			return;
		}

		// Stylesheet za forum se nažalost nalazi u projekti.css - izbaciti...
		?>
		<LINK href="css/projekti.css" rel="stylesheet" type="text/css">
		<?

		$q710 = myquery("SELECT p.id, p.naslov, UNIX_TIMESTAMP(p.vrijeme), o.id, o.prezime, o.ime, pt.tekst FROM zavrsni_bb_post AS p, osoba AS o, zavrsni_bb_post_text AS pt WHERE p.tema=$id_teme AND p.osoba=o.id AND p.id=pt.post");

		?>
		<h3>Prikaz teme</h3>
		<p><a href="<?=$linkPrefix?>">Nazad na početnu stranicu</a></p>
		<div id="fullThread">
		<?
		while ($r710 = mysql_fetch_row($q710)) {
			$id_posta = $r710[0];
			$naslov = $r710[1];
			$vrijeme = date("d.m.Y. H:i:s", $r710[2]);
			$editabilno = ($userid == $r710[3]);
			$autor = $r710[4]." ".$r710[5];
			$tekst = $r710[6];

			?>
			<div class="post"><a name="p<?=$id_posta ?>">
			<div id="post_<?=$post[id]?>_header" class="header clearfix" onClick="toggleShowPost('post_<?=$id_posta ?>')">
			<div class="buttons">
				<a href="<?=$linkPrefix . "&subakcija=nova_poruka&tema=$id_teme&post=$id_posta"?>" title="Odgovori na ovaj post">Odgovori</a>
				<?
				if ($editabilno) {
					?>
					| <a href="<?=$linkPrefix . "&subakcija=izmijeni_poruku&tema=$id_teme&post=$id_posta"?>" title="Izmijeni vlastiti post">Izmijeni</a>
					| <a href="<?=$linkPrefix . "&subakcija=obrisi_poruku&tema=$id_teme&post=$id_posta"?>" title="Obriši vlastiti post">Obriši</a>		
					<?
				}
		
				?>
			</div>
			<div class="maininfo">
				<div class="date"><?=$vrijeme ?></div>
				<div class="author"><?=$autor ?></div> - 
				<div class="title"><?=$naslov ?></div>
			</div>
			</div><!--header-->
			<div class="text" id="post_<?=$id_posta ?>_text"><?=$tekst ?></div><!--text-->
		
			</div><!--post-->
			<?
		} //foreach post

		// Povecavamo view counter
		$q720 = myquery("UPDATE zavrsni_bb_tema SET pregleda=pregleda+1 WHERE id=$id_teme");

		?>
		</div> <!-- fullthread -->
		<?
	}

	
	// Subakcija nova tema na forumu
	if ($subakcija == 'nova_tema') {
		if (isset($_REQUEST['submit'])) {
			if (!check_csrf_token())  {
				zamgerlog("csrf token nije dobar",3);
				niceerror("Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			//get variables
			$naslov = my_escape(trim($_REQUEST['naslov']));
			$tekst = my_escape(trim($_REQUEST['tekst']));
	
			if (empty($naslov) || empty($tekst)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q800 = myquery("SELECT id FROM zavrsni_bb_tema ORDER BY id DESC LIMIT 1");
			if (mysql_num_rows($q800)>0)
				$id_teme = mysql_result($q800,0,0) + 1;
			else
				$id_teme = 1;

			$q810 = myquery("INSERT INTO zavrsni_bb_tema SET id=$id_teme, osoba=$userid, zavrsni=$zavrsni");


			$q820 = myquery("SELECT id FROM zavrsni_bb_post ORDER BY id DESC LIMIT 1");
			if (mysql_num_rows($q820)>0)
				$id_posta = mysql_result($q820,0,0) + 1;
			else
				$id_posta = 1;

			$q830 = myquery("INSERT INTO zavrsni_bb_post SET id=$id_posta, naslov='$naslov', osoba=$userid, tema=$id_teme");

			$q840 = myquery("INSERT INTO zavrsni_bb_post_text SET post=$id_posta, tekst='$tekst'");

			$q850 = myquery("UPDATE zavrsni_bb_tema SET prvi_post=$id_posta, zadnji_post=$id_posta WHERE id=$id_teme");

			nicemessage('Nova tema uspješno dodana.');
			zamgerlog("dodao novu temu na zavrsni rad $zavrsni (pp$predmet)", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>	
		<h3>Nova tema</h3>
		<?=genform("POST", "addForm"); ?>
		<div id="formDiv">
			Polja sa * su obavezna. <br />
		
			<div class="row">
				<span class="label">Naslov *</span>
				<span class="formw"><input name="naslov" type="text" id="naslov" size="70" /></span> 
			</div>
			<div class="row">
				<span class="label">Tekst *</span>
				<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		
		</div><!--formDiv-->
		
		</form>
		<?
	}


	// Subakcija odogovor na temu na forumu
	if ($subakcija == 'nova_poruka') {
		$id_teme = intval($_REQUEST['tema']);
		$id_posta = intval($_REQUEST['post']);

		$q900 = myquery("SELECT t.zavrsni, p.naslov FROM zavrsni_bb_post AS p, zavrsni_bb_tema AS t WHERE p.tema=$id_teme AND t.id=$id_teme ORDER BY p.id LIMIT 1");
		if (mysql_num_rows($q900)==0 || $zavrsni != mysql_result($q900,0,0)) {
			niceerror("Nepostojeća tema.");
			zamgerlog("spoofing forum teme $id_teme za zavrsni rad $zavrsni", 3);
			return;
		}

		// Submit akcija
		if (isset($_REQUEST['submit'])) {
			if (!check_csrf_token())  {
				zamgerlog("csrf token nije dobar",3);
				niceerror("Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			//get variables
			$naslov = my_escape(trim($_REQUEST['naslov']));
			$tekst = my_escape(trim($_REQUEST['tekst']));
		
			if (empty($naslov) || empty($tekst)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q820 = myquery("SELECT id FROM zavrsni_bb_post ORDER BY id DESC LIMIT 1");
			if (mysql_num_rows($q820)>0)
				$id_posta = mysql_result($q820,0,0) + 1;
			else
				$id_posta = 1;

			$q830 = myquery("INSERT INTO zavrsni_bb_post SET id=$id_posta, naslov='$naslov', osoba=$userid, tema=$id_teme");

			$q840 = myquery("INSERT INTO zavrsni_bb_post_text SET post=$id_posta, tekst='$tekst'");

			$q850 = myquery("UPDATE zavrsni_bb_tema SET zadnji_post=$id_posta WHERE id=$id_teme");

			nicemessage('Novi odgovor uspješno dodan.');
			zamgerlog("dodao novi odgovor na diskusiju ID $id_teme, tema zavrsnog rada $zavrsni (pp$predmet)", 2);
			nicemessage('<a href="'. $linkPrefix . "&subakcija=vidi_temu&tema=$id_teme" . '">Povratak.</a>');
			return;
		}


		// Naslov poruke je "Re: $naslov"
		$novi_naslov = "Re: ".mysql_result($q900,0,1);

		?>	
		<h3>Novi odgovor</h3>
		<?=genform("POST", "addForm"); ?>
		<div id="formDiv">
			Polja sa * su obavezna. <br />
		
			<div class="row">
				<span class="label">Naslov *</span>
				<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$novi_naslov?>" /></span> 
			</div>
			<div class="row">
				<span class="label">Tekst *</span>
				<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		
		</div><!--formDiv-->
		
		</form>
		<?
	}


	// Subakcija izmjena poruke na forumu
	if ($subakcija == 'izmijeni_poruku') {
		$id_teme = intval($_REQUEST['tema']); // Samo se koristi za backlink
		$id_posta = intval($_REQUEST['post']);

		$q400 = myquery("SELECT p.osoba, p.naslov, pt.tekst FROM zavrsni_bb_post AS p, zavrsni_bb_post_text AS pt WHERE p.id=$id_posta AND pt.post=$id_posta AND p.tema=$id_teme"); // ujedno provjeravamo i temu
		if (mysql_num_rows($q400)<1 || $userid != mysql_result($q400,0,0)) {
			niceerror("Niste autor ove poruke.");
			zamgerlog("spoofing forum poruke $id_posta,$id_teme prilikom editovanja za zavrsni rad $zavrsni", 3);
			return;
		}

		// Submit akcija
		if (isset($_REQUEST['submit'])) {
			if (!check_csrf_token())  {
				zamgerlog("csrf token nije dobar",3);
				niceerror("Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			//get variables
			$naslov = my_escape(trim($_REQUEST['naslov']));
			$tekst = my_escape(trim($_REQUEST['tekst']));
		
			if (empty($naslov) || empty($tekst)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			

			$q410 = myquery("UPDATE zavrsni_bb_post SET naslov='$naslov' WHERE id=$id_posta");
	
			$q420 = myquery("UPDATE zavrsni_bb_post_text SET tekst='$tekst' WHERE post=$id_posta");

			nicemessage('Uspješno ste izmijenili poruku.');
			zamgerlog("izmijenio vlastiti post $id_posta, tema zavrsnog rada $zavrsni (pp$predmet)", 2);
			nicemessage('<a href="'. $linkPrefix . "&subakcija=vidi_temu&tema=$id_teme" . '">Povratak.</a>');
			return;
		}

		$naslov = mysql_result($q400,0,1);
		$tekst = mysql_result($q400,0,2);


		?>	
		<h3>Izmijeni poruku </h3>
		<?=genform("POST", "addForm"); ?>
		<div id="formDiv">
			Polja sa * su obavezna. <br />
		
			<div class="row">
				<span class="label">Naslov *</span>
				<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$naslov?>" /></span> 
			</div>
			<div class="row">
				<span class="label">Tekst *</span>
				<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?=$tekst?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		
		</div><!--formDiv-->
		
		</form>
		<?
	}


	// Subakcija brisanje poruke na forumu
	if ($subakcija == 'obrisi_poruku') {
		$id_teme = intval($_REQUEST['tema']); // Samo se koristi za backlink
		$id_posta = intval($_REQUEST['post']);

		$q300 = myquery("SELECT osoba FROM zavrsni_bb_post WHERE id=$id_posta AND tema=$id_teme"); // ujedno provjeravamo i temu
		if (mysql_num_rows($q300)<1 || $userid != mysql_result($q300,0,0)) {
			niceerror("Niste autor ove poruke.");
			zamgerlog("spoofing forum poruke $id_posta,$id_teme prilikom brisanja za zavrsni rad $zavrsni", 3);
			return;
		}

		// Da li je ovo početna tema threada?
		$q310 = myquery("SELECT COUNT(*) FROM zavrsni_bb_post WHERE tema=$id_teme AND id<$id_posta");
		if (mysql_result($q310,0,0)<1)
			$pocetna = true;
		else
			$pocetna = false;

		// Submit akcija
		if (isset($_REQUEST['potvrda'])) {
			$q320 = myquery("DELETE FROM zavrsni_bb_post WHERE id=$id_posta");
			$q330 = myquery("DELETE FROM zavrsni_bb_post_text WHERE post=$id_posta");

			// Ako je početna poruka, brišemo kompletnu temu
			if ($pocetna) {
				$q340 = myquery("SELECT id FROM zavrsni_bb_post WHERE tema=$id_teme");
				while ($r340 = mysql_fetch_row($q340)) {
					$drugi_id = $r340[0];
					$q350 = myquery("DELETE FROM zavrsni_bb_post WHERE id=$drugi_id");
					$q360 = myquery("DELETE FROM zavrsni_bb_post_text WHERE post=$drugi_id");
				}
				$q370 = myquery("DELETE FROM zavrsni_bb_tema WHERE id=$id_teme");

				nicemessage('Uspješno ste obrisali kompletnu temu.');	
				zamgerlog("obrisao temu na forumu zavrsnog rada $zavrsni (pp$predmet)", 2);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			} else {
				nicemessage('Uspješno ste obrisali poruku.');	
				zamgerlog("obrisao poruku na forumu zavrsnog rada $zavrsni (pp$predmet)", 2);
				nicemessage('<a href="'. $linkPrefix . "&subakcija=vidi_temu&tema=$id_teme" . '">Povratak.</a>');
			}
			return;
		}



		// Ekran za potvrdu
		if ($pocetna) {
			?>
			<p><b>Da li ste sigurni da želite kompletnu temu i sve poruke na njoj?</b> <br />
			<?
		} else {
			?>
			<p><b>Da li ste sigurni da želite obrisati ovu poruku?</b> <br />
			<?
		}


		?>
		Napominjemo da ne postoji opcija za povratak obrisanog (undelete)!<br />
		<a href="<?= $linkPrefix ."&subakcija=obrisi_poruku&tema=$id_teme&post=$id_posta" ?> &potvrda=1">Da</a> | <a href="<?=$linkPrefix . "&subakcija=vidi_temu&tema=$id_teme" ?>">Odustani</a></p>
		<?
	}
/*

				elseif ($subaction == 'del') {
					//delete item
					if (isset($id) && is_int($id) && $id > 0) {
						if (isUserAuthorOfPost($id, $userid) == false) {
							zamgerlog("pokusava izbrisati post $id a nije autor, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
							return;
						}
						$threadID = intval($_REQUEST[tid]);
						if ($threadID<=0) {
							zamgerlog("pokusava izbrisati nepostojeci post $id, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
							return;
						}
						
						if (!isset($_REQUEST['c'])) {
							echo "Da li ste sigurni da zelite obrisati ovaj post? <br />";	
							echo '<a href="' . $linkPrefix .'&amp;subaction=del&tid=' . $threadID .'&id=' . $id . '&amp;c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else {
							if ($_REQUEST['c'] == 'true') {
								//delete the record
								if (deletePostZavrsni($id)) {
									nicemessage('Uspješno ste obrisali post.');	
									zamgerlog("obrisao post na temu zavrsnog rada $zavrsni (pp$predmet)", 2);
									if (getCountPostsInThread($threadID) > 0)
										$link = $linkPrefix . "&subaction=view&tid=$_REQUEST[tid]";	
									else
										$link = $linkPrefix;
								}
								else {
									niceerror('Došlo je do greske prilikom brisanja posta. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						 } //else isset c get parameter
								
					  } //id is okay		
				
				 } //subaction == del*/

/*	// Akcija IZMJENA FAJLA - verzija bazirana na Agićevom kodu koju je prepravljala Kozar
	if ($subaction == 'edit') {
		if (isset($_REQUEST['submit'])) {
			if (!check_csrf_token()) {
				zamgerlog("csrf token nije dobar",3);
				niceerror("Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji");
				return;
			}

			$id = intval($_REQUEST['id']);
	
			if ($id <= 0) {
				$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
				zamgerlog("pokusao urediti nepostojeci fajl $id, zavrsni rad $zavrsni (pp$predmet)", 3);
				return $errorText;
			}

			


	if ($option == 'edit' && isThisFileFirstRevision($id) == false) {
		//cannot get access to revisions other than the first one	
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokusao urediti staru reviziju fajla $id, zavrsni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}
	
	//process file
	if ($option == 'edit') {
		$entryZavrsni = getFileFirstRevisionZavrsni($id);
		$lastRevisionEntryZavrsni = getFileLastRevisionZavrsni($id);
	}
	
	//get variables
	$filename	= $_FILES['filename'];
	
	if ($option == 'edit') {
		$revizija = $lastRevisionEntryZavrsni[revizija] + 1;
		$file = $entry['id'];
	}
	else {
		$revizija = 1;
		$file = '';	
	}

	$zavrsni = intval($_REQUEST['zavrsni']);
	$predmet = intval($_REQUEST['predmet']);
	global $userid;

	if ($filename['error'] == 4) {
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
		
	global $conf_files_path;
	$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/$userid/";
	
	if (!file_exists("$conf_files_path/zavrsni/fajlovi/$zavrsni"))  {
		mkdir ("$conf_files_path/zavrsni/fajlovi/$zavrsni",0777, true);
	}
	if (!file_exists($lokacijafajlova))  {
		mkdir ($lokacijafajlova,0777, true);
	}
	//adding or replacing file - depends on the $option parameter(add, edit)

	if ($filename['error'] > 0)	{
		if ($filename['error'] == 1 || $filename['error'] == 2)
			$errorText .= 'Pokušavate poslati fajl koji je veci od dozvoljene velicine. Probajte sa manjim fajlom.<br />';
		else
			$errorText .= 'Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />';
		return $errorText;		
	}
	else {
		//No error occured so far
		//escape file name before upload
		if ($option == 'add')
			$Name = $filename['name'];
		else
			$Name = $entry[filename];

		$Name = trim($Name);					
		
		//make directory structure for this file
		//$uploadDir = $lokacijafajlova . "$Name" . "_revizije/v$revizija/";
		$uploadDir = $lokacijafajlova . "$Name/";
		
		if (!file_exists($uploadDir)) {
			mkdir ($uploadDir,0777, true);
		}
		$uploadDir = $uploadDir . "v$revizija/";
		
		if (!file_exists($uploadDir))  {
			mkdir ($uploadDir,0777, true);
		}

		//final file name
		if ($option == 'add')
			$uploadFile =  $Name;
		else
			$uploadFile = $entry['filename'];

		
		if (move_uploaded_file($filename['tmp_name'], $uploadDir . $uploadFile)) {
			//transfered a file to upload directory from temp dir
			//if edit option REPLACING the old image (overwrite)
			chmod($uploadDir . $uploadFile, 0777);	
		} 
		else {
			
			$errorText .= 'Desila se greška prilikom uploada fajla. Molimo kontaktirajte administratora.<br />AA';
			return $errorText;			
		} //else
		
	} //else
	
	//diff
	$diff = '';
	$diffing = 0;

	if ($option == 'edit') {
		//diffing with textual files only
		$lastRevisionFile = $lokacijafajlova . $lastRevisionEntry['filename'] . '/v' . $lastRevisionEntry['revizija'] . '/' . $lastRevisionEntry['filename'];
		$newFile          = $uploadDir . $uploadFile;
		
		$extension = preg_replace('/.+(\..*)$/', '$1', $lastRevisionEntry['filename']);
		$textExtensions = array(
								'.txt'
								);  

		if (in_array($extension, $textExtensions)) 
			$diffing = 1;
		
		if ($diffing == 1) {
			$diff = `/usr/bin/diff -u $lastRevisionFile $newFile`;
		}	
		 
	} //option == edit

	$data = array(
				'filename' => $uploadFile,
				'revizija' => $revizija, 
				'file' => $file, 
				'osoba' => $userid, 
				'zavrsni' => $zavrsni, 
				'diffing' => $diffing, 
				'diff' => $diff
	);
	
	if (!insertFileZavrsni($data)) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	return $errorText;*/


}

/*


				elseif ($subaction == 'edit') {
					if (!isUserAuthorOfFileForZavrsni($id, $userid))
						return;

					if (!isThisFileFirstRevision($id))
						return;
					
					//edit item
					if (!isset($_REQUEST['submit'])) {
						$entryZavrsni = getFileFirstRevisionZavrsni($id);
						$lastRevisionEntryZavrsni = getFileLastRevisionZavrsni($id);
		?>
					 <h3>Uredi fajl</h3>
				<?
					print genform("POST", "editForm\" enctype=\"multipart/form-data\" ");
				?>
					
					<div id="formDiv">
						Polja sa * su obavezna. <br />
						<b>Limit za upload je 20MB.</b> <br />							
					   <div class="row">
							<span class="label">Trenutni fajl</span>
							<span class="formw"><a href="<?='index.php?sta=common/attachment' . "&tip=zavrsni&zavrsni=$zavrsni&id=" . $lastRevisionEntryZavrsni[id]?>" >
								<?=filtered_output_string($lastRevisionEntryZavrsni[filename]) ?>
							</a>
							</span>
					   </div> 

						<div class="row">
						  <span class="label">Zamijeni fajl</span>
							<span class="formw">
								<input name="filename" type="file" id="filename" size="50" />
								<input type="hidden" name="MAX_FILE_SIZE" value="20971520">
							</span>
						</div>                         
						<div class="row">	
							<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
						</div>
					
					</div><!--formDiv-->
					</form>
	
		<?									
					}
					else {
						$errorText = formProcess_file('edit');
						if($errorText == '') {
							nicemessage('Uspješno ste uredili fajl.');
							zamgerlog("uredio fajl na temi završnog rada $zavrsni (pp$predmet)", 2);
							$link = $linkPrefix;
						}
						else {	
							//an error occured trying to process the form
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
						
					} //submitted the form
					
				} //subaction == edit
				elseif ($subaction == 'del') {
					if (!isUserAuthorOfFileForZavrsni($id, $userid))
						return;
						
					if (!isThisFileFirstRevisionZavrsni($id))
						return;

					//delete item
					if (isset($id) && is_int($id) && $id > 0) {
						if (!isset($_REQUEST['c'])) {
							echo "Da li ste sigurni da zelite obrisati ovaj fajl? Obrisacete sve revizije fajla sa servera.<br />";	
							echo '<a href="' . $linkPrefix . '&subaction=del&id=' . $id . '&c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else {
							if ($_REQUEST['c'] == 'true') {
								//delete the record
								if (deleteFileZavrsni($id)) {
									nicemessage('Uspješno ste obrisali fajl.');	
									zamgerlog("obrisao fajl na temi zavrsnog rada $zavrsni (pp$predmet)", 2);
									$link = $linkPrefix;
								}
								else {
									niceerror('Doslo je do greske prilikom brisanja fajla. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						} //else isset c get parameter
								
					} //id is okay		
				
				} //subaction == del

	
	return;
	
	if (!isset($section)) {
		//display  start page zavrsni
	?>
  	    <div id="mainWrapper" class="clearfix">
			<div id="leftBlocks">
                <div class="blockRow clearfix">
                     <div class="block" id="latestPosts">
                        <a class="blockTitle" href="<?=$linkPrefix . "&section=bb" ?>" title="Grupa za diskusiju">Najnoviji postovi</a>
                        <div class="items">
                        <?
                            $latestPostsZavrsni = fetchLatestPostsForZavrsni($zavrsni1[id], 4);
                            foreach ($latestPostsZavrsni as $post) {
                        ?>
                            <div class="item">
                                <span class="date"><?=date('d.m H:i  ', mysql2time($post[vrijeme])) ?></span>
                                <a href="<?=$linkPrefix . "&section=bb&subaction=view&tid=$post[tema]#p$post[id]" ?>" title="<?=$post['naslov']?>" target="_blank"><?php
                                
                                    $maxLen = 100;	
                                    $len = strlen($post[naslov]);
                                    
                                    echo filtered_output_string(substr($post['naslov'], 0, $maxLen-1));
                                    if ($len>$maxLen) 
                                        echo '...';
                                 ?></a>
                                <span class="author"> - <?=filtered_output_string($post[osoba][prezime] . ' ' . $post[osoba][ime]) ?></span>
                                <div class="desc"><?php
                                    $maxLen = 200;	
                                    $len = strlen($post[tekst]);
                                    
                                    echo filtered_output_string(substr($post['tekst'], 0, $maxLen-1));
                                    if ($len>$maxLen) 
                                        echo '...';
    
                             ?></div><!--desc-->
                            </div><!--item-->	
                        <?
                            }
                        ?>
                        </div><!--items-->
                    </div><!--block--> 
                </div><!--blockRow-->
            </div><!--leftBlocks-->
            <div id="rightBlocks" class="clearfix">	  
            	<div class="blockRow">
                    <div class="block" id="latestFiles">
                        <a class="blockTitle" href="<?=$linkPrefix . "&section=file" ?>" title="Fajlovi">Fajlovi</a>
                        <div class="items">
        				<?
            			//get latest entries
            			$filesZavrsni = fetchFilesForZavrsniLatestRevisions($zavrsni1[id], 0, 4);;
            
            			foreach ($filesZavrsni as $file) {
                            $authorZavrsni = getAuthorOfFileForZavrsni($file[id]);					
        					?>
                            <div class="item">
                                <span class="date"><?=date('d.m H:i  ', mysql2time($file[vrijeme])) ?></span>
                                <a href="<?="index.php?sta=common/attachment&tip=zavrsni&zavrsni=$zavrsni&id=$file[id]" ?>" title="<?=$file['filename']?>" ><?php
                                
                                    $maxLen = 100;	
                                    $len = strlen($file[filename]);
                                    
                                    echo filtered_output_string(substr($file['filename'], 0, $maxLen-1));
                                    if ($len>$maxLen) 
                                        echo '...';
                                 ?></a>
                                <span class="authorZavrsni"> - <?=filtered_output_string($authorZavrsni[prezime] . ' ' . $authorZavrsni[ime]) ?></span>
                            </div><!--item-->	
        					<?
                            } //foreach
							?>     
                        </div><!--items-->   
                    </div><!--block-->
                </div><!--blockRow-->                      
            </div><!--rightBlocks-->
        </div><!--mainWrapper-->    
    	<?
		} //section not set
	else {
		if ($section == 'file') {
			//files management
			$linkPrefix .='&section=file';
			?>
			<h2>Fajlovi</h2>
 			<div class="links clearfix" id="rss">
   				 <ul>
        			<li><a href="<?php echo $linkPrefix?>">Lista fajlova</a></li>
        			<li><a href="<?php echo $linkPrefix . "&subaction=add"?>">Novi fajl</a></li>
   				</ul>   
			</div>	
    		<?	
			if (!isset($subaction)) {
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_REQUEST['page'])) {
					$pageNum = $_REQUEST['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;			
				
				//display files for this zavrsni, with links to edit and delete
				$filesZavrsni = fetchFilesForZavrsniAllRevisions($zavrsni1[id], $offset, $rowsPerPage);
				?>
				<table class="files_table" border="0" cellspacing="0" cellpadding="0">
  					<tr>
    					<th scope="col" class="creation_date">Datum kreiranja</th>
    					<th scope="col" class="author">Autor</th>
    					<th scope="col" class="revision">Revizija</th>
                        <th scope="col" class="name">Naziv</th>
                        <th scope="col" class="filesize">Veličina</th>
                        <th scope="col" class="options">Opcije</th>
  					</tr>
				<?
				foreach ($filesZavrsni as $file) {
					$lastRevisionId = 0;
					$firstRevisionId = count($file) > 0 ? count($file) - 1 : 0;
					$authorZavrsni = getAuthorOfFileForZavrsni($file[$lastRevisionId][id]);
					?>				
                    <tr>
                        <td class="creation_date"><?=date('d.m.Y H:i:s', mysql2time($file[$lastRevisionId][vrijeme]))?></td><!--vrijeme-->
                        <td class="author"><?=filtered_output_string($authorZavrsni['ime'] . ' ' . $authorZavrsni['prezime']) ?></td><!--author-->
                        <td class="revision">v<?=$file[$lastRevisionId][revizija] ?></td><!--revizija-->
                        <td class="filename"><? 
							if (count($file) > 1) {
							?>
							<a href="#" onClick="toggleFileRevisions('file_<?=$file[$lastRevisionId][id] ?>_revisions')"><?=filtered_output_string($file[$lastRevisionId][filename]) ?></a>		
   							<?
    					}
						else {
							?>
    						<?=filtered_output_string($file[$lastRevisionId][filename]) ?>
   				 			<?						
						}
    					?>        </td><!--filename-->
                        <td class="filesize"><?php
                            $lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/" . $file[$lastRevisionId][osoba] . "/" . 
                            $file[$lastRevisionId][filename] . '/v' . $file[$lastRevisionId][revizija] . '/';
                            $filepath = $lokacijafajlova . $file[$lastRevisionId][filename];
                            $filesize = filesize($filepath);
                            echo nicesize($filesize);
                            ?>        </td><!--filesize-->
                        <td class="options">
							<a href="<?='index.php?sta=common/attachment' . "&tip=zavrsni&zavrsni=$zavrsni&id=" . $file[$lastRevisionId][id] ?>">Snimi</a>        
							<?
							if (isUserAuthorOfFileForZavrsni($file[$lastRevisionId][id], $userid)) {
							?>
          						<a href="<? echo $linkPrefix . "&subaction=edit&id=" . $file[$firstRevisionId]['id'] ?>">Uredi</a>
           						<a href="<? echo $linkPrefix . "&subaction=del&id=" . $file[$firstRevisionId]['id']?>">Briši</a>
								<?
							} //if user is author of this item
							?>        </td><!--options-->
   				 	</tr><!--file_leading-->
    				<?
					if (count($file) > 1) {
						for ($i = 1; $i < count($file); $i++) {	
							$revision = $file[$i];
							$authorZavrsni = getAuthorOfFileForZavrsni($revision[id]);
							?>
									<tr class="file_<?=$file[$lastRevisionId][id] ?>_revisions" style="display: none;" id="file_revisions">
										<td class="creation_date"><?=date('d.m.Y H:i:s', mysql2time($revision[vrijeme]))?></td><!--vrijeme-->
										<td class="author"><?=filtered_output_string($author['ime'] . ' ' . $author['prezime']) ?></td><!--author-->
										<td class="revision">v<?=$revision[revizija] ?></td><!--revizija-->
										<td class="filename"><?=filtered_output_string($revision[filename]) ?></td><!--filename-->
										<td class="filesize"><?php
											$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/" . $revision[osoba] . "/" . 
											$revision[filename] . '/v' . $revision[revizija] . '/';
											$filepath = $lokacijafajlova . $revision[filename];
											$filesize = filesize($filepath);
											echo nicesize($filesize);
											?>
										</td><!--filesize-->
										<td class="options">
											<a href="<?='index.php?sta=common/attachment' . "&tip=zavrsni&zavrsni=$zavrsni&id=" . $revision[id] ?>">Snimi</a>        
										</td><!--options-->
									</tr><!--file_revision-->	
							<?					
						} //for 

					} //if count files > 1

				} //foreach file
				?>
				</table>
			<!--files_table-->
			<?
				$numrowsZavrsni = getCountFilesForZavrsniWithoutRevisions($zavrsni1[id]);
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
			
				if ($maxPage > 0) {
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1) {
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage) {
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span
				}	
				
	?>
    <script type="text/javascript">
		function getElementsByClassName( strClassName, obj ) {
			var ar = arguments[2] || new Array();
			var re = new RegExp("\\b" + strClassName + "\\b", "g");
		
			if ( re.test(obj.className) ) {
				ar.push( obj );
			}
			for ( var i = 0; i < obj.childNodes.length; i++ )
				getElementsByClassName( strClassName, obj.childNodes[i], ar );
			
			return ar;
		}
		
		function toggleFileRevisions(divID) {
			 var aryClassElements = getElementsByClassName( divID, document.body );
			for ( var i = 0; i < aryClassElements.length; i++ ) {
				if (aryClassElements[i].style.display == '')
					aryClassElements[i].style.display = 'none';
				else
					aryClassElements[i].style.display = '';	
			}
		}
	
	</script>
    <?		
			} //subaction not set
			else {
	
			} //subaction set
			
		} //section == file
		elseif ($section == 'bb') {
			//links management
			$linkPrefix .='&section=bb';
    ?>
<h2>Grupa za diskusiju</h2>
 <div class="links clearfix" id="bl">
    <ul>
        <li><a href="<? echo $linkPrefix?>">Lista tema</a></li>
        <li><a href="<? echo $linkPrefix . "&subaction=add"?>">Nova tema</a></li>
    </ul>   
</div>	
    <?
			if (!isset($subaction))	{
				
				$rowsPerPage = 20;
				$pageNum = 1;
				if(isset($_REQUEST['page'])) {
					$pageNum = $_REQUEST['page'];
				}
				// counting the offset
				$offset = ($pageNum - 1) * $rowsPerPage;
				
				$threadsZavrsni = fetchThreadsForZavrsni($zavrsni1[id], $offset, $rowsPerPage);
				$numrowsZavrsni = getCountThreadsForZavrsni($zavrsni1[id]);

	?>
<div id="threadList">
	<div class="threadRow caption clearfix">
        <div class="threadInfo">
        	<div class="views">Pregleda</div><!--views-->
        	<div class="lastReply">Zadnji odgovor</div><!--lastReply-->
            <div class="replies">Odgovora</div><!--replies-->
        </div><!--threadInfo-->
    	<div class="title">Teme (<?=$numrows ?>)</div><!--title-->		
    </div><!--threadRow caption-->
    <?
				foreach($threadsZavrsni as $key => $thread) {
	?>
	<div class="threadRow clearfix<? if  ($key % 2) echo ' pattern'?>">
        <div class="threadInfo">
        	<div class="views"><?=intval($thread[pregleda]) ?></div><!--views-->
        	<div class="lastReply"><?=date('d.m.Y H:i:s', mysql2time($thread[zadnji_post][vrijeme])) ?><br /><?=filtered_output_string($thread[zadnji_post][osoba][prezime] . ' ' . $thread[zadnji_post][osoba][ime]) ?></div><!--lastReply-->
            <div class="replies"><?=intval($thread[broj_odgovora]) ?></div><!--replies-->
        </div><!--threadInfo-->
    	<div class="title"><a href="<?=$linkPrefix . "&subaction=view&tid=$thread[id]" ?>" title="<?php echo $thread['naslov'] ?>"><?=filtered_output_string($thread[naslov]) ?></a></div><!--title-->
        <div class="author"><?=filtered_output_string($thread[prvi_post][osoba][prezime] . ' ' . $thread[prvi_post][osoba][ime]) ?></div><!--author-->		
    </div><!--threadRow caption-->
    <?
				} //foreach thread
	?>
</div><!--threadList-->
    <?
							
				$maxPage = ceil($numrows/$rowsPerPage);
				$self = $linkPrefix;
				
				if ($maxPage > 0) {
					echo "<span class=\"newsPages\">";
					if ($pageNum > 1) {
						$page = $pageNum - 1;
						$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
						
						$first = " <a href=\"$self&page=1\">[Prva]</a> ";
					} 
					
					if ($pageNum < $maxPage) {
						$page = $pageNum + 1;
						$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
						
						$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
					} 
					
					echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
					echo "</span>"; //newsPages span	
				}
							
			} //subactin not set
			else {
				if ($subaction == 'view') {
					$tid = intval($_REQUEST[tid]);
					$thread = getThreadAndPosts($tid);
					if (empty($thread)) {
						zamgerlog("strane zavrsnih radova: nepostojeci thread sa IDom $id, tema $zavrsni (pp$predmet, ag$ag)", 3);
						return;	
					}	
					incrementThreadViewCount($thread[id]);		
					
	?>
    <div id="fullThread">
    <?
					foreach ($thread[posts] as $post) {
	?>				
		<div class="post"><a name="p<?=$post[id] ?>">
        	<div id="post_<?=$post[id]?>_header" class="header clearfix" onClick="toggleShowPost('post_<?=$post[id] ?>')">
                <div class="buttons">
                	<a href="<?=$linkPrefix . "&subaction=add&tid=$post[tema]&id=$post[id]"?>" title="Odgovori na ovaj post">Odgovori</a>
    <?
		if (isUserAuthorOfPost($post[id], $userid) == true) {
	?>
    				| <a href="<?=$linkPrefix . "&subaction=edit&tid=$post[tema]&id=$post[id]"?>" title="Uredi vlastiti post">Uredi</a>
    				| <a href="<?=$linkPrefix . "&subaction=del&tid=$post[tema]&id=$post[id]"?>" title="Obriši vlastiti post">Obriši</a>		
    <?
		}
	
	?>
                </div>
                <div class="maininfo">
                	<div class="date"><?=date('d.m.Y H:i:s', mysql2time($post[vrijeme])) ?></div>
                    <div class="author"><?=filtered_output_string($post[osoba][prezime] . ' ' . $post[osoba][ime]) ?></div> - 
                    <div class="title"><?=filtered_output_string($post[naslov]) ?></div>
                </div>
            </div><!--header-->
            <div class="text" id="post_<?=$post[id] ?>_text"><?=filtered_output_string($post[tekst]) ?></div><!--text-->

        </div><!--post-->				
					
	<?			
					} //foreach post
	?>
    
    
    </div><!--fullThread-->
        <script type="text/javascript">
		function toggleShowPost(divID) {
			header = document.getElementById(divID + '_header');
			text = document.getElementById(divID + '_text');
			if (text.style.display == 'block' || text.style.display == '') {
				text.style.display = 'none';
				header.style.backgroundColor = '#F5F5F5';
				header.style.color = 'black';
			}
			else {
				text.style.display = 'block';
				header.style.backgroundColor = '#EEEEEE';
			}	
				
		}
		</script>
	
    <?			
				} //subaction == view (thread)
				elseif ($subaction == 'add') {
		
					$threadID = intval($_REQUEST['tid']);
					
					if ($threadID <=0)
						$thread = false;
					else
						$thread = true;
					
					if ($thread == true) {
						$postInfo = getPostInfoForThread($threadID, $id);
						$extendedThreadInfo = array();
						getExtendedInfoForThread($threadID, $extendedThreadInfo);
						
						if (empty($postInfo)) {
							zamgerlog("strane zavrsnih radova: odgovor na nepostojeci post $id, tema $zavrsni (pp$predmet)", 3);
							return;
						}	
					}
					if (!isset($_REQUEST['submit'])) {
			?>	
    		
				 <h3><? if ($thread == true) echo 'Novi odgovor'; else echo 'Nova tema'; ?></h3>
				<?
					print genform("POST", "addForm");
				?>
                <? 
					if ($thread == true)
					{
				?> 
					<input type="hidden" name="tid" value="<?=$threadID?>"  />
				<?
					}
				?>
                <div id="formDiv">
                	Polja sa * su obavezna. <br />
                
                	<div class="row">
                        <span class="label">Naslov *</span>
                        <span class="formw"><input name="naslov" type="text" id="naslov" size="70" <?php if ($thread == true) {?> value="RE: <?=$extendedThreadInfo['naslov']?>"<? } ?>/></span> 
                  	</div>
                    <div class="row">
                        <span class="label">Tekst *</span>
                        <span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
                   	</div> 
                                      
                    <div class="row">	
                      	<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
                    </div>
                
                </div><!--formDiv-->
               
                </form>
               
	<?	
					} //not submitted yet
					else {
						$errorText = formProcess_bb('add', $thread, $threadID);
						if($errorText == '') {
							if ($thread == true) {
								nicemessage('Novi odgovor uspješno dodan.');
								zamgerlog("dodao novi odgovor na diskusiju ID $threadID, tema zavrsnog rada $zavrsni (pp$predmet)", 2);
							}
							else {
								nicemessage('Nova tema uspješno dodana.');
								zamgerlog("dodao novu temu na zavrsni rad $zavrsni (pp$predmet)", 2);
							}
								
							if (!empty($_REQUEST[tid]))				
								$link = $linkPrefix . "&subaction=view&tid=$_REQUEST[tid]";	
							else
								$link = $linkPrefix;
						}
						else {	
							niceerror($errorText);
							$link = "javascript:history.back();";		
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
					
					} //submitted the form
	
				} //subaction == addThread
				elseif ($subaction == 'edit') {
					//edit item
					if (isUserAuthorOfPost($id, $userid) == false) {
						zamgerlog("pokusava urediti post $id a nije autor, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
						return;
					}
					$threadID = intval($_REQUEST[tid]);
					if ($threadID <=0) {
						zamgerlog("pokusava urediti nepostojeci post $id, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
						return;
					}
									
					if (!isset($_REQUEST['submit'])) {
						$entry = getPost($id);
						if (empty($entry)) {
							zamgerlog("pokusava urediti nepostojeci post $id, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
							return;
						}
?>
				 <h3>Uredi post</h3>
				<?
					print genform("POST", "editForm");
				?>
				<div id="formDiv">
					Polja sa * su obavezna. <br />
				
					<div class="row">
						<span class="label">Naslov *</span>
						<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?php echo $entry['naslov']?>" /></span> 
					</div>
					<div class="row">
						<span class="label">Tekst *</span>
						<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?php echo $entry['tekst'] ?></textarea></span>
					</div> 
					
					<div class="row">	
						<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
					</div>
				
				</div><!--formDiv-->
				
				</form>
				<?					
					}
					else {
						$errorText = formProcess_bb('edit', $thread, $threadID);
						if($errorText == '') {
							nicemessage('Uspješno ste uredili post.');
							zamgerlog("uredio vlastiti BB post $id, tema zavrsnog rada $zavrsni (pp$predmet)", 2);
							$link = $linkPrefix . "&subaction=view&tid=$_REQUEST[tid]";
						}
						else {	
							//an error occured trying to process the form
							niceerror($errorText);
							$link = "javascript:history.back();";	
						}
						nicemessage('<a href="'. $link .'">Povratak.</a>');
						
					} //submitted the form
				
				} //subaction == edit
				elseif ($subaction == 'del') {
					//delete item
					if (isset($id) && is_int($id) && $id > 0) {
						if (isUserAuthorOfPost($id, $userid) == false) {
							zamgerlog("pokusava izbrisati post $id a nije autor, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
							return;
						}
						$threadID = intval($_REQUEST[tid]);
						if ($threadID<=0) {
							zamgerlog("pokusava izbrisati nepostojeci post $id, tema zavrsnog rada $zavrsni (pp$predmet)", 3);
							return;
						}
						
						if (!isset($_REQUEST['c'])) {
							echo "Da li ste sigurni da zelite obrisati ovaj post? <br />";	
							echo '<a href="' . $linkPrefix .'&amp;subaction=del&tid=' . $threadID .'&id=' . $id . '&amp;c=true">Da</a> | <a href="' . $linkPrefix . '">Odustani</a>';			
						}
						else {
							if ($_REQUEST['c'] == 'true') {
								//delete the record
								if (deletePostZavrsni($id)) {
									nicemessage('Uspješno ste obrisali post.');	
									zamgerlog("obrisao post na temu zavrsnog rada $zavrsni (pp$predmet)", 2);
									if (getCountPostsInThread($threadID) > 0)
										$link = $linkPrefix . "&subaction=view&tid=$_REQUEST[tid]";	
									else
										$link = $linkPrefix;
								}
								else {
									niceerror('Došlo je do greske prilikom brisanja posta. Molimo kontaktirajte administratora.');
									$link = "javascript:history.back();";	
								}
								nicemessage('<a href="'. $link .'">Povratak.</a>');
							}
							
						 } //else isset c get parameter
								
					  } //id is okay		
				
				 } //subaction == del
		
			} //subaction set
				
		} //section == bb (forum)		
	
	} //else - section is set

} //function


function formProcess_file($option) {
	$errorText = '';
	if (!check_csrf_token()) {
		zamgerlog("csrf token nije dobar",3);
		return "Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji";
	}
	
	set_time_limit(0);
	
	if (!in_array($option, array('add', 'edit') ) ) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_REQUEST['id']);
	
	if ($option == 'edit' && $id <=0) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokusao urediti nepostojeci fajl $id, zavrsni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}
	if ($option == 'edit' && isThisFileFirstRevision($id) == false) {
		//cannot get access to revisions other than the first one	
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokusao urediti staru reviziju fajla $id, zavrsni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}
	
	//process file
	if ($option == 'edit') {
		$entryZavrsni = getFileFirstRevisionZavrsni($id);
		$lastRevisionEntryZavrsni = getFileLastRevisionZavrsni($id);
	}
	
	//get variables
	$filename	= $_FILES['filename'];
	
	if ($option == 'edit') {
		$revizija = $lastRevisionEntryZavrsni[revizija] + 1;
		$file = $entry['id'];
	}
	else {
		$revizija = 1;
		$file = '';	
	}

	$zavrsni = intval($_REQUEST['zavrsni']);
	$predmet = intval($_REQUEST['predmet']);
	global $userid;

	if ($filename['error'] == 4) {
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
		
	global $conf_files_path;
	$lokacijafajlova ="$conf_files_path/zavrsni/fajlovi/$zavrsni/$userid/";
	
	if (!file_exists("$conf_files_path/zavrsni/fajlovi/$zavrsni"))  {
		mkdir ("$conf_files_path/zavrsni/fajlovi/$zavrsni",0777, true);
	}
	if (!file_exists($lokacijafajlova))  {
		mkdir ($lokacijafajlova,0777, true);
	}
	//adding or replacing file - depends on the $option parameter(add, edit)

	if ($filename['error'] > 0)	{
		if ($filename['error'] == 1 || $filename['error'] == 2)
			$errorText .= 'Pokušavate poslati fajl koji je veci od dozvoljene velicine. Probajte sa manjim fajlom.<br />';
		else
			$errorText .= 'Vaš fajl nije poslan korektno. Molimo pokušajte ponovo.<br />';
		return $errorText;		
	}
	else {
		//No error occured so far
		//escape file name before upload
		if ($option == 'add')
			$Name = $filename['name'];
		else
			$Name = $entry[filename];

		$Name = trim($Name);					
		
		//make directory structure for this file
		//$uploadDir = $lokacijafajlova . "$Name" . "_revizije/v$revizija/";
		$uploadDir = $lokacijafajlova . "$Name/";
		
		if (!file_exists($uploadDir)) {
			mkdir ($uploadDir,0777, true);
		}
		$uploadDir = $uploadDir . "v$revizija/";
		
		if (!file_exists($uploadDir))  {
			mkdir ($uploadDir,0777, true);
		}

		//final file name
		if ($option == 'add')
			$uploadFile =  $Name;
		else
			$uploadFile = $entry['filename'];

		
		if (move_uploaded_file($filename['tmp_name'], $uploadDir . $uploadFile)) {
			//transfered a file to upload directory from temp dir
			//if edit option REPLACING the old image (overwrite)
			chmod($uploadDir . $uploadFile, 0777);	
		} 
		else {
			
			$errorText .= 'Desila se greška prilikom uploada fajla. Molimo kontaktirajte administratora.<br />AA';
			return $errorText;			
		} //else
		
	} //else
	
	//diff
	$diff = '';
	$diffing = 0;

	if ($option == 'edit') {
		//diffing with textual files only
		$lastRevisionFile = $lokacijafajlova . $lastRevisionEntry['filename'] . '/v' . $lastRevisionEntry['revizija'] . '/' . $lastRevisionEntry['filename'];
		$newFile          = $uploadDir . $uploadFile;
		
		$extension = preg_replace('/.+(\..*)$/', '$1', $lastRevisionEntry['filename']);
		$textExtensions = array(
								'.txt'
								);  

		if (in_array($extension, $textExtensions)) 
			$diffing = 1;
		
		if ($diffing == 1) {
			$diff = `/usr/bin/diff -u $lastRevisionFile $newFile`;
		}	
		 
	} //option == edit

	$data = array(
				'filename' => $uploadFile,
				'revizija' => $revizija, 
				'file' => $file, 
				'osoba' => $userid, 
				'zavrsni' => $zavrsni, 
				'diffing' => $diffing, 
				'diff' => $diff
	);
	
	if (!insertFileZavrsni($data)) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	return $errorText;
}

function insertFileZavrsni($data) {
	//generate unique id value
	$id = generateIdFromTable('zavrsni_file');
	
	$query = sprintf("INSERT INTO zavrsni_file (id, filename, revizija, osoba, zavrsni, file) VALUES ('%d', '%s', '%d', '%d', '%d', '%d')", 
											$id, 
											my_escape($data['filename']), 
											intval($data['revizija']), 
											intval($data['osoba']), 
											intval($data['zavrsni']), 
											intval($data['file'])  						
					);
	$result = myquery($query);	
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//insert diff
	if ($data['diffing'] == 1) {
		$query = sprintf("INSERT INTO zavrsni_file_diff (file, diff) VALUES ('%d', '%s')", 
											$id, 
											my_escape($data['diff'])
		);
		$result = myquery($query);
		
		if ($result == false || mysql_affected_rows() == 0)
			return false;			
	}
	
	return true;	
}

function deleteFileZavrsni($id) {
	global $conf_files_path;
	
	$listZavrsni = fetchAllRevisionsForFileZavrsni($id);
	
	foreach ($listZavrsni as $item) {
		$query = sprintf("DELETE FROM zavrsni_file WHERE id='%d' LIMIT 1", 
					intval($item[id])
					);
	
		$result = myquery($query);
		if (mysql_affected_rows() == 0)
			return false;
			
		$lokacijarevizije = "$conf_files_path/zavrsni/fajlovi/" . $item['zavrsni'] . '/' . $item['osoba'] . '/' . $item['filename'] . '/v' . $item['revizija'];
		
		if (!unlink($lokacijarevizije . '/' . $item[filename]))
			return false;	
		if (!rmdir($lokacijarevizije))
			return false;
			
		//remove any diffs for this file
		myquery("DELETE FROM zavrsni_file_diff WHERE file='" . $item[id] . "' LIMIT 1");
	}
	
	$lokacijafajlova = "$conf_files_path/zavrsni/fajlovi/" . $list[0]['zavrsni'] . '/' . $list[0]['osoba'] . '/' . $list[0]['filename'];
	if (!rmdir($lokacijafajlova))
		return false;
	
	return true;
}


function formProcess_bb($option, $thread, $threadID) {
	$errorText = '';
	if (!check_csrf_token())  {
		zamgerlog("csrf token nije dobar",3);
		return "Poslani podaci nisu ispravni. Vratite se nazad, ponovo popunite formu i kliknite na dugme Pošalji";
	}
	if (!in_array($option, array('add', 'edit') ) ) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;		
	}
	
	$id = intval($_REQUEST['id']);
	
	if ($option == 'edit' && $id <=0) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokusao urediti nepostojeci post $id, zavrsni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}

	if ($thread == true && $threadID <=0) {
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		zamgerlog("pokusao urediti nepostojeci post $id, zavrsni rad $zavrsni (pp$predmet)", 3);
		return $errorText;
	}
	
	
	//get variables
	$naslov 	= $_REQUEST['naslov'];
	$tekst 		= $_REQUEST['tekst'];
	
	$zavrsni = intval($_REQUEST['zavrsni']);
	$predmet = intval($_REQUEST['predmet']);
	global $userid;

	
	if (empty($naslov) || empty($tekst)) {
		$errorText = 'Unesite sva obavezna polja.';
		return $errorText;
	}
	
	$naslov = trim($naslov);
	$tekst = trim($tekst);
	
	if ($option == 'edit') {
		$entry = getPost($id);
	}
	
	$data = array(
				'naslov' => $naslov, 
				'tekst' => $tekst, 
				'osoba' => $userid, 
				'zavrsni' => $zavrsni, 
				'threadID' => $threadID //only used in insertReply if thread == true		
	);
	
	if ($option == 'add') {
		if ($thread == false) {
			//new thread inserting
			if (!insertThread($data)) {
				$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
				return $errorText;		
			}
		} //thread false
		else {
			//inserting post in thread
			if (!insertReplyForThread($threadID, $data)) {
				$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
				return $errorText;		
			}
		
		}
	
	} //option == add
	else {
		if (!updatePost($data, $id)) {
			$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
			return $errorText;		
		}
	
	} //option == edit

	return $errorText;	
}

function insertThreadZavrsni($data) {
	//generate unique id value
	$thread_id = generateIdFromTable('bb_tema');
	
	$query = sprintf("INSERT INTO bb_tema (id, osoba, zavrsni) VALUES('%d', '%d', '%d')", 
											$thread_id,
											intval($data['osoba']), 
											intval($data['zavrsni'])											
	
	);
	$result = myquery($query);	
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	$post_id = insertReplyForThread($thread_id, $data);
	if ($post_id == false)
		return false;

	//update some data in newly created thread
	$query = sprintf("UPDATE bb_tema SET prvi_post='%d', zadnji_post='%d' WHERE id='%d' LIMIT 1", 
											$post_id, 
											$post_id, 
											$thread_id
	);
	
	$result = myquery($query);
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
		
	return true;
}

function insertReplyForThread($thread, $data) {
	
	//insert post for this thread, this is the first post
	$post_id = generateIdFromTable('bb_post');
	$query = sprintf("INSERT INTO bb_post (id, naslov, osoba, tema) VALUES('%d', '%s', '%d', '%d')", 
											$post_id, 
											my_escape($data['naslov']), 
											intval($data['osoba']), 
											$thread	
	);
	$result = myquery($query);
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//insert text for this post
	$query = sprintf("REPLACE INTO bb_post_text SET post='%d', tekst='%s'", 
											$post_id, 
											my_escape($data['tekst'])	
	);
	
	$result = myquery($query);
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	//update some data in thread
	$query = sprintf("UPDATE bb_tema SET zadnji_post='%d' WHERE id='%d' LIMIT 1", 
											$post_id, 
											$thread
	);
	
	$result = myquery($query);
	
	if ($result == false || mysql_affected_rows() == 0)
		return false;
	
	return $post_id;	
}	

function updatePost($data, $id) {
	$query = sprintf("UPDATE bb_post SET naslov='%s' WHERE id='%d' LIMIT 1", 
											my_escape($data['naslov']), 
											intval($id) 
											
					);
	$result = myquery($query);
	
	if ($result == false)
		return false;	
	
	$query = sprintf("UPDATE bb_post_text SET tekst='%s' WHERE post='%d' LIMIT 1", 
											my_escape($data['tekst']), 
											intval($id) 
											
					);
	$result = myquery($query);

	return ( $result == false ) ? false : true;
}

function deletePost($id) {	
	$query = sprintf("DELETE FROM bb_post WHERE id='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	if (mysql_affected_rows() == 0)
		return false;
		
	$query = sprintf("DELETE FROM bb_post_text WHERE post='%d' LIMIT 1", 
					intval($id)
					);
	
	$result = myquery($query);
	
	if (mysql_affected_rows() == 0)
		return false;
	
	//if first post, delete thread
	
	$result = myquery("SELECT prvi_post, id FROM bb_tema WHERE prvi_post='$id' LIMIT 1");
	
	if (mysql_num_rows($result) > 0) {
		//delete evetyhing
		$row = mysql_fetch_assoc($result);
		$thread = $row[id];
		
		$result = myquery("DELETE FROM bb_tema WHERE id='$thread' LIMIT 1");
		if ($result == false || mysql_affected_rows() == 0)
			return false;
			
		return true;
	}
	
	$result = myquery("SELECT zadnji_post, id FROM bb_tema WHERE zadnji_post='$id' LIMIT 1");
	if (mysql_num_rows($result) > 0) {
		//assign this value to the new last post
		$row = mysql_fetch_assoc($result);
		$thread = $row[id];
		
		$result = myquery("SELECT id FROM bb_post WHERE tema='$thread' ORDER BY vrijeme DESC LIMIT 1");
		$row = mysql_fetch_assoc($result);
		$post = $row[id];
		
		$result = myquery("UPDATE bb_tema SET zadnji_post='$post' WHERE id='$thread' LIMIT 1");
		if ($result == false || mysql_affected_rows() == 0)
			return false;
		
		return true;		
	}	

	return true;
}

function generateIdFromTable($table) {
	$result = myquery("select id from $table order by id desc limit 1");
	
	if (mysql_num_rows($result) == 0) {
		$id = 0;
	}
	else {	
		$id = mysql_fetch_row($result);
		$id = $id[0];
	}
	
	return intval($id+1);
}

function filtered_output_string($string) {
	//performing nl2br function to display text from the database
	return nl2br($string);
}

function rmdir_recursive($dirname) {
    // Sanity check
    if (!file_exists($dirname))  {
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read())  {
        // Skip pointers
        if ($entry == '.' || $entry == '..')  {
            continue;
        }

        // Recurse
        rmdir_recursive($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return rmdir($dirname);
}

function fetchFilesForZavrsniAllRevisions($id, $offset = 0, $rowsPerPage = 0) {
	$query = "SELECT * FROM zavrsni_file WHERE zavrsni='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	$files = array();

	foreach ($list as $item) {
		$files[] = fetchAllRevisionsForFileZavrsni($item[id]);	
	}
	return $files;	
}

function fetchFilesForZavrsniLatestRevisions($id, $offset = 0, $rowsPerPage = 0) {
	$query = "SELECT * FROM zavrsni_file WHERE zavrsni='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;
}

function getAuthorOfFileForZavrsni($id) {
	$result = myquery("SELECT o.* FROM osoba o WHERE o.id=(SELECT f.osoba FROM zavrsni_file f WHERE f.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isUserAuthorOfFileForZavrsni($file, $user) {
	$result = myquery("SELECT id FROM zavrsni_file WHERE osoba='$user' AND id='$file' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getFileFirstRevisionZavrsni($id) {
	$result = myquery("SELECT * FROM zavrsni_file WHERE id='$id' AND revizija=1 LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getFileZavrsni($id) {
	$result = myquery("SELECT * FROM zavrsni_file WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isThisFileFirstRevisionZavrsni($id) {
	$result = myquery("SELECT id FROM zavrsni_file WHERE id='$id' AND revizija=1 LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getFileLastRevisionZavrsni($id) {
	$result = myquery("SELECT * FROM zavrsni_file WHERE file='$id' ORDER BY revizija DESC LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	if (empty($list)) {
		//samo jedna revizija
		$list[0] = getFileFirstRevisionZavrsni($id);
	}
	
	return $list[0];
}

function fetchAllRevisionsForFileZavrsni($id) {
	$list = array();	
	$result = myquery("SELECT * FROM zavrsni_file WHERE file='$id' ORDER BY revizija DESC");
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;
	
	$list[] = getFileFirstRevisionZavrsni($id);
	return $list;	
}

function getCountFilesForZavrsniWithoutRevisions($id) {
	$result = myquery("SELECT COUNT(id) FROM zavrsni_file WHERE zavrsni='$id' AND revizija=1 LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}*/
?>
