<?

// NASTAVNIK/OBAVJESTENJA - slanje obavjestenja studentima



function nastavnik_obavjestenja() {
	
	global $userid, $_api_http_code, $person;
	
	
	
	// Parametri
	$predmet = int_param('predmet');
	$ag = int_param('ag');
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/predmet privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Obavještenja za studente</h3></p>
	
	<script language="JavaScript">
	function upozorenje(obavjest) {
		var a = confirm("Želite li obrisati ovo obavještenje? Ako ste odabrali opciju Slanje maila, ne možete poništiti njen efekat!");
		if (a) {
			document.brisanjeobavjestenja.obavjestenje.value=obavjest;
			document.brisanjeobavjestenja.submit();
		}
	}
	</script>
	<?=genform("POST", "brisanjeobavjestenja")?>
	<input type="hidden" name="akcija" value="obrisi_obavjestenje">
	<input type="hidden" name="obavjestenje" value=""></form>
	
	<?
	
	// Parametri
	
	$naslov = $tekst = "";
	
	$citava = int_param('citava');
	$izmijeni = int_param('izmijeni');
	
	
	// Brisanje obavjestenja
	
	if (param('akcija')=="obrisi_obavjestenje" && check_csrf_token()) {
		$obavjestenje = intval($_POST['obavjestenje']);
		api_call("inbox/announcements/$obavjestenje", [], "DELETE");
		
		if ($_api_http_code != "204") {
			niceerror("Neuspješno brisanje obavještenja $obavjestenje: kod $_api_http_code");
		} else {
			zamgerlog("obrisano obavjestenje (id $obavjestenje )", 2);
			zamgerlog2("obrisana poruka", $obavjestenje);
		}
	}
	
	
	
	// Novo obavještenje / izmjena obavještenja
	
	if (param('akcija')=='novo' && check_csrf_token()) {
		if (!$course['AcademicYear']['isCurrent']) {
			niceerror("Slanje obavještenja je dozvoljeno samo u aktuelnoj akademskoj godini");
			return;
		}
		
		$naslov = $_REQUEST['naslov'];
		$tekst = $_REQUEST['tekst'];
		$primalac = intval($_REQUEST['primalac']);
		$io = intval($_REQUEST['izmjena_obavjestenja']);
		
		// Ako je postavljen primalac, to je id grupe
		if ($primalac > 0)
			$ann = array_to_object( [ "id" => $io, "scope" => 6, "sender" => $userid, "receiver" => $primalac, "subject" => $naslov, "text" => $tekst ]);
		else
			$ann = array_to_object( [ "id" => $io, "scope" => 5, "sender" => $userid, "receiver" => $predmet, "subject" => $naslov, "text" => $tekst]);
	
		// $io je id obavještenja ako se mijenja ili 0 ako je novo
		if ($io > 0)
			$result = api_call("inbox/announcements/$io", $ann, "PUT");
		else
			$result = api_call("inbox/announcements", $ann, "POST");
		
		if ($_api_http_code != "201") {
			if ($result['message'] == "Announcement is too short")
				niceerror("Obavještenje je prekratko");
			else
				niceerror("Neuspješno postavljanje obavještenja: " . $result['message']);
		} else if ($io > 0) {
			zamgerlog("izmjena obavjestenja (id $io)",2);
			zamgerlog2("poruka izmijenjena", $io);
		} else {
			zamgerlog("novo obavjestenje (predmet pp$predmet)",2);
			zamgerlog2("nova poruka poslana", $io);
			//print_r($result);
			
			// Slanje mailova studentima
			if ($_REQUEST['email']) {
				if ($primalac > 0)
					$group = api_call("group/$primalac");
				else
					$group = api_call("group/course/$predmet");
				
				
				// Podaci za konverziju naših slova
				$nasaslova = array("č", "ć", "đ", "š", "ž", "Č", "Ć", "Đ", "Š", "Ž");
				$beznasihslova = array("c", "c", "d", "s", "z", "C", "C", "D", "S", "Z");
				
				// Subject email poruke
				$subject = "OBAVJEŠTENJE: $predmet_naziv";
				if ($primalac>0) {
					$subject .= " (".$group['name'].")";
				}
				
				$subject = iconv("UTF-8", "ISO-8859-2", $subject); // neki mail klijenti ne znaju prikazati utf-8 u subjektu
				$preferences = array(
					"input-charset" => "ISO-8859-2",
					"output-charset" => "ISO-8859-2",
					"line-length" => 76,
					"line-break-chars" => "\n"
				);
				$preferences["scheme"] = "Q"; // quoted-printable
				$subject = iconv_mime_encode("", $subject, $preferences);
				
				// Vraćamo naslov i tekst obavještenja koji su ranije escapovani
				// mail() nema poznatih eksploita po tom pitanju
				$naslov = $_REQUEST['naslov'];
				$tekst = $_REQUEST['tekst'];
				
				$mail_body = "\n=== OBAVJEŠTENJE ZA STUDENTE ===\n\nNastavnik ili saradnik na predmetu $predmet_naziv poslao vam je sljedeće obavještenje:\n\n$naslov\n\n$tekst";
				
				// Podaci za from polje
				$from = $person['name']." ".$person['surname'];
				$from = str_replace($nasaslova, $beznasihslova, $from);
				
				$from .= " <".$person['email'].">";
				
				$add_header = "From: $from\r\nContent-Type: text/plain; charset=utf-8\r\n";
				
				
				foreach($group['members'] as $member) {
					$student_id = $member['student']['id'];
					$student_ime_prezime = str_replace($nasaslova, $beznasihslova, $member['student']['name'] . "  " . $member['student']['surname']);
					
					// TODO više adresa
					$mail_to = "$student_ime_prezime <" . $member['student']['email'] . ">";
					$mail_cc = "";
					// Prvu adresu stavljamo u To: a sve ostale u Cc: kako bi mail server otkrio eventualne aliase
					
					if ($member['student']['email']) { // Da li student ima ijednu adresu?
						mail($mail_to, $subject, $mail_body, "$add_header");
						nicemessage ("Mail poslan za $student_ime_prezime &lt;$mail_to&gt;");
					}
				}
			}
		}
	
		$naslov=$tekst="";
	}
	
	
	// Postojeća obavjestenja
	$announcements = api_call("inbox/course/$predmet/$ag")['results'];
	if (count($announcements) > 0) {
		?>
	<p>Do sada unesena obavještenja:</p>
	<ul>
		<?
	} else {
		?>
	<p>Do sada niste unijeli nijedno obavještenje.</p>
		<?
	}
	
	foreach($announcements as $ann) {
		$tekst_poruke = nl2br($ann['text']);
		if (strlen($tekst_poruke)>0) {
			if ($citava == $ann['id']) // $citava is parameter
				$tekst_poruke = "<br/><br/>" . $tekst_poruke;
			else
				$tekst_poruke = " (<a href=\"?sta=nastavnik/obavjestenja&predmet=$predmet&ag=$ag&citava=" . $ann['id'] . "\">Dalje...</a>)";
		}
		
		if ($izmijeni == $ann['id']) {
			$naslov = $ann['subject'];
			$tekst = $ann['text'];
			if ($ann['scope'] == 5)
				$labgrupa = 0;
			else
				$labgrupa = $ann['receiver'];
		}
		?>
		<li>
			<b>(<?=date("d.m.Y", db_timestamp($ann['time']))?>)</b>
			<?=$ann['subject']?>
			<?=$tekst_poruke?>
			<br/>
			<a href="?sta=nastavnik/obavjestenja&predmet=<?=$predmet?>&ag=<?=$ag?>&izmijeni=<?=$ann['id']?>">[Izmijeni]</a>
			<a href="javascript:onclick=upozorenje('<?=$ann['id']?>')">[Obriši]</a>
		</li>
		<?
	}
	if (count($announcements) > 0) {
		?>
	</ul>
		<?
	}
	
	
	// Formular za novo obavještenje
	
	?>
	<hr>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="novo">
	<? if ($izmijeni>0) { ?>
	<input type="hidden" name="izmjena_obavjestenja" value="<?=$izmijeni?>">
	<p><b>Izmjena postojećeg obavještenja</b></p>
	<? } else {
	?>
	<input type="hidden" name="izmjena_obavjestenja" value="0">
	<p><b>Unos novog obavještenja</b></p>
	<? } ?>
	<p>Obavještenje za: <select name="primalac" class="default"><option value="0">Sve studente</option>
	<?
	
	$groups = api_call("group/course/$predmet", ["year" => $ag] )["results"];
	foreach($groups as $group) {
		if ($group['id'] == $labgrupa) $sel="SELECTED"; else $sel="";
		?>
		<option value="<?=$group['id']?>" <?=$sel?>><?=$group['name']?></option>
		<?
	}
	?>
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="email" value="1"> Slanje e-maila
	</p>
	<p>Kraći tekst (2-3 rečenice):<br/>
	<textarea  rows="5" cols="80" name="naslov"><?=$naslov?></textarea>
	<br/><br/>
	Detaljan tekst (nije obavezan):<br/>
	<textarea  rows="20" cols="80" name="tekst"><?=$tekst?></textarea>
	<br/><br/>
	<input type="submit" value=" Pošalji ">  <input type="reset" value=" Poništi ">
	</p></form>
	
	<?


}

?>
