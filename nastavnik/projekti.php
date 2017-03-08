<?php

// NASTAVNIK/PROJEKTI - nastavnicki modul za definisanje projekata, parametara



function nastavnik_projekti() {

	require_once ('lib/projekti.php');
	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Da li korisnik ima pravo ući u modul?
	if (!$user_siteadmin) 
	{
		$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (db_num_rows($q10)<1 || db_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/projekti privilegije (predmet pp$predmet)",3);
			zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	$linkPrefix = "?sta=nastavnik/projekti&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	?>
	<LINK href="static/css/projekti.css" rel="stylesheet" type="text/css">
	<h2>Projekti</h2>
	<?
	
	// Preuzimanje projektnih parametara
	$q10 = db_query("SELECT min_timova, max_timova, min_clanova_tima, max_clanova_tima, zakljucani_projekti FROM predmet_projektni_parametri WHERE predmet=$predmet AND akademska_godina=$ag");
	if (db_num_rows($q10)<1)
		$nema_parametara = true;
	else {
		$nema_parametara = false;
		$param_min_timova = db_result($q10,0,0);
		$param_max_timova = db_result($q10,0,1);
		$param_min_clanova_tima = db_result($q10,0,2);
		$param_max_clanova_tima = db_result($q10,0,3);
		$param_zakljucan = db_result($q10,0,4);
	}

	// Glavni meni
	if ($akcija != 'projektna_stranica') {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="<?=$linkPrefix."&akcija=param"?>">Parametri projekata</a></li>
				<li><a href="<?=$linkPrefix ?>">Lista projekata</a></li>
				<li><a href="<?=$linkPrefix."&akcija=dodaj_projekat" ?>">Novi projekat</a></li>
				<li class="last"><a href="<?=$linkPrefix."&akcija=dodjela_studenata"?>">Dodjela projekata studentima</a></li>
			</ul>
		</div>	
		<?
	}
	
	// Default akcija - LISTA PROJEKATA
	if (!isset($akcija)) {
		?>
		<h2>Lista projekata</h2>
		<?

		// Početne informacije
		$q100 = db_query("SELECT id, naziv, opis FROM projekat WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
		$broj_projekata = db_num_rows($q100);
		if ($broj_projekata > 0) {
			if ($param_zakljucan == 1) {
				?>
				<span class="notice">Onemogućene su prijave u projektne timove. Otvorene su projektne stranice.</span>	
				<?
			} else {
				?>
				<span class="noticeGreen">Studenti se još uvijek mogu prijaviti u projektne timove. Niste zaključali spiskove u parametrima prijekata.</span>	
				<?
			}

			$q460 = db_query("select distinct p.id from student_projekat as sp, projekat as p where sp.projekat=p.id and p.predmet=$predmet and p.akademska_godina=$ag");
			$broj_nepraznih = db_num_rows($q460);

			if ($broj_nepraznih < $param_min_timova) {
				?>
				<span class="notice">Trenutni broj timova sa barem jednim studentom (<?=$broj_nepraznih?>) je ispod minimalnog broj timova koji ste definisali za ovaj predmet (<?=$param_min_timova?>).</span>
				<?
			}

		} else {
			?>
			<span class="notice">Nema kreiranih projekata na ovom predmetu.</span>	
			<?
		}
	
		while ($r100 = db_fetch_row($q100)) {
			$id_projekta = $r100[0];
			$naziv_projekta = $r100[1];
	
			?>
			<h3><?=$naziv_projekta?></h3>
			<div class="links">
				<ul class="clearfix" style="margin-bottom: 10px;">
					<li><a href="<?=$linkPrefix."&akcija=izmjena_projekta&id=$id_projekta" ?>">Izmijeni projekat</a></li>
					<li><a href="<?=$linkPrefix."&akcija=dodaj_biljesku&id=$id_projekta" ?>">Dodaj bilješku</a></li>
					<li <? if ($param_zakljucan == 0) { print 'class="last"'; } ?>><a href="<?=$linkPrefix."&akcija=obrisi_projekat&id=$id_projekta" ?>">Obriši projekat</a></li>
					<?
					if ($param_zakljucan == 1) {
						?>
						<li class="last"><a href="<?= $linkPrefix . "&akcija=projektna_stranica&projekat=$id_projekta" ?>">Projektna stranica</a></li>
						<?
					}
					?>
				</ul> 
				<?

				$q110 = db_query("SELECT COUNT(id) FROM osoba as o, student_projekat as sp where o.id=sp.student and sp.projekat=$id_projekta");
				$broj_clanova = db_result($q110,0,0);
				if ($broj_clanova < $param_min_clanova_tima) {
					?>
					<span class="notice">Broj prijavljenih studenata (<?=$broj_clanova?>) je ispod minimuma koji ste definisali za ovaj predmet (<?=$param_min_clanova_tima?>).</span>	
					<?
				}

				?>
			</div>

			<table class="projekti" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<th width="200" align="left" valign="top" scope="row">Naziv</th>
					<td width="490" align="left" valign="top"><?=$naziv_projekta?></td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Prijavljeni studenti</th>
					<td width="490" align="left" valign="top">
					<?

					// Spisak studenata
					$q120 = db_query("SELECT o.id, o.prezime, o.ime, o.brindexa FROM osoba as o, student_projekat as sp WHERE sp.student=o.id and sp.projekat=$id_projekta ORDER BY o.prezime, o.ime");
					if (db_num_rows($q120)<1)
						print 'Nema prijavljenih studenata.';
					else {
						print "<ul>\n";
						while ($r120 = db_fetch_row($q120)) {
							print "<li>$r120[1] $r120[2] ($r120[3])";
							if ($param_zakljucan==0) {
								print ' - (<a href="'.$linkPrefix."&akcija=izbaci_studenta&student=$r120[0]&projekat=$id_projekta".'">izbaci</a>)';
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
					<td width="490" align="left" valign="top"><?=$r100[2]?></td>
				</tr>
			</table>
			<?
		} // while ($r100...
	} // if (!isset($akcija)



	// Akcija PARAMETRI PROJEKATA

	if ($akcija == 'param') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za promjenu
			$min_timova = intval($_REQUEST['min_timova']);
			$max_timova  = intval($_REQUEST['max_timova']);
			
			$min_clanova_tima = intval($_REQUEST['min_clanova_tima']);
			$max_clanova_tima = intval($_REQUEST['max_clanova_tima']);
			
			$zakljucani_projekti = 0;
			if (isset($_REQUEST['lock'])) $zakljucani_projekti = 1;
			
			if ($min_timova <= 0 || $max_timova <= 0 || $min_clanova_tima <= 0 || $max_clanova_tima <= 0) {
				niceerror("Morate unijeti ispravne vrijednosti u sva polja");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q200 = db_query("REPLACE predmet_projektni_parametri SET predmet=$predmet, akademska_godina=$ag, min_timova=$min_timova, max_timova=$max_timova, min_clanova_tima=$min_clanova_tima, max_clanova_tima=$max_clanova_tima, zakljucani_projekti=$zakljucani_projekti");

			nicemessage('Uspješno ste uredili parametre projekata.');
			zamgerlog("izmijenio parametre projekata na predmetu pp$_REQUEST[predmet]", 2);
			zamgerlog2("izmijenjeni parametri projekata na predmetu", $predmet, $ag);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		<h2>Parametri projekata</h2>

		<?=genform("POST", "editForm"); ?>
		<input type="hidden" name="subakcija" value="potvrda">
		<div id="formDiv">
			Polja sa * su obavezna. <br />
			<div class="row">
				<span class="label">Zaključaj stanje projekata i timova</span>
				<span class="formw"><input name="lock" type="checkbox" id="lock" <? if ($param_zakljucan == 1) print 'checked';?> /></span> 
				<br /><br /> Ova opcija će onemogućiti prijavljivanje na projekte i pokrenuti projektne stranice.
			</div>
			<div class="row">
				<span class="label">MIN timova *</span>
				<span class="formw"><input name="min_timova" type="text" id="min_timova" size="10" value="<?=$param_min_timova;?>" /></span> 
			</div>
			<div class="row">
				<span class="label">MAX timova *</span>
				<span class="formw"><input name="max_timova" type="text" id="max_timova" size="10" value="<?=$param_max_timova?>" /></span> 
			</div>
			<div class="row">
				<span class="label">MIN članova tima *</span>
				<span class="formw"><input name="min_clanova_tima" type="text" id="min_clanova_tima" size="10" value="<?=$param_min_clanova_tima?>" /></span> 
			</div>
			<div class="row">
				<span class="label">MAX članova tima *</span>
				<span class="formw"><input name="max_clanova_tima" type="text" id="max_clanova_tima" size="10" value="<?=$param_max_clanova_tima?>" /></span> 
			</div>
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</div><!--formDiv-->
		</form>
		<?
	}



	// Akcija DODAJ PROJEKAT

	elseif ($akcija == 'dodaj_projekat') {
		if ($nema_parametara && !isset($_REQUEST['submit'])) {
			nicemessage("Prvo podesite parametre projekata.");
			nicemessage('<a href="'. $linkPrefix .'&akcija=param">Parametri projekata</a>');
			return;
		}

		if ($param_zakljucan == 1) {
			niceerror("Zaključali ste stanje projekata na ovom predmetu. Nije moguće napraviti novi projekat.");
			nicemessage('<a href="'. $linkPrefix .'&akcija=param">Parametri projekata</a>');
			return;
		}

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje projekta
			$naziv = db_escape(trim($_REQUEST['naziv']));
			$opis  = db_escape(trim($_REQUEST['opis']));
	
			$id = intval($_REQUEST['id']);
	
			if (empty($naziv) || empty($opis)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Generišemo jedinstven ID
			$qnesta = db_query("select id from projekat order by id desc limit 1");
			if (db_num_rows($qnesta)<1)
				$id = 1;
			else
				$id = db_result($qnesta,0,0)+1;
	
			$q210 = db_query("INSERT INTO projekat (id, naziv, opis, predmet, akademska_godina) VALUES ($id, '$naziv', '$opis', '$predmet', '$ag')");

			nicemessage('Novi projekat uspješno dodan.');
			zamgerlog("dodao novi projekat na predmetu pp$predmet", 2);
			zamgerlog2("dodao projekat", db_insert_id(), $predmet, $ag);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}
		
		?>	
		<h2>Novi projekat</h2>
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



	// Akcija IZMJENA PROJEKTA

	elseif ($akcija == 'izmjena_projekta') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za izmjenu projekta
			$naziv = db_escape(trim($_REQUEST['naziv']));
			$opis  = db_escape(trim($_REQUEST['opis']));
	
			if (empty($naziv) || empty($opis)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q220 = db_query("select count(*) from projekat where id=$id");
			if (db_result($q220,0,0)==0) {
				niceerror("Projekat sa IDom $id ne postoji.");
				nicemessage('<a href="'.$linkPrefix.'">Povratak.</a>');
				return;
			}


			$q230 = db_query("UPDATE projekat SET naziv='$naziv', opis='$opis' WHERE id='$id'");

			nicemessage('Uspješno ste izmijenili projekat.');
			zamgerlog("izmijenio projekat $id na predmetu pp$predmet", 2);
			zamgerlog2("izmijenio projekat", $id);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		// Prikaz forme
		$q240 = db_query("SELECT naziv, opis FROM projekat WHERE id=$id");

		?>
		<h1>Izmijeni projekat</h1>
		<?=genform("POST", "editForm");?>
		<input type="hidden" name="subakcija" value="potvrda">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" value="<?=db_result($q10,0,0)?>" /></span> 
				</div>
				<div class="row">
					<span class="label">Opis *</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="opis"><?=db_result($q10,0,1)?></textarea></span>
				</div> 
				
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			
			</div><!--formDiv-->
		</form>
		<?
	} //akcija == izmjena_projekta



	// Akcija DODAJ BILJEŠKU

	elseif ($akcija == 'dodaj_biljesku') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje bilješke
			$biljeska = db_escape($_REQUEST['biljeska']);
			$q250 = db_query("UPDATE projekat SET biljeska='$biljeska' WHERE id=$id");

			nicemessage('Uspješno ste dodali bilješku.');
			zamgerlog("dodao biljesku na projekat $id na predmetu pp$predmet", 2);
			zamgerlog2("dodao biljesku na projekat", $id);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		$q260 = db_query("SELECT biljeska FROM projekat WHERE id=$id");

		?>
		<h3>Dodaj bilješku za projekat</h3>	
		<?=genform('POST','addNote'); ?>			
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" wrap="physical" id="opis"><?=db_result($q260,0,0)?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj biljesku


	// Akcija OBRIŠI PROJEKAT

	elseif ($akcija == 'obrisi_projekat') {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Brisanje projekta

			// Brisanje fajlova
			$lokacijafajlova = "$conf_files_path/projekti/fajlovi/";
			if (!rmdir_recursive($lokacijafajlova . $id)) {
				// Ignorišemo greške jer šta ako fajlovi nisu ni postojali??

				//niceerror("Greška prilikom brisanja direktorija sa člancima!");
				//zamgerlog("greška prilikom brisanja direktorija fajlovi za projekat $id", 3);
				//return;
			}

			// Brisanje članaka
			$lokacijaclanaka ="$conf_files_path/projekti/clanci/";
			if (!rmdir_recursive($lokacijaclanaka . $id)) { 
				// Ignorišemo greške jer šta ako fajlovi nisu ni postojali??

				//niceerror("Greška prilikom brisanja direktorija sa člancima!");
				//zamgerlog("greška prilikom brisanja direktorija clanci za projekat $id", 3);
				//return;
			}
			
			$q300 = db_query("DELETE FROM bl_clanak WHERE projekat=$id");

			// Brisanje linkova
			$q310 = db_query("DELETE FROM projekat_link WHERE projekat=$id");
	
			// Brisanje RSSa
			$q320 = db_query("DELETE FROM projekat_rss WHERE projekat=$id"); 

			// Brisanje foruma
			$q330 = db_query("DELETE FROM bb_post_text WHERE post IN (SELECT id FROM bb_post WHERE tema IN (SELECT id FROM bb_tema WHERE projekat=$id) )");
			$q340 = db_query("DELETE FROM bb_post WHERE tema IN (SELECT id FROM bb_tema WHERE projekat=$id)");
			$q350 = sprintf("DELETE FROM bb_tema WHERE projekat=$id");

			// Ispis studenata sa projekta
			$q360 = db_query("DELETE FROM student_projekat WHERE projekat=$id");
			
			// Brisanje samog projekta
			$q370 = db_query("DELETE FROM projekat WHERE id=$id");

			nicemessage('Uspješno ste obrisali projekat.');	
			zamgerlog("izbrisan projekat $id na predmetu pp$predmet", 4);
			zamgerlog2("izbrisan projekat", $id, $predmet, $ag);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		?>
		Da li ste sigurni da želite obrisati ovaj projekat? Svi podaci vezani za aktivnosti na ovom projektu će biti nepovratno izgubljeni.<br />
		<?=genform('POST'); ?>
		<input type="hidden" name="subakcija" value="potvrda">
		<input type="submit" value="Potvrda brisanja">
		<input type="button" onclick="location.replace('<?=$linkPrefix?>');" value="Odustani">
		</form>
		<?
	
	} //akcija == obrisi_projekat


	// Akcija PROJEKTNA STRANICA

	elseif ($akcija == 'projektna_stranica') {
		require_once ('common/projektneStrane.php');
		common_projektneStrane();
	} //akcija == projektna_stranica



	// Akcija DODJELA STUDENATA NA PROJEKTE

	elseif ($akcija == 'dodjela_studenata') {
		if ($_REQUEST['subakcija'] == "dodaj" && check_csrf_token()) {
			// Dodavanje studenta na projekat

			$student = intval($_REQUEST['student']);
			$projekat = intval($_REQUEST['projekat']);

			if ($param_zakljucan) {
				// Ne bi se smjelo desiti
				niceerror("Zaključane su prijave na projekte.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Da li je projekat popunjen?
			$q430 = db_query("select count(*) from student_projekat where projekat=$projekat");
			if (db_result($q430,0,0)>=$param_max_clanova_tima) {
				// Ne bi se smjelo desiti
				niceerror("Projekat je popunjen.");
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			// Da li je student već na nekom projektu?
			$stari_projekat=0;
			$q440 = db_query("select p.id FROM projekat as p, student_projekat as sp WHERE p.id=sp.projekat AND sp.student=$student AND p.predmet=$predmet AND p.akademska_godina=$ag");
			while ($r440 = db_fetch_row($q440)) {
				$stari_projekat = $r440[0];
			}

			// Da li je prekoračen maksimalan broj nepraznih projekata?
			$q460 = db_query("select distinct p.id from student_projekat as sp, projekat as p where sp.projekat=p.id and p.predmet=$predmet and p.akademska_godina=$ag");
			$broj_nepraznih = db_num_rows($q460);
			if ($broj_nepraznih >= $param_max_timova) {
				// No ako studenta ispisujemo iz projekta koji će postati prazan onda je sve ok
				$prekoracenje = true;
				if ($stari_projekat!=0) {
					$q470 = db_query("select count(*) from student_projekat where projekat=$stari_projekat");
					if (db_result($q470,0,0) == 1) $prekoracenje = false;
				}

				if ($prekoracenje) {
					niceerror("Ne mogu upisati studenta na ovaj projekat jer bi time bio prekoračen maksimalan broj timova. $broj_nepraznih");
					print "<p>Koristite <a href='$linkPrefix&akcija=param'>Parametre projekata</a> da biste povećali ograničenje broja timova.</p>";
					nicemessage('<a href="javascript:history.back();">Povratak.</a>');
					return;
				}
			}

			// Potvrđujemo prijavu
			$q450 = db_query("delete from student_projekat where student=$student and projekat=$stari_projekat");
			$q480 = db_query("INSERT INTO student_projekat (student, projekat) VALUES ($student, $projekat)");

			nicemessage('Student je uspješno prijavljen na projekat!');
			if ($stari_projekat==0) {
				zamgerlog ("student u$student prijavljen na projekat $projekat (predmet pp$predmet", 2);
				zamgerlog2 ("student prijavljen na projekat", $student, $projekat);
			} else {
				zamgerlog ("student u$student prebacen sa projekta $stari_projekat na $projekat (predmet pp$predmet", 2);
				zamgerlog2 ("student prebacen na projekat", $student, $projekat, 0, $stari_projekat);
			}

			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
		}


		$opcije = "";

		?>
		<!-- Ako je prvi put ucitano, dohvati predmete i dohvati sve studente na predmetu, prikazi formu. -->

		</br>
		<b>LISTA STUDENATA BEZ PROJEKTA:</b>
		<?
			$q400 = db_query("SELECT o.id, o.ime, o.prezime, o.brindexa FROM student_predmet as sp, osoba as o, ponudakursa as pk where sp.student=o.id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime");

			if (db_num_rows($q400)==0) {
				nicemessage('Svim studentima je dodijeljen projekat!');
			} else {
				$cnt = 0;
				
				while ($r400 = db_fetch_row($q400)) {
					// Odmah kreiramo i opcije za selektovanje studenta
					$opcije .= "<option value='$r400[0]'>$r400[2] $r400[1]</option>\n";

					$q410 = db_query("select count(*) from student_projekat as sp, projekat as p where sp.student=$r400[0] and sp.projekat=p.id and p.predmet=$predmet and p.akademska_godina=$ag");
					if (db_result($q410,0,0)>0) continue;
					$cnt = $cnt+1;
					print "</br>";
					print "<span id=\"noProjectStudent\">$cnt. $r400[2] $r400[1]</span>";
				}
				
			}
		?>
		<br><br><br>
		<b>DODAVANJE STUDENTA NA PROJEKAT</b><br>
		<span class="napomena">*Uputa:</span> Izaberite studenta, a zatim projekat i konačno kliknite Upiši!<br>
		<?=genform("POST"); ?>
		<input type="hidden" name="subakcija" value="dodaj">
			Student: <select name="student"><?=$opcije?></select><br/>
			Projekat: <select name="projekat"><? 
			$cnt2 = 0;
			$q420 = db_query("SELECT id, naziv FROM projekat WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
			$rowcounter = 0;
			while ($r420 = db_fetch_row($q420)) {
				$cnt2 = $cnt2 +1;
				?>
				<option value="<?=$r420[0]?>"><?=$r420[1]?></option>
				<?  
			}
			?></select>
			<br />
			<input name="dodaj" type="submit" value="Upiši"/>
		</form>

		<p>Za ispisivanje studenta sa projekta, koristite listu projekata.</p>
		<?

	} //akcija - dodjela_studenata



	// Akcija BRISANJE STUDENTA SA PROJEKTA

	elseif ($akcija == 'izbaci_studenta') {
		$student = intval($_REQUEST['student']);
		$projekat = intval($_REQUEST['projekat']);
		
		if ($param_zakljucan==1) {
			niceerror('Zaključane su prijave na projekte. Odjave nisu dozvoljene.');
			return;
		}

		$q500 = db_query("select naziv from projekat where id=$projekat");
		if (db_num_rows($q500)<1) {
			niceerror("Nepostojeći projekat $projekat");
			return;
		}
		$naziv_projekta = db_result($q500,0,0);

		$q505 = db_query("select ime, prezime from osoba where id=$student");
		if (db_num_rows($q505)<1) {
			niceerror("Nepostojeći student $student");
			return;
		}
		$imeprezime = db_result($q505,0,0)." ".db_result($q505,0,1);

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			$q510 = db_query("select p.id FROM projekat as p, student_projekat as sp WHERE p.id=sp.projekat AND sp.student=$student AND p.predmet=$predmet AND p.akademska_godina=$ag");
			if (db_num_rows($q510) > 0) {
				$student_projekat = db_result($q510,0,0);
				if ($projekat != $student_projekat) {
					niceerror("Student uopšte nije prijavljen na projekat $naziv_projekta.");
				} else {
					$q520 = db_query("DELETE FROM student_projekat WHERE student=$student AND projekat=$student_projekat");
					print "Student $imeprezime uspješno odjavljen sa projekta $naziv_projekta";
					zamgerlog("student u$student odjavljen sa projekta $projekat (pp$predmet)", 2);
					zamgerlog2("student odjavljen sa projekta", $student, $projekat);
					nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
				}
			} else {
				//Greska - student nije nigdje upisan
				niceerror("Student nije prijavljen niti na jedan projekat.");
			}
			return;
		}

		?>
		<?=genform("POST")?>
		<input type="hidden" name="subakcija" value="potvrda">
		Da li ste sigurni da želite ispisati studenta <?=$imeprezime?> sa projekta <?=$naziv_projekta?>?<br>
		<input type="submit" value="Potvrda ispisa">
		<input type="button" onclick="location.replace('<?=$linkPrefix?>');" value="Odustani">
		</form>
		<?
	}

} // function


/*function rmdir_recursive($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) 
	{
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname))
	{
        return unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) 
	{
        // Skip pointers
        if ($entry == '.' || $entry == '..') 
		{
            continue;
        }

        // Recurse
        rmdir_recursive($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return rmdir($dirname);
}*/


?>