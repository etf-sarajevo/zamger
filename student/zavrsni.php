<?

// STUDENT/ZAVRSNI - studenski modul za prijavu na teme zavrsnih radova i ulazak na stanicu zavrsnih



function student_zavrsni()  {
	//debug mod aktivan
	global $userid, $user_student;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);	
	
	// Da li student slusa predmet?
	$q900 = db_query("select sp.predmet, p.naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and pk.predmet=p.id");
	if (db_num_rows($q900)<1)  {
		zamgerlog("student ne sluša predmet pp$predmet", 3);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}
	$q15 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
	$tippredmeta = db_result($q15,0,0);
	if ($tippredmeta != 1000) {
		zamgerlog("student/zavrsni a nije završni", 3);
		biguglyerror("Modul za završne radove može se koristiti samo na predmetu 'Završni rad'");
		return;
	}

	
	$linkprefix = "?sta=student/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);
	
	if ($akcija == 'prijava') {
		$zavrsni = intval($_REQUEST['zavrsni']);
		
		$q105 = db_query("SELECT student FROM zavrsni WHERE id=$zavrsni AND predmet=$predmet AND akademska_godina=$ag AND tema_odobrena=1");
		if (db_num_rows($q105)<1) {
			niceerror("Završni rad nije sa ovog predmeta");
			zamgerlog("spoofing zavrsnog rada $zavrsni", 3);
			return;
		}
		if (db_result($q105,0,0)==$userid) {
			nicemessage("Uspješno ste prijavljeni za završni rad.");
			zamgerlog("vec prijavljen za zavrsni $zavrsni", 3);
			return;
		}
		if (db_result($q105,0,0)!=0) {
			nicemerror("Ovaj rad je već zauzet");
			zamgerlog("vec zauzet zavrsni $zavrsni", 3);
			return;
		}
		
		
		// Upisujemo u novu temu završnog rada
		$q110 = db_query("UPDATE zavrsni SET student=$userid WHERE id=$zavrsni");
		nicemessage("Uspješno ste prijavljeni na temu završnog rada");
		zamgerlog("student upisan na zavrsni $zavrsni", 2);
		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') {
		$zavrsni = intval($_REQUEST['zavrsni']);
		
		$q115 = db_query("SELECT student FROM zavrsni WHERE id=$zavrsni AND predmet=$predmet AND akademska_godina=$ag");
		if (db_num_rows($q115)<1) {
			niceerror("Završni rad nije sa ovog predmeta");
			zamgerlog("spoofing zavrsnog rada (odjava) $zavrsni", 3);
			return;
		}
		if (db_result($q115,0,0)==0) {
			nicemessage("Uspješno ste odjavljeni za završni rad.");
			zamgerlog("niko nije prijavljen na zavrsni $zavrsni", 3);
			return;
		}
		if (db_result($q115,0,0)!=$userid) {
			nicemerror("Niste prijavljeni za ovaj rad");
			zamgerlog("neko drugi prijavljen za $zavrsni", 3);
			return;
		}
		
		$q120 = db_query("UPDATE zavrsni SET student=0 WHERE id=$zavrsni");
		nicemessage("Uspješno ste odjavljeni sa teme završnog rada");
		zamgerlog("student ispisan sa zavrsnog rada $zavrsni", 2);
		
		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == odjava


	if ($akcija == 'zavrsni_stranica') {
		require_once('common/zavrsniStrane.php');
		common_zavrsniStrane();
		return;
	} //akcija == zavrsnistranica
	
	if ($akcija == 'detalji') {
		$zavrsni = intval($_REQUEST['zavrsni']);
		$q130 = db_query("select naslov, podnaslov, kratki_pregled, literatura, mentor, predsjednik_komisije, clan_komisije, student FROM zavrsni WHERE id=$zavrsni AND tema_odobrena=1");
		$naslov = db_result($q130,0,0);
		$podnaslov = db_result($q130,0,1);
		$kpregled = db_result($q130,0,2);
		$literatura = db_result($q130,0,3);
		$id_mentor = db_result($q130,0,4);
		$id_predkom = db_result($q130,0,5);
		$id_clankom = db_result($q130,0,6);
		$student = db_result($q130,0,7);

		?>
		<h2>Završni rad</h2>
		<h3>Detaljnije informacije o temi završnog rada</h3>
		<table border="0" cellpadding="10">
		<tr><td align="right" valign="top"><b>Naslov teme:</b></td><td><?=$naslov?></td></tr>
		<tr><td align="right" valign="top"><b>Podnaslov:</b></td><td><?=$podnaslov?></td></tr>
		<tr><td align="right" valign="top"><b>Kratki pregled teme:</b></td><td><?=$kpregled?></td></tr>
		<tr><td align="right" valign="top"><b>Literatura:</b></td><td><?=$literatura?></td></tr>
		<tr><td align="right" valign="top"><b>Mentor:</b></td><td><?=tituliraj($id_mentor, false)?></td></tr>
		<tr><td align="right" valign="top"><b>Predsjednik komisije:</b></td><td><?=tituliraj($id_predkom, false)?></td></tr>
		<tr><td align="right" valign="top"><b>Član komisije:</b></td><td><?=tituliraj($id_clankom, false)?></td></tr>
		</table>
		<?
		
		if ($student==$userid) {
			?>
			<p><b>Akcije:</b><br>
			<a href="<?=$linkprefix?>&zavrsni=<?=$zavrsni?>&akcija=odjava">Odjavi se sa ove teme</a><br>
			<a href="<?=$linkprefix?>&zavrsni=<?=$zavrsni?>&akcija=zavrsni_stranica">Stranica završnog rada</a>
			</p>
			<?

		} else if ($student==0) {
			?>
			<p><b>Akcije:</b><br>
			<a href="<?=$linkprefix?>&zavrsni=<?=$zavrsni?>&akcija=prijava">Prijavi se na ovu temu</a>
			</p>
			<?
		} else {
			?>
			<p>Ova tema je zauzeta!</p>
			<?
		}
		?>
		<p><a href="<?=$linkprefix?>">&lt; &lt; Nazad</a></p>
		<?
		return;
	}
	
	// Glavni ekran
	if (!isset($akcija)) {
	
		// Ako je kandidat potvrdjen, nema mogucnosti promjene teme
		// Prikazuje se stranica završnog rada
		$q800 = db_query("SELECT id,kandidat_potvrdjen FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag AND student=$userid");
		if (db_num_rows($q800)>0 && db_result($q800,0,1)==1) {
			$_REQUEST['zavrsni'] = db_result($q800,0,0);
			require_once('common/zavrsniStrane.php');
			common_zavrsniStrane();
			return;
		}
	
		?>
		<h2>Lista tema završnih radova</h2>
		<?
		
		// Početne informacije
		$q901 = db_query("SELECT id, naslov, mentor, student FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag AND tema_odobrena=1 ORDER BY naslov");
		$broj_tema = db_num_rows($q901);
		if ($broj_tema == 0) {
			?>
			<span class="notice">Nema kreiranih tema za završni rad.</span>	
			<?
		} else {
			?>
			<table border="1" cellspacing="0" cellpadding="2">
			<tr bgcolor="#CCCCCC"><td><b>R.br.</b></td><td><b>Tema</b></td><td><b>Mentor</b></td><td><b>Opcije</b></td></tr>
			<?
			$rbr=0;
		}
	
		while ($r901 = db_fetch_row($q901)) {
			$id_zavrsni = $r901[0];
			$naslov_teme = $r901[1];
			$naslov_teme = "<a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=detalji\">$naslov_teme</a>";
			$mentor = tituliraj($r901[2], false, false, true);
			$rbr++;
			if ($r901[3] == $userid) {
				$link = "<a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=odjava\">odjava</a> * <a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=zavrsnistranica\">stranica</a>";
			} else if ($r901[3] == 0) {
				$link = "<a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=prijava\">prijava</a> * <a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=detalji\">detalji</a>";
			} else {
				$link = "<font color='red'>zauzeta</font>";
			}

			?>
			<tr>
				<td><?=$rbr?>.</td>
				<td><?=$naslov_teme?></td>
				<td><?=$mentor?></td>
				<td><?=$link?></td> 
			</tr>
			<?
		} // while ($r901...
	} // if (!isset($akcija)
} //function
?>
