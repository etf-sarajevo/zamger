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
	$q10 = myquery("SELECT z.naslov, o.ime, o.prezime, o.naucni_stepen, z.student, z.sazetak, z.summary FROM zavrsni AS z, osoba AS o WHERE z.id=$zavrsni AND z.mentor=o.id");
	if (mysql_num_rows($q10)<1) {
		niceerror("Nepostojeći rad");
		zamgerlog("zavrsniStrane: nepostojeci rad $zavrsni", 3);
		zamgerlog2("nepostojeci rad", $zavrsni);
		return;
	}

	// Cache naučnog stepena
	$q20 = myquery("select id, titula from naucni_stepen");
	while ($r20 = mysql_fetch_row($q20))
		$naucni_stepen[$r20[0]]=$r20[1];

	$naslov_rada = mysql_result($q10,0,0);
	$mentor = mysql_result($q10,0,2)." ".$naucni_stepen[mysql_result($q10,0,3)]." ".mysql_result($q10,0,1);
	$id_studenta = mysql_result($q10,0,4);
	if (substr($sta,0,7) != "student" || substr($sta,0,10) == "studentska") {
		$q30 = myquery("select ime,prezime,brindexa from osoba where id=$id_studenta");
		$student = "Student: ".mysql_result($q30,0,1)." ".mysql_result($q30,0,0)." (".mysql_result($q30,0,2).")";
	}
	
	// Naslov stranice

	?>
	<h2><?=$naslov_rada?></h2>
	<p>Mentor: <?=$mentor?><br>
	<?=$student?></p>
	<?
	

	// Prikaz ako nije odabrana subakcija

	if (!isset($subakcija)) {

		
	// Da li je definisan sazetak?
	$sazetak = mysql_result($q10,0,5);
	$summary = mysql_result($q10,0,6);
	if ($userid == $id_studenta) {
		if (!preg_match("/\w/", $sazetak) || !preg_match("/\w/", $summary)) {
			?>
			<p><b><font color="red">Nije definisan sažetak teme</font></b></p>
			<p>Molimo vas da prije slanja finalne verzije rada definišete sažetak.</p>
			<?
		}

		?>
		<p><a href="<?=$linkPrefix?>&subakcija=sazetak">Kliknite ovdje da definišete sažetak</a></p>
		<?
	}
	

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
				zamgerlog2("csrf token nije dobar");
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
				zamgerlog2("greska prilikom slanja fajla na zavrsni", $zavrsni);
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
				zamgerlog2("dodao fajl na zavrsni", $zavrsni);
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
			zamgerlog2 ("id fajla nepostojeci ili ne odgovara zavrsnom", $id, $zavrsni);
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
			zamgerlog2("nije uspjelo brisanje fajla za zavrsni", $id);
			return;
		}

		$q610 = myquery("DELETE FROM zavrsni_file WHERE id=$id");
		nicemessage("Brisanje fajla uspjelo");
		zamgerlog("obrisan fajl $id za zavrsni rad $zavrsni", 2);
		zamgerlog2("obrisan fajl za zavrsni rad", $id, $zavrsni);
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
			zamgerlog2("nepostojeca forum tema ili ne odgovara zavrsnom", $id_teme, $zavrsni);
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
				zamgerlog2("csrf token nije dobar");
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
			zamgerlog2("dodao novu temu na zavrsni rad", $zavrsni);
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
			zamgerlog2("nepostojeca forum tema ili ne odgovara zavrsnom (odgovor)", $id_teme, $zavrsni);
			return;
		}

		// Submit akcija
		if (isset($_REQUEST['submit'])) {
			if (!check_csrf_token())  {
				zamgerlog("csrf token nije dobar",3);
				zamgerlog2("csrf token nije dobar");
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
			zamgerlog2("dodao novi odgovor na diskusiju za zavrsni rad", $id_teme, $zavrsni);
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
			zamgerlog2("nepostojeca forum poruka ili ne odgovara temi ili zavrsnom", $id_posta, $id_teme, $zavrsni);
			return;
		}

		// Submit akcija
		if (isset($_REQUEST['submit'])) {
			if (!check_csrf_token())  {
				zamgerlog("csrf token nije dobar",3);
				zamgerlog2("csrf token nije dobar");
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
			zamgerlog2("izmijenio vlastiti post za zavrsni rad", $id_posta, $zavrsni);
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
			zamgerlog2("nepostojeca forum poruka ili ne odgovara temi ili zavrsnom (brisanje)", $id_posta, $id_teme, $zavrsni);
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
				zamgerlog2("obrisao temu na forumu zavrsnog rada", $id_teme, $zavrsni);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			} else {
				nicemessage('Uspješno ste obrisali poruku.');	
				zamgerlog("obrisao poruku na forumu zavrsnog rada $zavrsni (pp$predmet)", 2);
				zamgerlog2("obrisao poruku na forumu zavrsnog rada", $id_posta, $zavrsni);
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


	// Subakcija za sažetak / summary
	if ($subakcija == 'sazetak') {
		if ($_REQUEST['potvrda'] && check_csrf_token()) {
			$sazetak = my_escape($_REQUEST['sazetak']);
			$summary = my_escape($_REQUEST['summary']);
			$q1000 = myquery("UPDATE zavrsni SET sazetak='$sazetak', summary='$summary' WHERE id=$zavrsni");
			nicemessage("Sažetak ažuriran");
			zamgerlog("azuriran sazetak zavrsnog rada $zavrsni", 2);
			zamgerlog2("azuriran sazetak zavrsnog rada", $zavrsni);
			?><a href="<?=$linkPrefix?>">Nazad</a><?
			return;
		}
	
		$sazetak = mysql_result($q10,0,5);
		$summary = mysql_result($q10,0,6);

		?>
		<?=genform("POST")?>
		<input type="hidden" name="potvrda" value="da">
		<p>Sažetak (lokalni jezik):<br>
		<textarea rows="15" cols="60" name="sazetak"><?=$sazetak?></textarea><br>
		&nbsp;<br>
		Sažetak (engleski jezik) - Summary:<br>
		<textarea rows="15" cols="60" name="summary"><?=$summary?></textarea><br>
		&nbsp;<br>
		<input type="submit" value=" Pošalji izmjene ">
		<input type="button" value=" Nazad " onclick="javascript:history.go(-1);">
		</form>
		<?
	}
}

?>
