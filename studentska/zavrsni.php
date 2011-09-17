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
	
	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<h2>Završni rad</h2>
	<?
	
	// Preuzimanje parametara završnih radova
	$q900 = myquery("SELECT naziv, nastavnik, student, kratki_pregled, literatura FROM zavrsni WHERE akademska_godina=$ag");
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
	if ($akcija != 'zavrsni_stranica')  {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="<?=$linkPrefix ?>">Lista tema završnih radova</a></li>
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
		$q901 = myquery("SELECT id, naziv, kratki_pregled, nastavnik, student, prvi_clan_komisije, drugi_clan_komisije, treci_clan_komisije, termin_odbrane, konacna_ocjena FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
		$broj_tema = mysql_num_rows($q901);
		if ($broj_tema > 0) {
			$q902 = myquery("select distinct z.id from student_zavrsni as sz, zavrsni as z where sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
			$broj_nepraznih = mysql_num_rows($q902);
		} 
		else {
			?>
			<span class="notice">Nema kreiranih tema završnih radova.</span>	
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
					<li <? if ($param_zakljucan == 0) { print 'class="last"'; } ?>><a href="<?=$linkPrefix."&akcija=obrisi_zavrsni&id=$id_zavrsni" ?>">Obriši temu završnog rada</a></li>
				</ul> 
			</div>

			<table class="zavrsni" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<th width="200" align="left" valign="top" scope="row">Naziv teme završnog rada</th>
					<td width="490" align="left" valign="top"><?=$r901[1]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Kratki pregled</th>
					<td width="490" align="left" valign="top"><?=$r901[2]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Odgovorni profesor</th>
					<td width="490" align="left" valign="top"><?=$r901[3]?></td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Prijavljeni student</th>
					<td width="490" align="left" valign="top"><?=$r901[4]?></td>
				</tr>
               	<tr>
					<th width="200" align="left" valign="top" scope="row">Prvi član komisije</th>
					<td width="490" align="left" valign="top">
                    	<?
							$prvi_clan_komisije = intval($_REQUEST['prvi_clan_komisije']);
							$naziv = intval($_REQUEST['naziv']);
							$ag = intval($_REQUEST['ag']);


							// Potvrđujemo prijavu
							$q990 = myquery("UPDATE zavrsni SET prvi_clan_komisije='$prvi_clan_komisije' WHERE naziv='$naziv'");
							
							$opcije = "";
							$cnt6 = 0;
							$q953 = myquery("SELECT id, nastavnik FROM nastavnik_predmet WHERE akademska_godina=$ag ORDER BY nastavnik");
							if(mysql_num_rows($q953)==0)
								nicemessage('Niti jedan profesor ne radi u trenutnoj akademskoj godini!');
			 				else  {
								$rowcounter6 = 0;
								while ($r953 = mysql_fetch_row($q953)) {
									// Odmah kreiramo i opcije za selektovanje studenta
									$opcije .= "<option value='$r953[0]'>$r953[1]</option>\n";
									$cnt6 = $cnt6 + 1;
									print "</br>";
									print "<span>$cnt6. $r953[1]</span>";
									?>
               						
                					<?
								}
							}
									?>
                		<?=genform("POST"); ?>
        				<input name="prvi_clan_komisije" type="hidden" id="prvi_clan_komisije" size="40" value="dodaj"/>
                    		<select name="prvi_clan_komisije"><?=$opcije?></select> &nbsp;&nbsp;&nbsp;
						<input name="dodaj" type="submit" value="Upiši"/>
						</form>
                    </td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Drugi član komisije</th>
					<td width="490" align="left" valign="top">
                    	<?
							$drugi_clan_komisije = intval($_REQUEST['drugi_clan_komisije']);
							$naziv = intval($_REQUEST['naziv']);
							$ag = intval($_REQUEST['ag']);


							// Potvrđujemo prijavu
							$q991 = myquery("UPDATE zavrsni SET drugi_clan_komisije='$drugi_clan_komisije' WHERE naziv='$naziv'");
			
							$opcije = "";
							$cnt7 = 0;
							$q954 = myquery("SELECT id, nastavnik FROM nastavnik_predmet WHERE akademska_godina=$ag ORDER BY nastavnik");
							if(mysql_num_rows($q954)==0)
								nicemessage('Niti jedan profesor ne radi u trenutnoj akademskoj godini!');
			 				else  {
								$rowcounter7 = 0;
								while ($r954 = mysql_fetch_row($q954)) {
									// Odmah kreiramo i opcije za selektovanje studenta
									$opcije .= "<option value='$r954[0]'>$r954[1]</option>\n";
									$cnt7 = $cnt + 1;
									print "</br>";
									print "<span>$cnt7. $r954[1]</span>";
									?>
               						
                					<?
								}
							}
									?>
                		<?=genform("POST"); ?>
        				<input name="drugi_clan_komisije" type="hidden" id="drugi_clan_komisije" size="40" value="dodaj"/>
                    		<select name="drugi_clan_komisije"><?=$opcije?></select> &nbsp;&nbsp;&nbsp;
						<input name="dodaj" type="submit" value="Upiši"/>
						</form>
                    </td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Treći član komisije</th>
					<td width="490" align="left" valign="top">
                    	<?
							$treci_clan_komisije = intval($_REQUEST['treci_clan_komisije']);
							$naziv = intval($_REQUEST['naziv']);
							$ag = intval($_REQUEST['ag']);

							// Potvrđujemo prijavu
							$q992 = myquery("UPDATE zavrsni SET treci_clan_komisije='$treci_clan_komisije' WHERE naziv='$naziv'");
							$opcije = "";
							$cnt8 = 0;
							$q955 = myquery("SELECT id, nastavnik FROM nastavnik_predmet WHERE akademska_godina=$ag ORDER BY nastavnik");
							if(mysql_num_rows($q955)==0)
								nicemessage('Niti jedan profesor ne radi u trenutnoj akademskoj godini!');
			 				else  {
								$rowcounter8 = 0;
								while ($r955 = mysql_fetch_row($q955)) {
									// Odmah kreiramo i opcije za selektovanje studenta
									$opcije .= "<option value='$r955[0]'>$r955[1]</option>\n";
									$cnt8 = $cnt8 + 1;
									print "</br>";
									print "<span>$cnt. $r955[1]</span>";
									?>
               						
                					<?
								}
							}
									?>
                		<?=genform("POST"); ?>
        				<input name="treci_clan_komisije" type="hidden" id="treci_clan_komisije" size="40" value="dodaj"/>
                    		<select name="treci_clan_komisije"><?=$opcije?></select> &nbsp;&nbsp;&nbsp;
						<input name="dodaj" type="submit" value="Upiši"/>
						</form>
                    </td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Termin odbrane završnog rada</th>
					<td width="490" align="left" valign="top">
                    	<?
							$termin_odbrane = intval($_REQUEST['termin_odbrane']);
							$naziv = intval($_REQUEST['naziv']);
							$ag = intval($_REQUEST['ag']);

							// Potvrđujemo prijavu
							$q993 = myquery("UPDATE zavrsni SET termin_odbrane='$termin_odbrane' WHERE naziv='$naziv'");
						?>
                		<?=genform("POST"); ?>
        				<input name="termin_odbrane" type="text" id="termin_odbrane" size="40" value="dodaj"/> &nbsp;&nbsp;&nbsp;
						<input name="dodaj" type="submit" value="Upiši"/>
						</form>
                    </td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Konačna ocjena</th>
					<td width="490" align="left" valign="top">
                    	<?
							$konacna_ocjena = intval($_REQUEST['konacna_ocjena']);
							$naziv = intval($_REQUEST['naziv']);
							$ag = intval($_REQUEST['ag']);

							// Potvrđujemo prijavu
							$q994 = myquery("UPDATE zavrsni SET konacna_ocjena='$konacna_ocjena' WHERE naziv='$naziv'");
						?>
                		<?=genform("POST"); ?>
        				<input name="konacna_ocjena" type="text" id="konacna_ocjena" size="40" value="dodaj"/> &nbsp;&nbsp;&nbsp;
						<input name="dodaj" type="submit" value="Upiši"/>
						</form>
                    </td>
				</tr>
			</table>
			<?
		} // while ($r901...
	} // if (!isset($akcija) - lista završnih radova


	// Akcija DODAJ TEMU ZAVRSNOG RADA

	elseif ($akcija == 'dodaj_zavrsni')  {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$kratki_pregled  = my_escape(trim($_REQUEST['kratki_pregled']));
			$nastavnik = my_escape(trim($_REQUEST['nastavnik']));
			$predmet = my_escape(trim($_REQUEST['predmet']));
			$literatura = my_escape(trim($_REQUEST['literatura']));
			$ag = my_escape(trim($_REQUEST['ag']));
			$smjer = my_escape(trim($_REQUEST['smjer']));
	
			$id = intval($_REQUEST['id']);
	
			if (empty($naziv) || empty($kratki_pregled) || empty($literatura) || empty($nastavnik) || empty($ag) || empty($smjer)) {
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
			$q906 = myquery("INSERT INTO zavrsni (id, naziv, predmet, akademska_godina, kratki_pregled, literatura, nastavnik, student) VALUES ($id, '$naziv', '$predmet', '$ag',  '$kratki_pregled', '$literatura', '$nastavnik', '$student')");
			//$q965 = myquery("INSERT INTO nastavnik_predmet(nastavnik, akademska_godina, predmet, nivo_pristupa) VALUES ('$nastavnik', '$ag', '$zr', '$np')");
			$q966 = myquery("INSERT INTO zavrsni_rad_predmet (id, predmet, akademska_godina, student, nastavnik) VALUES ($id, '$predmet', '$ag', '$student', '$nastavnik')");
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
					<span class="label">Smjer studija: *</span>
					<span class="formw"><input name="smjer" type="hidden" id="smjer" size="70" />
			 			<select name="smjer"><?
							$cnt3 = 0;
							$q950 = myquery("SELECT id, naziv FROM institucija ORDER BY naziv");
							if(mysql_num_rows($q950)==0)
								nicemessage('Niste unijeli ispravne podatke!');
			 				else  {
								$rowcounter3 = 0;
								while ($r950 = mysql_fetch_row($q950)) {
									$cnt3 = $cnt3 + 1;
									?>
               						 <option value="<?=$r950[0]?>"><?=$r950[1]?></option>
                					<?
								}
							}
									?>
                		</select>
                    </span> 
				</div>
                <div class="row">
					<span class="label">Akademska godina *</span>
					<span class="formw"><input name="ag" type="hidden" id="ag" size="70" />
			 			<select name="predmet"><?
							$cnt4 = 0;
							$q951 = myquery("SELECT id, naziv FROM akademska_godina ORDER BY naziv");
							if(mysql_num_rows($q951)==0)
								nicemessage('Niste unijeli ispravne podatke!');
			 				else  {
								$rowcounter4 = 0;
								while ($r951 = mysql_fetch_row($q951)) {
									$cnt4 = $cnt4 + 1;
									?>
               						 <option value="<?=$r951[0]?>"><?=$r951[1]?></option>
                					<?
								}
							}
									?>
                		</select>
                	</span>
				</div>
                <div class="row">
					<span class="label">Profesor *</span>
					<span class="formw"><input name="nastavnik" type="hidden" id="nastavnik" size="70" />
                    <select name="nastavnik"><?
							$cnt5 = 0;
							$q952 = myquery("SELECT id, nastavnik FROM nastavnik_predmet WHERE akademska_godina=$ag ORDER BY nastavnik");
							if(mysql_num_rows($q952)==0)
								nicemessage('Niti jedan profesor ne radi u trenutnoj akademskoj godini!');
			 				else  {
								$rowcounter5 = 0;
								while ($r952 = mysql_fetch_row($q952)) {
									$cnt5 = $cnt5 + 1;
									?>
               						 <option value="<?=$r952[0]?>"><?=$r952[1]?></option>
                					<?
								}
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
					<span class="label">Kratki pregled *</span>
					<span class="formw"><textarea name="kratki_pregled" cols="60" rows="15" wrap="physical" id="kratki_pregled"></textarea></span> 
				</div>
                <div class="row">
					<span class="label">Preporučena literatura *</span>
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
		$q909 = myquery("SELECT naziv, nastavnik, student, kratki_pregled, literatura FROM zavrsni WHERE id=$id");

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
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token())  {
			// Brisanje teme zavrsnog rada

			// Brisanje fajlova
			$lokacijafajlova = "$conf_files_path/zavrsni/fajlovi/";
			
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
	
} // function

?>
