<?php
// STUDENTSKA/ZAVRSNI - modul za definisanje zavrsnih radova, parametara, komisije, termina odbrane i konacne ocjene

function studentska_zavrsni() 
{
	require("lib/manip.php"); // radi ispisa studenata sa predmeta
	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Provjera privilegija
	if (!$user_studentska && !$user_siteadmin) 
	{
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
	
	// Glavni meni
	if ($akcija != 'zavrsni_stranica') 
	{
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="<?=$linkPrefix ?>">Lista tema završnih radova</a></li>
				<li><a href="<?=$linkPrefix."&akcija=dodaj_zavrsni" ?>">Nova tema završnog rada</a></li>
                <li><a href="<?=$linkPrefix."&akcija=dodaj_komisiju" ?>">Dodjela komisije i termina odbrane završnog rada</a></li>
                <li class="last"><a href="<?=$linkPrefix."&akcija=dodaj_ocjenu" ?>">Unos konačne ocjene odbrane završnog rada</a></li>
			</ul>
		</div>	
		<?
	}
	
	// Default akcija - LISTA ZAVRSNIH RADOVA
	if (!isset($akcija)) 
	{
		?>
		<h2>Lista tema završnih radova</h2>
		<?

		// Početne informacije
		$q901 = myquery("SELECT id, naziv, opis, nastavnik, student FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
		$broj_tema = mysql_num_rows($q901);
		if ($broj_tema > 0) 
		{
			$q902 = myquery("select distinct z.id from student_zavrsni as sz, zavrsni as z where sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
			$broj_nepraznih = mysql_num_rows($q902);
		} 
		else 
		{
			?>
			<span class="notice">Nema kreiranih tema završnih radova.</span>	
			<?
		}
	
		while ($r901 = mysql_fetch_row($q901)) 
		{
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
					<th width="200" align="left" valign="top" scope="row">Odgovorni profesor</th>
					<td width="490" align="left" valign="top"><?=$r901[3]?></td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Prijavljeni student</th>
					<td width="490" align="left" valign="top"><?=$r901[4]?></td>
				</tr>
				<tr>
					<th width="200" align="left" valign="top" scope="row">Opis</th>
					<td width="490" align="left" valign="top"><?=$r901[2]?></td>
				</tr>
			</table>
			<?
		} // while ($r901...
	} // if (!isset($akcija) - lista završnih radova


	// Akcija DODAJ TEMU ZAVRSNOG RADA

	elseif ($akcija == 'dodaj_zavrsni') 
	{
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) 
		{
			// Poslana forma za dodavanje teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$opis  = my_escape(trim($_REQUEST['opis']));
			$nastavnik = my_escape(trim($_REQUEST['nastavnik']));
			$predmet = my_escape(trim($_REQUEST['predmet']));
			$ag = my_escape(trim($_REQUEST['ag']));
			$student = my_escape(trim($_REQUEST['student']));
	
			$id = intval($_REQUEST['id']);
	
			if (empty($naziv) || empty($opis) || empty($nastavnik) || empty($predmet) || empty($ag) || empty($student)) 
			{
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
			$q906 = myquery("INSERT INTO zavrsni (id, naziv, predmet, akademska_godina, opis, nastavnik, student) VALUES ($id, '$naziv', '$predmet', '$ag',  '$opis', '$nastavnik', '$student')");
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
					<span class="label">Naziv *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Predmet *</span>
					<span class="formw"><input name="predmet" type="text" id="predmet" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Akademska godina *</span>
					<span class="formw"><input name="ag" type="text" id="ag" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Profesor *</span>
					<span class="formw"><input name="nastavnik" type="text" id="nastavnik" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Student *</span>
					<span class="formw"><input name="student" type="text" id="student" size="70" /></span> 
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

	elseif ($akcija == 'izmjena_zavrsni') 
	{
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) 
		{
			// Poslana forma za izmjenu teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$opis  = my_escape(trim($_REQUEST['opis']));
			$nastavnik = my_escape(trim($_REQUEST['nastavnik']));
			$student = my_escape(trim($_REQUEST['student']));
	
			if (empty($naziv) || empty($opis) || empty($nastavnik) || empty($student)) 
			{
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q907 = myquery("select count(*) from zavrsni where id=$id");
			if (mysql_result($q907,0,0)==0)
			{
				niceerror("Završni rad sa IDom $id ne postoji.");
				nicemessage('<a href="'.$linkPrefix.'">Povratak.</a>');
				return;
			}

			$q908 = myquery("UPDATE zavrsni SET naziv='$naziv', opis='$opis', nastavnik='$nastavnik', student='$student' WHERE id='$id'");

			nicemessage('Uspješno ste izmijenili temu završnog rada.');
			zamgerlog("izmijenio temu završnog rada $id na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		// Prikaz forme
		$q909 = myquery("SELECT naziv, predmet, nastavnik,student, opis FROM zavrsni WHERE id=$id");

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
					<span class="label">Predmet *</span>
					<span class="formw"><input name="predmet" type="text" id="predmet" size="70" value="<?=mysql_result($q900,0,1)?>"  /></span>
				</div> 
                <div class="row">
					<span class="label">Nastavnik *</span>
					<span class="formw"><input name="nastavnik" type="text" id="nastavnik" size="70" value="<?=mysql_result($q900,0,2)?>"  /></span>
				</div>
                <div class="row">
					<span class="label">Student *</span>
					<span class="formw"><input name="student" type="text" id="student" size="70" value="<?=mysql_result($q900,0,3)?>"  /></span>
				</div>
                <div class="row">
					<span class="label">Opis *</span>
					<span class="formw"><textarea name="opis" cols="60" rows="15" wrap="physical" id="nastavnik"><?=mysql_result($q900,0,4)?></textarea></span>
				</div>
				
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			
			</div><!--formDiv-->
		</form>
		<?
	} //akcija == izmjena_zavrsni


	// Akcija OBRIŠI TEMU ZAVRSNOG RADA

	elseif ($akcija == 'obrisi_zavrsni') 
	{
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) 
		{
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

	elseif ($akcija == 'zavrsni_stranica') 
	{
		require_once ('common/zavrsniStrane.php');
		common_zavrsniStrane();
	} //akcija == zavrsni_stranica


	//AKCIJA DODJELA KOMISIJE 
	
	elseif ($akcija == 'dodaj_komisiju') 
	{
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) 
		{
			// Poslana forma za dodavanje teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$predmet  = my_escape(trim($_REQUEST['opis']));
			$prvi_clan_komisije = my_escape(trim($_REQUEST['prvi_clan_komisije']));
			$drugi_clan_komisije = my_escape(trim($_REQUEST['drugi_clan_komisije']));
			$treci_clan_komisije = my_escape(trim($_REQUEST['treci_clan_komisije']));
			$termin_odbrane = my_escape(trim($_REQUEST['termin_odbrane']));
	
			if (empty($naziv) || empty($prvi_clan_komisije) || empty($drugi_clan_komisije) || empty($treci_clan_komisije) || empty($termin_odbrane)) 
			{
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			//$q960 = myquery ("SELECT count(*) FROM zavrsni WHERE naziv=$naziv");
			//if (mysql_result($q960,0,0)==1) 
			//{
			$q906 = myquery(" UPDATE zavrsni SET prvi_clan_komisije='$prvi_clan_komisije', drugi_clan_komisije='$drugi_clan_komisije', treci_clan_komisije='$treci_clan_komisije', termin_odbrane='$termin_odbrane' WHERE naziv='$naziv'");

			nicemessage('Uspješno ste dodali komisiju.');
			zamgerlog("dodani članovi komisije na temu završnog rada $naziv na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
			
		}
		
		?>	
		<h2>Dodjela komisije i termina odbrane</h2>
		<?=genform("POST", "addForm");?>
		<input type="hidden" name="subakcija" value="dodaj">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
				<div class="row">
					<span class="label">Tema završnog rada: *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
				</div>
				<div class="row">
					<span class="label">Prvi član komisije: *</span>
					<span class="formw"><input name="prvi_clan_komisije" type="text" id="prvi_clan_komisije" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Drugi član komisije: *</span>
					<span class="formw"><input name="drugi_clan_komisije" type="text" id="drugi_clan_komisije" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Treći član komisije: *</span>
					<span class="formw"><input name="treci_clan_komisije" type="text" id="treci_clan_komisije" size="70" /></span> 
				</div>
				<div class="row">
					<span class="label">Termin odbrane završnog rada: *</span>
					<span class="formw"><input name="termin_odbrane" type="text" id="termin_odbrane" size="70"></textarea></span>
				</div> 
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			</div><!--formDiv-->
		</form>
		<?
	}
	
	// AKCIJA DODAJ KONAČNU OCJENU ZA ODBRANU ZAVRŠNOG RADA
	elseif ($akcija == 'dodaj_ocjenu') 
	{
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) 
		{
			// Poslana forma za dodavanje teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$predmet  = my_escape(trim($_REQUEST['opis']));
			$konacna_ocjena = my_escape(trim($_REQUEST['konacna_ocjena']));
	
			if (empty($naziv) || empty($konacna_ocjena)) 
			{
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q961 = myquery ("SELECT count(*) FROM zavrsni WHERE naziv=$naziv");
			if (mysql_result($q960,0,0)==1) 
			{
				$q962 = myquery("UPDATE zavrsni SET konacna_ocjena='$konacna_ocjena' WHERE naziv='$naziv'");
				
				nicemessage('Uspješno ste dodali konačnu ocjenu.');
				zamgerlog("dodana konačna ocjena na temu završnog rada $naziv na predmetu pp$predmet", 2);
				nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
				
				return;
			}
		}
		?>	
		<h2>Dodjela konačne ocjene za odbranu završnog rada</h2>
		<?=genform("POST", "addForm");?>
		<input type="hidden" name="subakcija" value="dodaj">
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Tema završnog rada: *</span>
					<span class="formw"><input name="naziv" type="text" id="naziv" size="70" /></span> 
				</div>
                <div class="row">
					<span class="label">Konačna ocjena: *</span>
					<span class="formw"><input name="konacna_ocjena" type="text" id="konacna_ocjena" size="70" /></span> 
				</div>
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			</div><!--formDiv-->
		</form>
		<?
	}

} // function

?>
