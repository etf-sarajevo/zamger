<?

// STUDENT/ZAVRSNI - studenski modul za prijavu na teme zavrsnih radova i ulazak na stanicu zavrsnih



function student_zavrsni()  {
	global $userid, $_api_http_code;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);

	// Test if student is enrolled on course (remove?)
	$course = getCourseDetails($predmet, $ag);
	if (empty($course)) {
		zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		biguglyerror("Nepoznat predmet");
		return;
	}
	
	$linkprefix = "?sta=student/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$zavrsni = intval($_REQUEST['zavrsni']);
	$thesis = [];
	if ($zavrsni > 0) {
		$thesis = api_call("thesis/$zavrsni", [ "resolve" => [ "Person" ]]);
		if ($_api_http_code != "200") {
			niceerror("Završni rad nije sa ovog predmeta");
			zamgerlog("spoofing zavrsnog rada $zavrsni", 3);
			api_report_bug($thesis, []);
			return;
		}
	}
	
	if ($akcija == 'prijava') {
		if ($zavrsni == 0) {
			niceerror("Nije naveden završni rad");
			return;
		}
		if ($thesis['candidate']['id'] == $userid) {
			nicemessage("Uspješno ste prijavljeni za završni rad.");
			zamgerlog("vec prijavljen za zavrsni $zavrsni", 3);
			return;
		}
		if ($thesis['candidate']['id'] != null) {
			niceerror("Ovaj rad je već zauzet");
			zamgerlog("vec zauzet zavrsni $zavrsni", 3);
			return;
		}
	
		
		// Upisujemo u novu temu završnog rada
		$result = api_call("thesis/$zavrsni/register/$userid", [], "POST");
		if ($_api_http_code == "201") {
			nicemessage("Uspješno ste prijavljeni na temu završnog rada");
			zamgerlog("student upisan na zavrsni $zavrsni", 2);
		} else {
			niceerror("Prijava nije uspjela");
			api_report_bug($result, []);
		}
		print '<a href="' . $linkprefix . '">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') {
		if ($zavrsni == 0) {
			niceerror("Nije naveden završni rad");
			return;
		}
		if ($thesis['candidate']['id'] == null) {
			nicemessage("Uspješno ste odjavljeni za završni rad.");
			zamgerlog("niko nije prijavljen na zavrsni $zavrsni", 3);
			return;
		}
		if ($thesis['candidate']['id'] != $userid) {
			niceerror("Niste prijavljeni za ovaj rad");
			zamgerlog("neko drugi prijavljen za $zavrsni", 3);
			return;
		}
		if ($thesis['candidateApproved']) {
			niceerror("Ne možete se odjaviti sa potvrđenog rada");
			return;
		}
		
		$result = api_call("thesis/$zavrsni/unregister", [], "POST");
		if ($_api_http_code == "201") {
			nicemessage("Uspješno ste odjavljeni sa teme završnog rada");
			zamgerlog("student ispisan sa zavrsnog $zavrsni", 2);
		} else {
			niceerror("Odjava nije uspjela");
			api_report_bug($result, []);
		}
		print '<a href="' . $linkprefix . '">Povratak.</a>';
		return;
	} // akcija == odjava


	if ($akcija == 'zavrsni_stranica') {
		require_once('common/zavrsniStrane.php');
		common_zavrsniStrane($thesis);
		return;
	} //akcija == zavrsnistranica
	
	if ($akcija == 'detalji') {
		if ($zavrsni == 0) {
			niceerror("Nije naveden završni rad");
			return;
		}
		
		$mentori = "";
		foreach($thesis['menthors'] as $menthor) {
			if ($mentori != "") $mentori .= "<br>\n";
			$mentori .= tituliraj_api($menthor, false);
		}
		$clanovi = "";
		foreach($thesis['committeeMembers'] as $member) {
			if ($clanovi != "") $clanovi .= "<br>\n";
			$clanovi .= tituliraj_api($member, false);
		}
		
		?>
		<h2>Završni rad</h2>
		<h3>Detaljnije informacije o temi završnog rada</h3>
		<table border="0" cellpadding="10">
		<tr><td align="right" valign="top"><b>Naslov teme:</b></td><td><?=$thesis['title']?></td></tr>
		<tr><td align="right" valign="top"><b>Podnaslov:</b></td><td><?=$thesis['subtitle']?></td></tr>
		<tr><td align="right" valign="top"><b>Kratki pregled teme:</b></td><td><?=$thesis['description']?></td></tr>
		<tr><td align="right" valign="top"><b>Literatura:</b></td><td><?=$thesis['literature']?></td></tr>
		<tr><td align="right" valign="top"><b>Mentor(i):</b></td><td><?=$mentori?></td></tr>
		<tr><td align="right" valign="top"><b>Predsjednik komisije:</b></td><td><?=tituliraj_api($thesis['committeeChair'], false)?></td></tr>
		<tr><td align="right" valign="top"><b>Član(ovi) komisije:</b></td><td><?=$clanovi?></td></tr>
		</table>
		<?
		
		if ($thesis['candidate']['id'] == $userid) {
			?>
			<p><b>Akcije:</b><br>
			<?
			if (!$thesis['candidateApproved']) {
				?>
				<a href="<?=$linkprefix?>&amp;zavrsni=<?=$zavrsni?>&amp;akcija=odjava">Odjavi se sa ove teme</a><br>
				<?
			}
			?>
			<a href="<?=$linkprefix?>&amp;zavrsni=<?=$zavrsni?>&amp;akcija=zavrsni_stranica">Stranica završnog rada</a>
			</p>
			<?

		} else if (!$thesis['candidate']['id']) {
			?>
			<p><b>Akcije:</b><br>
			<a href="<?=$linkprefix?>&amp;zavrsni=<?=$zavrsni?>&amp;akcija=prijava">Prijavi se na ovu temu</a>
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
		$thesis = api_call("thesis/forStudent/$userid");
		if ($_api_http_code == "200") {
			$_REQUEST['zavrsni'] = $thesis['id'];
			require_once('common/zavrsniStrane.php');
			common_zavrsniStrane($thesis);
			return;
		}
		else if ($_api_http_code != "404") {
			niceerror("Neuspješna provjera da li je student odabrao temu");
			zamgerlog("spoofing zavrsnog rada $zavrsni", 3);
			api_report_bug($thesis, []);
			return;
		}
	
		?>
		<h2>Lista tema završnih radova</h2>
		<?
		
		$theses = api_call("thesis/course/$predmet/$ag")["results"];
		if (count($theses) == 0) {
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
	
		foreach($theses as $thesis) {
			$id_zavrsni = $thesis['id'];
			
			$naslov_teme = "<a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=detalji\">" . $thesis['title'] . "</a>";
			$mentori = "";
			foreach($thesis['menthors'] as $menthor) {
				if ($mentori != "") $mentori .= "<br>\n";
				$mentori .= tituliraj_api($menthor, false);
			}
			
			$rbr++;
			if ($thesis['candidate']['id'] == $userid) {
				$link = "<a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=odjava\">odjava</a> * <a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=zavrsnistranica\">stranica</a>";
			} else if ($thesis['candidate']['id'] > 0) {
				$link = "<font color='red'>zauzeta</font>";
			} else {
				$link = "<a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=prijava\">prijava</a> * <a href=\"$linkprefix&zavrsni=$id_zavrsni&akcija=detalji\">detalji</a>";
			}

			?>
			<tr>
				<td><?=$rbr?>.</td>
				<td><?=$naslov_teme?></td>
				<td><?=$mentori?></td>
				<td><?=$link?></td> 
			</tr>
			<?
		} // while ($r901...
	} // if (!isset($akcija)
} //function
?>
