<?php
// NASTAVNIK/ZAVRSNI - nastavnicki modul za definisanje zavrsnih radova, parametara

function nastavnik_zavrsni() {

	require_once ('lib/zavrsni.php');
	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Da li korisnik ima pravo ući u modul?
	if (!$user_siteadmin) 
	{
		$q900 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q900)<1 || mysql_result($q900,0,0)=="asistent") {
			zamgerlog("nastavnik/zavrsni privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	$linkPrefix = "?sta=nastavnik/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<h2>Zavrsni</h2>
	<?
	
	// Preuzimanje parametara zavrsnih radova
	$q900 = myquery("SELECT min_tema, max_tema, min_clanova, max_clanova, zakljucani_zavrsni FROM predmet_parametri_zavrsni WHERE predmet=$predmet AND akademska_godina=$ag");
	if (mysql_num_rows($q900)<1)
		$nema_parametara = true;
	else {
		$nema_parametara = false;
		$param_min_tema = mysql_result($q900,0,0);
		$param_max_tema = mysql_result($q900,0,1);
		$param_min_clanova = mysql_result($q900,0,2);
		$param_max_clanova = mysql_result($q900,0,3);
		$param_zakljucan = mysql_result($q900,0,4);
	}

	// Glavni meni
	if ($akcija != 'zavrsni_stranica') {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="<?=$linkPrefix."&akcija=param"?>">Parametri završnih radova</a></li>
				<li><a href="<?=$linkPrefix ?>">Lista tema završnih radova</a></li>
				<li><a href="<?=$linkPrefix."&akcija=dodaj_zavrsni" ?>">Nova tema završnog rada</a></li>
				<li class="last"><a href="<?=$linkPrefix."&akcija=dodjela_studenata"?>">Dodjela tema završnih radova studentima</a></li>
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
		$q901 = myquery("SELECT id, naziv, opis FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
		$broj_tema = mysql_num_rows($q901);
		if ($broj_tema > 0) {
			if ($param_zakljucan == 1) {
				?>
				<span class="notice">Onemogućene su prijave na teme završnih radova. Otvorene su stranice završnih radova.</span>	
				<?
			} else {
				?>
				<span class="noticeGreen">Studenti se još uvijek mogu prijaviti na neku od tema završnih radova. Niste zaključali teme u parametrima završnih radova.</span>	
				<?
			}

			$q902 = myquery("select distinct z.id from student_zavrsni as sz, zavrsni as z where sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
			$broj_nepraznih = mysql_num_rows($q902);

			if ($broj_nepraznih < $param_min_tema) {
				?>
				<span class="notice">Trenutni broj slobodnih tema (<?=$broj_nepraznih?>) je ispod minimalnog broj tema koji ste definisali za ovaj predmet (<?=$param_min_tema?>).</span>
				<?
			}

		} else {
			?>
			<span class="notice">Nema kreiranih tema završnih radova na ovom predmetu.</span>	
			<?
		}
	
		while ($r901 = mysql_fetch_row($q901)) {
			$id_zavrsni = $r901[0];
			$naziv_teme = $r901[1];
	
			?>
			<h3><?=$naziv_teme?></h3>
			<div class="links">
				<ul class="clearfix" style="margin-bottom: 10px;">
					<li><a href="<?=$linkPrefix."&akcija=izmjena_zavrsni&id=$id_zavrsni" ?>">Izmijeni temu završnog rada</a></li>
					<li><a href="<?=$linkPrefix."&akcija=dodaj_biljesku&id=$id_zavrsni" ?>">Dodaj bilješku</a></li>
					<li <? if ($param_zakljucan == 0) { print 'class="last"'; } ?>><a href="<?=$linkPrefix."&akcija=obrisi_zavrsni&id=$id_zavrsni" ?>">Obriši temu završnog rada</a></li>
					<?
					if ($param_zakljucan == 1) {
						?>
						<li class="last"><a href="<?= $linkPrefix . "&akcija=zavrsni_stranica&zavrsni=$id_zavrsni" ?>">Stranica završnih radova</a></li>
						<?
					}
					?>
				</ul> 
				<?

				$q903 = myquery("SELECT COUNT(id) FROM osoba as o, student_zavrsni as sz where o.id=sz.student and sz.zavrsni=$id_zavrsni");
				$broj_clanova = mysql_result($q903,0,0);
				if ($broj_clanova < $param_min_clanova) {
					?>
					<span class="notice">Broj prijavljenih studenata (<?=$broj_clanova?>) je ispod minimuma koji ste definisali za ovaj predmet (<?=$param_min_clanova?>).</span>	
					<?
				}

				?>
			</div>

			<table class="zavrsni" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<th width="200" align="left" valign="top" scope="row">Naziv</th>
					<td width="490" align="left" valign="top"><?=$naziv_teme?></td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Prijavljeni studenti</th>
					<td width="490" align="left" valign="top">
					<?

					// Spisak studenata
					$q904 = myquery("SELECT o.id, o.prezime, o.ime, o.brindexa FROM osoba as o, student_zavrsni as sz WHERE sz.student=o.id and sz.zavrsni=$id_zavrsni ORDER BY o.prezime, o.ime");
					if (mysql_num_rows($q904)<1)
						print 'Nema prijavljenih studenata.';
					else {
						print "<ul>\n";
						while ($r904 = mysql_fetch_row($q904)) {
							print "<li>$r904[1] $r904[2] ($r904[3])";
							if ($param_zakljucan==0) {
								print ' - (<a href="'.$linkPrefix."&akcija=izbaci_studenta&student=$r904[0]&zavrsni=$id_zavrsni".'">izbaci</a>)';
							}
							print "</li>\n";
						}
						print "</ul>\n";
					}
					?>
					</td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Opis</th>
					<td width="490" align="left" valign="top"><?=$r901[2]?></td>
				</tr>
			</table>
			<?
		} // while ($r901...
	} // if (!isset($akcija)



	// Akcija PARAMETRI ZAVRSNIH RADOVA

	if ($akcija == 'param') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za promjenu
			$min_tema = intval($_REQUEST['min_tema']);
			$max_tema  = intval($_REQUEST['max_tema']);
			
			$min_clanova = intval($_REQUEST['min_clanova']);
			$max_clanova = intval($_REQUEST['max_clanova']);
			
			$zakljucani_zavrsni = 0;
			if (isset($_REQUEST['lock'])) $zakljucani_zavrsni = 1;
			
			if ($min_tema <= 0 || $max_tema <= 0 || $min_clanova <= 0 || $max_clanova <= 0) {
				niceerror("Morate unijeti ispravne vrijednosti u sva polja");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q905 = myquery("REPLACE predmet_parametri_zavrsni SET predmet=$predmet, akademska_godina=$ag, min_tema=$min_tema, max_tema=$max_tema, min_clanova=$min_clanova, max_clanova=$max_clanova, zakljucani_zavrsni=$zakljucani_zavrsni");

			nicemessage('Uspješno ste uredili parametre završnih radova.');
			zamgerlog("izmijenio parametre završnih radova na predmetu pp$_REQUEST[predmet]", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		<h2>Parametri završnih radova</h2>

		<?=genform("POST", "editForm"); ?>
		<input type="hidden" name="subakcija" value="potvrda">
		<div id="formDiv">
			Polja sa * su obavezna. <br />
			<div class="row">
				<span class="label">Zaključaj stanje teme završnog rada</span>
				<span class="formw"><input name="lock" type="checkbox" id="lock" <? if ($param_zakljucan == 1) print 'checked';?> /></span> 
				<br /><br /> Ova opcija će onemogućiti prijavljivanje na temu završnog rada i pokrenuti stranice završnih radova.
			</div>
			<div class="row">
				<span class="label">MIN tema *</span>
				<span class="formw"><input name="min_tema" type="text" id="min_tema" size="10" value="<?=$param_min_tema;?>" /></span> 
			</div>
			<div class="row">
				<span class="label">MAX tema *</span>
				<span class="formw"><input name="max_tema" type="text" id="max_tema" size="10" value="<?=$param_max_tema?>" /></span> 
			</div>
			<div class="row">
				<span class="label">MIN članova *</span>
				<span class="formw"><input name="min_clanova" type="text" id="min_clanova" size="10" value="<?=$param_min_clanova?>" /></span> 
			</div>
			<div class="row">
				<span class="label">MAX članova *</span>
				<span class="formw"><input name="max_clanova" type="text" id="max_clanova" size="10" value="<?=$param_max_clanova?>" /></span> 
			</div>
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</div><!--formDiv-->
		</form>
		<?
	}



	// Akcija DODAJ TEMU ZAVRSNOG RADA

	elseif ($akcija == 'dodaj_zavrsni') {
		if ($nema_parametara && !isset($_REQUEST['submit'])) {
			nicemessage("Prvo podesite parametre završnih radova.");
			nicemessage('<a href="'. $linkPrefix .'&akcija=param">Parametri završnih radova</a>');
			return;
		}

		if ($param_zakljucan == 1) {
			niceerror("Zaključali ste stanje tema završnih radova na ovom predmetu. Nije moguće dodati novu temu.");
			nicemessage('<a href="'. $linkPrefix .'&akcija=param">Parametri završnih radova</a>');
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$opis  = my_escape(trim($_REQUEST['opis']));
	
			$id = intval($_REQUEST['id']);
	
			if (empty($naziv) || empty($opis)) {
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
	
			$q906 = myquery("INSERT INTO zavrsni (id, naziv, opis, predmet, akademska_godina) VALUES ($id, '$naziv', '$opis', '$predmet', '$ag')");

			nicemessage('Nova tema završnog rada je uspješno dodana.');
			zamgerlog("dodana nova tema završnog rada na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}
		
		?>	
		<h2>Nova tema završnog rada</h2>
		<?=genform("POST", "addForm");?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
				</div>
				<div class="row">
					<span class="label">Opis *</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"></textarea></span>
				</div> 
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			</div><!--formDiv-->
		</form>
		<?
	}



	// Akcija IZMJENA TEME ZAVRSNOG RADA

	elseif ($akcija == 'izmjena_zavrsni') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za izmjenu teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$opis  = my_escape(trim($_REQUEST['opis']));
	
			if (empty($naziv) || empty($opis)) {
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


			$q908 = myquery("UPDATE zavrsni SET naziv='$naziv', opis='$opis' WHERE id='$id'");

			nicemessage('Uspješno ste izmijenili temu završnog rada.');
			zamgerlog("izmijenio temu završnog rada $id na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		// Prikaz forme
		$q909 = myquery("SELECT naziv, opis FROM zavrsni WHERE id=$id");

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
					<span class="label">Opis *</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?=mysql_result($q900,0,1)?></textarea></span>
				</div> 
				
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
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
			$q910 = myquery("UPDATE zavrsni SET biljeska='$biljeska' WHERE id=$id");

			nicemessage('Uspješno ste dodali bilješku.');
			zamgerlog("dodao biljesku na temu završnog rada $id na predmetu pp$predmet", 2);		
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		$q911 = myquery("SELECT biljeska FROM zavrsni WHERE id=$id");

		?>
		<h3>Dodaj bilješku za temu završnog rada</h3>	
		<?=genform('POST','addNote'); ?>			
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" wrap="physical" id="opis"><?=mysql_result($q911,0,0)?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj_biljesku


	// Akcija OBRIŠI TEMU ZAVRSNOG RADA

	elseif ($akcija == 'obrisi_temu') {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Brisanje teme zavrsnog rada

			// Brisanje fajlova
			$lokacijafajlova = "$conf_files_path/zavrsni/fajlovi/";
			//if (!rmdir_recursive($lokacijafajlova . $id)) {
			//}

			// Brisanje članaka
			$lokacijaclanaka ="$conf_files_path/zavrsni/clanci/";
			//if (!rmdir_recursive($lokacijaclanaka . $id)) { 
			//}
			
			$q912 = myquery("DELETE FROM bl_clanak WHERE zavrsni=$id");

			// Brisanje linkova
			$q913 = myquery("DELETE FROM zavrsni_link WHERE zavrsni=$id");
	
			// Brisanje RSSa
			$q914 = myquery("DELETE FROM zavrsni_rss WHERE zavrsni=$id"); 

			// Brisanje foruma
			$q915 = myquery("DELETE FROM bb_post_text WHERE post IN (SELECT id FROM bb_post WHERE tema IN (SELECT id FROM bb_tema WHERE zavrsni=$id) )");
			$q916 = myquery("DELETE FROM bb_post WHERE tema IN (SELECT id FROM bb_tema WHERE zavrsni=$id)");
			$q917 = sprintf("DELETE FROM bb_tema WHERE zavrsni=$id");

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



	// Akcija DODJELA STUDENATA NA TEME

	elseif ($akcija == 'dodjela_studenata') {
		if ($_REQUEST['subakcija'] == "dodaj" && check_csrf_token()) {
			// Dodavanje studenta na temu završnog rada

			$student = intval($_REQUEST['student']);
			$zavrsni = intval($_REQUEST['zavrsni']);

			if ($param_zakljucan) {
				// Ne bi se smjelo desiti
				niceerror("Zaključane su prijave na teme završnih radova.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Da li je tema zauzeta?
			$q920 = myquery("select count(*) from student_zavrsni where zavrsni=$zavrsni");
			if (mysql_result($q920,0,0)>=$param_max_clanova) {
				// Ne bi se smjelo desiti
				niceerror("Tema je zauzeta.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Da li je student već zauzeo neku temu?
			$stari_zavrsni=0;
			$q921 = myquery("select z.id FROM zavrsni as z, student_zavrsni as sz WHERE z.id=sz.zavrsni AND sz.student=$student AND z.predmet=$predmet AND z.akademska_godina=$ag");
			while ($r921 = mysql_fetch_row($q921)) {
				$stari_zavrsni = $r921[0];
			}

			// Da li je prekoračen maksimalan broj zauzetih tema?
			$q902 = myquery("select distinct z.id from student_zavrsni as sz, zavrsni as z where sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
			$broj_nepraznih = mysql_num_rows($q902);
			if ($broj_nepraznih >= $param_max_tema) {
				// Ukoliko student uzima drugu temu onda je sve ok
				$prekoracenje = true;
				if ($stari_zavrsni!=0) {
					$q922 = myquery("select count(*) from student_zavrsni where zavrsni=$stari_zavrsni");
					if (mysql_result($q922,0,0) == 1) $prekoracenje = false;
				}

				if ($prekoracenje) {
					niceerror("Ne mogu upisati studenta za ovu temu završnog rada jer bi time bio prekoračen broj članova koji mogu da rade istu. $broj_nepraznih");
					print "<p>Koristite <a href='$linkPrefix&akcija=param'>Parametre završnih radova</a> da biste povećali ograničenje broja tema.</p>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
			}

			// Potvrđujemo prijavu
			$q923 = myquery("delete from student_zavrsni where student=$student and zavrsni=$stari_zavrsni");
			$q924 = myquery("INSERT INTO student_zavrsni (student, zavrsni) VALUES ($student, $zavrsni)");

			nicemessage('Student je uspješno prijavljen na temu završnog rada!');
			if ($stari_zavrsni==0)
				zamgerlog ("student u$student prijavljen na temu završnog rada $zavrsni (predmet pp$predmet", 2);
			else
				zamgerlog ("student u$student prebacen sa teme završnog rada $stari_zavrsni na $zavrsni (predmet pp$predmet", 2);

			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
		}


		$opcije = "";

		?>
		<!-- Ako je prvi put ucitano, dohvati predmete i dohvati sve studente na predmetu, prikazi formu. -->

		</br>
		<b>LISTA STUDENATA BEZ TEME ZAVRŠNOG RADA:</b>
		<?
			$q925 = myquery("SELECT o.id, o.ime, o.prezime, o.brindexa FROM student_zavrsni as sz, osoba as o, ponudakursa as pk where sz.student=o.id  and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime");

			if (mysql_num_rows($q925)==0) {
				nicemessage('Svim studentima je dodijeljena tema završnog rada!');
			} else {
				$cnt = 0;
				
				while ($r925 = mysql_fetch_row($q925)) {
					// Odmah kreiramo i opcije za selektovanje studenta
					$opcije .= "<option value='$r925[0]'>$r925[2] $r925[1]</option>\n";

					$q926 = myquery("select count(*) from student_zavrsni as sz, zavrsni as z where sz.student=$r925[0] and sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
					if (mysql_result($q926,0,0)>0) continue;
					$cnt = $cnt+1;
					print "</br>";
					print "<span id=\"noZavrsniStudent\">$cnt. $r925[2] $r925[1]</span>";
				}
				
			}
		?>
		<br><br><br>
		<b>DODAVANJE STUDENTA NA TEMU ZAVRŠNOG RADA</b><br>
		<span class="napomena">*Uputa:</span> Izaberite studenta, a zatim temu završnog rada i konačno kliknite Upiši!<br>
		<?=genform("POST"); ?>
		<input type="hidden" name="subakcija" value="dodaj">
			Student: <select name="student"><?=$opcije?></select><br/>
			Završni: <select name="zavrsni"><? 
			$cnt2 = 0;
			$q927 = myquery("SELECT id, naziv FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
			$rowcounter = 0;
			while ($r927 = mysql_fetch_row($q927)) {
				$cnt2 = $cnt2 +1;
				?>
				<option value="<?=$r927[0]?>"><?=$r927[1]?></option>
				<?  
			}
			?></select>
			<br />
			<input name="dodaj" type="submit" value="Upiši"/>
		</form>

		<p>Za ispisivanje studenta sa teme završnog rada, koristite listu tema završnih radova.</p>
		<?

	} //akcija - dodjela_studenata



	// Akcija BRISANJE STUDENTA SA TEME ZAVRSNOG RADA

	elseif ($akcija == 'izbaci_studenta') {
		$student = intval($_REQUEST['student']);
		$zavrsni = intval($_REQUEST['zavrsni']);
		
		if ($param_zakljucan==1) {
			niceerror('Zaključane su prijave na teme završnih radova. Odjave nisu dozvoljene.');
			return;
		}

		$q928 = myquery("select naziv from zavrsni where id=$zavrsni");
		if (mysql_num_rows($q928)<1) {
			niceerror("Nepostojeća tema završnog rada $zavrsni");
			return;
		}
		$naziv_teme = mysql_result($q928,0,0);

		$q929 = myquery("select ime, prezime from osoba where id=$student");
		if (mysql_num_rows($q929)<1) {
			niceerror("Nepostojeći student $student");
			return;
		}
		$imeprezime = mysql_result($q929,0,0)." ".mysql_result($q929,0,1);

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			$q930 = myquery("select z.id FROM zavrsni as z, student_zavrsni as sz WHERE z.id=sz.zavrsni AND sz.student=$student AND z.predmet=$predmet AND z.akademska_godina=$ag");
			if (mysql_num_rows($q930) > 0) {
				$student_zavrsni = mysql_result($q930,0,0);
				if ($zavrsni != $student_zavrsni) {
					niceerror("Student uopšte nije prijavljen na temu završnog rada $naziv_teme.");
				} else {
					$q931 = myquery("DELETE FROM student_zavrsni WHERE student=$student AND zavrsni=$student_zavrsni");
					print "Student $imeprezime uspješno odjavljen sa teme završnog rada $naziv_teme";
					zamgerlog("student u$student odjavljen sa teme završnog rada $zavrsni (pp$predmet)", 2);
					nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
				}
			} else {
				//Greska - student nije nigdje upisan
				niceerror("Student nije prijavljen niti na jednu temu završnog rada.");
			}
			return;
		}

		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="potvrda">
		Da li ste sigurni da želite ispisati studenta <?=$imeprezime?> sa teme završnog rada <?=$naziv_teme?>?<br>
		<input type="submit" value="Potvrda ispisa">
		<input type="button" onclick="location.replace('<?=$linkPrefix?>');" value="Odustani">
		</form>
		<?
	}

} // function

?>