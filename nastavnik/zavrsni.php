<?php
// NASTAVNIK/ZAVRSNI 

function nastavnik_zavrsni() {

	global $userid, $user_nastavnik, $user_siteadmin;
	global $conf_files_path;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	

	// Da li korisnik ima pravo ući u modul?
	if (!$user_siteadmin) {
		$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
			zamgerlog("nastavnik/završni privilegije (predmet pp$predmet)",3);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	$linkPrefix = "?sta=nastavnik/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<h2>Završni rad</h2>
	<?
	
	//Preuzimanje parametara završnih radova
	$q900 = myquery("SELECT naziv, kratki_pregled, literatura, student FROM  zavrsni WHERE predmet=$predmet AND akademska_godina=$ag");
	if (mysql_num_rows($q900)<1)
		$nema_parametara = true;
	else {
		$nema_parametara = false;
		$param_naziv = mysql_result($q900,0,0);
		$param_kratki_pregled = mysql_result($q900,0,1);
		$param_literatura = mysql_result($q900,0,2);
	}
	
	// Glavni meni
	if ($akcija != 'projektna_stranica') {
		?>
		<div class="links">
			<ul class="clearfix">
				<li><a href="<?=$linkPrefix ?>">Lista završnih radova</a></li>
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
		$q980 = myquery("SELECT id, naziv, kratki_pregled, literatura, student FROM zavrsni WHERE nastavnik=$userid and predmet=$predmet AND akademska_godina=$ag ORDER BY naziv");
		$broj_tema = mysql_num_rows($q980);
		if ($broj_tema > 0) {
			$q981 = myquery("select distinct z.id from student_zavrsni as sz, zavrsni as z where sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
			$broj_nepraznih = mysql_num_rows($q981);
		} 
		else {
			?>
			<span class="notice">Nema kreiranih tema završnih radova.</span>	
			<?
		}
	
		while ($r980 = mysql_fetch_row($q980)) {
			$id_zavrsni = $r980[0];
			$naziv_teme = $r980[1];
			?>
			<h3><?=$naziv_teme?></h3>
			<div class="links">
				<ul class="clearfix" style="margin-bottom: 10px;">
					<li><a href="<?=$linkPrefix."&akcija=izmjena_zavrsni&id=$id_zavrsni" ?>">Izmijeni temu završnog rada</a></li>
                    <li><a href="<?=$linkPrefix."&akcija=dodaj_biljesku&id=$id_zavrsni" ?>">Dodaj bilješku</a></li>
                    <li class="last"><a href="<?= $linkPrefix . "&akcija=zavrsni_stranica&zavrsni=$id_zavrsni" ?>">Stranica završnih radova</a></li>
				</ul> 
			</div>

			<table class="zavrsni" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<th width="200" align="left" valign="top" scope="row">Naziv teme završnog rada</th>
					<td width="490" align="left" valign="top"><?=$r980[1]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Kratki pregled</th>
					<td width="490" align="left" valign="top"><?=$r980[2]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Preporučena literatura</th>
					<td width="490" align="left" valign="top"><?=$r980[3]?></td>
				</tr>                
				<tr>
					<th width="200" align="left" valign="top" scope="row">Prijavljeni student</th>
					<td width="490" align="left" valign="top"><?=$r980[4]?></td>
				</tr>
			</table>
			<?
		} // while 
	} // if (!isset($akcija) - lista završnih radova
	
	//Otvaranje stranica zavrsnih radova
	elseif ($akcija == 'zavrsni_stranica') {
		require_once ('common/zavrsniStrane.php');
		common_projektneStrane();
	} //akcija == zavrsni_stranica

	//akcija IZMJENA TEME ZAVRŠNOG RADA
	elseif ($akcija == 'izmjena_zavrsni') {
		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za izmjenu teme zavrsnog rada
			$naziv = my_escape(trim($_REQUEST['naziv']));
			$kratki_pregled  = my_escape(trim($_REQUEST['kratki_pregled']));
			$literatura  = my_escape(trim($_REQUEST['literatura']));
	
			if (empty($naziv) || empty($kratki_pregled) || empty($literatura)) {
				niceerror('Unesite sva obavezna polja.');
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}

			$q982 = myquery("select count(*) from zavrsni where id=$id");
			if (mysql_result($q982,0,0)==0) {
				niceerror("Završni rad sa IDom $id ne postoji.");
				nicemessage('<a href="'.$linkPrefix.'">Povratak.</a>');
				return;
			}

			$q908 = myquery("UPDATE zavrsni SET naziv='$naziv', kratki_pregled='$kratki_pregled', literatura='$literatura', WHERE id='$id'");

			nicemessage('Uspješno ste izmijenili temu završnog rada.');
			zamgerlog("izmijenio temu završnog rada $id na predmetu pp$predmet", 2);
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return;
		}

		// Prikaz forme
		$q909 = myquery("SELECT naziv, kratki_pregled, literatura FROM zavrsni WHERE id=$id");

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
	
	// Akcija DODAJ BILJEŠKU
	elseif ($akcija == 'dodaj_biljesku') {

		if ($_REQUEST['subakcija'] == "potvrda" && check_csrf_token()) {
			// Poslana forma za dodavanje bilješke
			$biljeska = my_escape($_REQUEST['biljeska']);
			$q250 = myquery("UPDATE zavrsni SET biljeska='$biljeska' WHERE id=$id");

			nicemessage('Uspješno ste dodali bilješku.');
			zamgerlog("dodao biljesku na zavrsni rad $id", 2);		
			nicemessage('<a href="'. $linkPrefix .'">Povratak.</a>');
			return; 
		}

		// Forma za izmjenu/dodavanje bilješke
		$q260 = myquery("SELECT biljeska FROM zavrsni WHERE id=$id");

		?>
		<h3>Dodaj bilješku za temu završnog rada</h3>	
		<?=genform('POST','addNote'); ?>			
		<input type="hidden" name="subakcija" value="potvrda">
			<div class="row">
				<span class="label">Bilješka:</span>
				<span class="formw"><textarea name="biljeska" cols="60" rows="15" wrap="physical" id="opis"><?=mysql_result($q260,0,0)?></textarea></span>
			</div> 
					
			<div class="row">	
				<span class="formw" style="margin-left:150px;"><input type="submit" id="submit" value="Potvrdi"/></span>
			</div>
		</form>
		<?
	} //akcija == dodaj biljesku

} // function
?>
