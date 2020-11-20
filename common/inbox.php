<?

// COMMON/INBOX + pregled poruka u sanducicu


function common_inbox() {
	
	global $userid, $_api_http_code, $person;
	
	require_once("lib/utility.php"); // linkuj_urlove
	
	// Pravimo neki okvir za sajt
	
	?>
	<center>
	<table width="80%" border="0"><tr><td>
	
	<h1>Lične poruke</h1>
	
	<?
	
	
	$pageSize = 20; // Size of page for inbox/outbox
	
	
	
	//////////////////////
	// Slanje poruke
	//////////////////////
	
	if (($_POST['akcija']=='send' || $_POST['akcija'] == "send_bug_report") && check_csrf_token()) {
	
		// Ko je primalac
		$receiverTxt = $_REQUEST['primalac'];
		$ref = intval($_REQUEST['ref']);
		$receiverTxt = preg_replace("/\(.*?\)/","",$receiverTxt);

		$receiver = api_call("person/byLogin", ["login" => $receiverTxt]);
		if ($_api_http_code != "200") {
			niceerror("Nepoznat primalac");
			return;
		}
		
		$text = $_REQUEST['tekst'];
		if ($_POST['akcija'] == "send_bug_report") {
			$text .= "\n\nŠta je radio: " . $_REQUEST['stasamradio'];
		}
		
		
		$message = array_to_object( [ "id" => 0, "type" => 2, "scope" => 7, "receiver" => $receiver['id'], "sender" => [ "id" => $userid ], "ref" => $ref, "subject" => $_REQUEST['naslov'], "text" => $text ] );
		$result = api_call("inbox", $message, "POST");
		if ($_api_http_code == "201") {
			nicemessage("Poruka uspješno poslana");
			zamgerlog("poslana poruka za u$receiverId",2);
			zamgerlog2("poslana poruka", intval($receiverId));
		} else {
			niceerror("Neuspješno slanje poruke: " . $result['message']);
			api_report_bug($result, $message);
		}
	}
	
	if ($_REQUEST['akcija']=='bugreport') {
		
		$text = "Šta: " . $_REQUEST['original_sta'] . "\n\nFile: " . $_REQUEST['file'] . "\n\nLine: " . $_REQUEST['line'] .
			"\n\nAPI request data: " . $_REQUEST['request_data'] . "\n\nAPI response code: " . $_REQUEST['code'] . "\n\nAPI message: " . $_REQUEST['message'] .
			"\n\nAPI response (JSON):" . $_REQUEST['server_json'];
		
		unset($_REQUEST['server_json']); unset($_REQUEST['request_data']);
		
		?>
		<a href="?sta=common/inbox">Nazad na inbox</a><br/>
		<h3>Prijava buga</h3>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="send_bug_report">
		<input type="hidden" name="primalac" value="vljubovic">
		<input type="hidden" name="naslov" value="PRIJAVA BUGA">
		<input type="hidden" name="tekst" value="<?=htmlentities($text)?>">
		
		<? nicemessage("Podaci o bugu uspješno prikupljeni!") ?>
		
		<p>Opišite šta ste radili u trenutku kada se bug desio (nije obavezno):</p>
		<textarea name="stasamradio" cols="60" rows="10"></textarea><br><br>

		<input type="submit" value="Prijavi bug">
		</form>
		<p><a onclick="daj_stablo('detalji')">Detaljnije informacije o bugu</a></p>
		
		<div id="detalji" style="display:none">
			<pre><?=htmlentities($text)?></pre>
		</div>
		
		<?
		return;
	}
	
	if ($_REQUEST['akcija']=='compose' || $_REQUEST['akcija']=='odgovor') {
		if ($_REQUEST['akcija']=='odgovor') {
			$messageId = intval($_REQUEST['poruka']);
			$message = api_call("inbox/$messageId", [ "resolve" => [ "Person" ]]);
	
			// Ko je poslao originalnu poruku (tj. kome odgovaramo)
			$receiverId = $message['sender']['id'];
			$receiver = $message['sender'];
			if ($receiverId == $userid) { // U slučaju odgovora na poslanu poruku, ponovo šaljemo poruku istoj osobi
				$receiverId = $message['receiver'];
				$receiver = api_call("person/$receiverId");
			}
			$receiverTxt = $receiver['login'] . " (" . $receiver['name'] . " " . $receiver['surname'] . ")";
			
			// Prepravka naslova i teksta
			$subject = $message['subject'];
			if (substr($subject,0,3) != "Re:") $subject = "Re: " . $subject;
			
			// Wrap message text at 80 characters
			$lineWidth = 80;
			$text = $message['text'];
			for ($i = $lineWidth; $i<strlen($text); $i += $lineWidth+1) {
				$k = $i-$lineWidth;
				while ($k<$i && $k!==false) {
					$oldk=$k;
					$k = strpos($text, " ",$k+1);
				}
				if ($oldk == $i - $lineWidth)
					$text = substr($text,0,$i)."\n".substr($text,$i);
				else
					$text = substr($text,0,$oldk)."\n".substr($text,$oldk+1);
			}
			$text = "> ".str_replace("\n","\n> ", $text);
			$text .= "\n\n";
			
		} else {
			// Omogucujemo da se naslov, tekst i primalac zadaju preko URLa
			if ($_REQUEST['naslov'])
				$subject = db_escape($_REQUEST['naslov']);
			else $subject="";
			if ($_REQUEST['tekst'])
				$text = db_escape($_REQUEST['tekst']);
			else $text="";
			if ($_REQUEST['primalac'])
				$receiverTxt = db_escape($_REQUEST['primalac']);
			else $receiverTxt="";
		}
		
		?>
		<a href="?sta=common/inbox">Nazad na inbox</a><br/>
		<h3>Slanje poruke</h3>
		<?=genform("POST")?>
		<?
		if ($_REQUEST['akcija']=='odgovor') {
			?>
			<input type="hidden" name="ref" value="<?=$messageId?>"><?
		}
		?>
		<input type="hidden" name="akcija" value="send">
		<script language="javascript">
		var tm=0;
		function startaj_timer(e) {
			sakrij_pretragu();
			if(e.keyCode!=13 && e.keyCode!=9) tm = setTimeout('pretraga_primalaca()',1000);
		}
		function pretraga_primalaca() {
			var ib=document.getElementById('primalac');
			var pg=document.getElementById('pretgraga');
			if (ib.value.length<3) return;
			//alert("Trazim: "+ib.value);
	
			// Nadji poziciju objekta - stari kod je bio obsolete i nije radio na Firefoxu
			var viewportOffset = ib.getBoundingClientRect();
			console.log(viewportOffset);
			pg.style.visibility = 'visible';
			pg.style.left = "" + viewportOffset.left + "px";
			pg.style.top= "" + (viewportOffset.top+ib.offsetHeight) + "px";
			console.log(pg.style);
	
			ajax_start(
				"ws/osoba",
				"GET",
				{ "akcija" : "pretraga", "upit" : ib.value },
				function(osobe) {
					var rp=document.getElementById('rezultati_pretrage');
					rp.innerHTML = "";
					found = false;
					for (i=0; i<osobe.length; i++) {
						osoba_tekst = osobe[i].logini[0] + " (" + osobe[i].ime + " " + osobe[i].prezime + ")";
						rp.innerHTML = rp.innerHTML+"<a href=\"javascript:postavi('"+osoba_tekst+"')\">"+osoba_tekst+"</a><br/>";
						found = true;
					}
					if (!found) rp.innerHTML = "<font color=\"#AAAAAA\">(Nema rezultata)</font><br/>";
				}
			);
		}
		function sakrij_pretragu() {
			var pg=document.getElementById('pretgraga');
			pg.style.visibility = 'hidden';
			if (tm!=0)
				clearTimeout(tm);
		}
		function postavi(prim) {
			var ib=document.getElementById('primalac');
			ib.value=prim;
			sakrij_pretragu();
		}
		function blur_dogadjaj(e) {
			setTimeout('sakrij_pretragu()',1000);
		}
		</script>
		<table border="0">
			<tr><td><b>Primalac:</b></td><td><input type="text" name="primalac" id="primalac" size="40" value="<?=$receiverTxt?>" autocomplete="off" onkeypress="startaj_timer(event);" onblur="blur_dogadjaj(event);"></td></tr>
			<tr><td colspan="2"><input type="radio" name="metoda" value="1" DISABLED> Pošalji e-mail    <input type="radio" name="metoda" value="2" CHECKED> Pošalji Zamger poruku<br/>&nbsp;<br/></td></tr>
			<tr><td><b>Naslov:</b></td><td><input type="text" name="naslov" size="40" value="<?=$subject?>"></td></tr>
		</table>
	
		<!-- Rezultati pretrage primaoca -->
		<div id="pretgraga" style="position:absolute;visibility:hidden">
			<table border="0" bgcolor="#FFFFEE"  style="border:1px;border-color:silver;border-style:solid;">
				<tr><td>
					<div id="rezultati_pretrage"></div>
				</td></tr>
			</table>
		</div>
	
		<br/>
		Tekst poruke:<br/>
		<textarea name="tekst" rows="10" cols="81"><?=$text?></textarea>
		<br/>&nbsp;<br/>
		<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
		</form>
		<?
		ajax_box();
		return;
	}
	
	
	
	?>
	<p><a href="?sta=common/inbox&akcija=compose">Pošalji novu poruku</a> * <?
		if ($_REQUEST['mode']=="outbox") {
	?><a href="?sta=common/inbox">Vaše sanduče</a><?
		} else {
	?><a href="?sta=common/inbox&mode=outbox">Vaše poslane poruke</a><?
		}
	?></p>
	<?
	
	
	
	//////////////////////
	// Čitanje poruke
	//////////////////////
	
	
	$mjeseci = array("", "januar", "februar", "mart", "april", "maj", "juni", "juli", "avgust", "septembar", "oktobar", "novembar", "decembar");
	
	$dani = array("Nedjelja", "Ponedjeljak", "Utorak", "Srijeda", "Četvrtak", "Petak", "Subota");
	
	$messageId = intval($_REQUEST['poruka']);
	if ($messageId>0) {
		// Dobavljamo podatke o poruci
		$message = api_call("inbox/$messageId", [ "resolve" => [ "Person" ]]);
		if ($_api_http_code != "200") {
			niceerror("Neuspješan pristup poruci: " . $message['message']);
			return;
		}
		// Access control is now done on backend
	
		$senderTxt = $message['sender']['name'] . " " . $message['sender']['surname'];
		$receiverId = $message['receiver'];
	
		// Primalac
		if ($message['scope'] == 0)
			$receiverTxt = "Svi korisnici Zamgera";
		else if ($message['scope'] == 1)
			$receiverTxt = "Svi studenti";
		else if ($message['scope'] == 2)
			$receiverTxt = "Svi nastavnici i saradnici";
		else if ($message['scope'] == 3) {
			$programme = api_call("programme/$receiverId");
			if ($_api_http_code == "200") {
				$receiverTxt = "Svi studenti na studiju: " . $programme['name'];
			} else {
				$receiverTxt = "Nepoznato!?";
			}
		}
		else if ($message['scope'] == 4) {
			$receiverTxt = "Svi studenti na $receiverId studijskoj godini";
		}
		else if ($message['scope'] == 5) {
			$course = api_call("course/$receiverId");
			if ($_api_http_code == "200") {
				$receiverTxt = "Svi studenti na predmetu: " . $course['courseName'];
			} else {
				$receiverTxt = "Nepoznato!?";
			}
		}
		else if ($message['scope'] == 6) {
			$group = api_call("group/$receiverId");
			if ($_api_http_code == "200") {
				$course = api_call("course/" . $group['CourseUnit']['id']);
				$receiverTxt = "Svi studenti u grupi " . $group['name'] . " (" . $course['courseName'] . ")";
			} else {
				$receiverTxt = "Nepoznato!?";
			}
		}
		else if ($message['scope'] == 7) {
			if ($receiverId == $userid) {
				$receiver = $person;
			} else {
				$receiver = $message['receiverPerson'];
			}
			$receiverTxt = $receiver['name'] . " " . $receiver['surname'];
		}
		else if ($message['scope'] == 8) {
			$studij = intval($receiverId / 10);
			if ($studij == -1) {
				$godina = -($receiverId+10);
				$receiverTxt = "Svi studenti na: Prvom ciklusu studija, $godina. godina";
			} else if ($studij == -2) {
				$godina = -($receiverId+20);
				$receiverTxt = "Svi studenti na: Drugom ciklusu studija, $godina. godina";
			} else {
				$godina = $receiverId%10;
				$programme = api_call("programme/$studij");
				if ($_api_http_code == "200") {
					$receiverTxt = "Svi studenti na: " . $programme['name'] . ", $godina. godina";
				} else {
					$receiverTxt = "Nepoznato!?";
				}
			}
		}
		else {
			$receiverTxt = "Nepoznato!?";
			zamgerlog("poruka $messageId ima nepoznat opseg $opseg",3);
			zamgerlog2("poruka ima nepoznat opseg", $messageId, $opseg);
		}
	
		// Fini datum
		$vr = db_timestamp($message['time']);
		if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i> - ";
		else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i> - ";
		$vrijeme .= $dani[date("w",$vr)].date(", j. ",$vr).$mjeseci[date("n",$vr)].date(" Y. H:i",$vr);
	
		// Naslov
		if ($message['type'] == 1) {
			$subject = "O B A V J E Š T E N J E";
			$text = $message['subject'] . "\n\n";
		} else {
			$subject = $message['subject'];
			if (!preg_match("/\S/",$subject)) $subject = "[Bez naslova]";
			$text = "";
		}
	
		?><h3>Prikaz poruke</h3>
		<table cellspacing="0" cellpadding="0" border="0"  style="border:1px;border-color:silver;border-style:solid;"><tr><td bgcolor="#f2f2f2">
			<table border="0">
				<tr><td><b>Vrijeme slanja:</b></td><td><?=$vrijeme?></td></tr>
				<tr><td><b>Pošiljalac:</b></td><td><?=$senderTxt?></td></tr>
				<tr><td><b>Primalac:</b></td><td><?=$receiverTxt?></td></tr>
				<tr><td><b>Naslov:</b></td><td><?=$subject?> (<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$messageId?>">odgovori</a>)</td></tr>
			</table>
		</td></tr><tr><td>
			<br/>
			<table border="0" cellpadding="5"><tr><td>
			<?
			$text .= $message['text']; // Dodajemo na eventualni naslov obavještenja
			if (starts_with($text, "'''")) {
				$text = "<pre>" . substr($text, 3) . "</pre>";
			} else {
				$text = linkuj_urlove($text);
				$text = str_replace("\n", "<br/>\n", $text);
			}
	
			print $text;
			?>
			</td><tr></table>
		</td></tr></table>
		<br/><br/>
		<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$messageId?>">Odgovorite na poruku</a>
		<br/><hr><br/><?
	}
	
	
	
	//////////////////////
	// OUTBOX
	//////////////////////
	
	if ($_REQUEST['mode']=="outbox") {
	
		?>
		<h3>Poslane poruke:</h3>
		<?
		
		$stranica = intval($_REQUEST['stranica']);
		if ($stranica==0) $stranica = 1;
		$start = ($stranica - 1) * $pageSize;
		
		$count = api_call("inbox/count");
		if ($count['countOutbox'] == 0) {
			?>
			<ul><li>Nemate nijednu poruku.</li></ul>
			<?
		}
		
		if ($count['countOutbox'] > $pageSize) {
			$totalPages = ($count['countOutbox'] - 1) / $pageSize + 1;
			print "<p>Stranica: ";
			for ($i=1; $i<=$totalPages; $i++) {
				if ($stranica==$i)
					print "$i ";
				else
					print "<a href=\"?sta=common/inbox&mode=outbox&stranica=$i\">$i</a> ";
			}
			print "</p>\n";
		}
		
		?>
		<table border="0" width="100%" style="border:1px;border-color:silver;border-style:solid;">
			<thead>
			<tr bgcolor="#cccccc"><td width="15%"><b>Datum</b></td><td width="15%"><b>Primalac</b></td><td width="70%"><b>Naslov</b></td></tr>
			</thead>
			<tbody>
			<?
		
		$messages = api_call("inbox/outbox", [ "resolve" => [ "Person" ], "messages" => $pageSize, "start" => $start])["results"];
		foreach($messages as $message) {
			$id = $message['id'];
	
			$subject = $message['subject'];
			if (strlen($subject)>60) $subject = substr($subject,0,55)."...";
			if (!preg_match("/\S/",$subject)) $subject = "[Bez naslova]";
		
			// Primalac
			$receiverTxt = $message['receiverPerson']['name'] . " " . $message['receiverPerson']['surname'];
		
			// Fino vrijeme
			$vr = db_timestamp($message['time']);
			$vrijeme="";
			if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>, ";
			else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>, ";
			else $vrijeme .= date("j. ",$vr).$mjeseci[date("n",$vr)].", ";
			$vrijeme .= date("H:i",$vr);
		
			if ($_REQUEST['poruka'] == $id) $bgcolor="#EEEECC"; else $bgcolor="#FFFFFF";
		
			?>
			<tr bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#EEEEEE'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<td><?=$vrijeme?></td>
				<td><?=$receiverTxt?></td>
				<td><a href="?sta=common/inbox&poruka=<?=$id?>&mode=outbox&stranica=<?=$stranica?>"><?=$subject?></a></td>
			</tr>
			<?
		}
		
		?>
			</tbody></table>
		<?
		
		
	//////////////////////
	// INBOX
	//////////////////////
	
	} else {
		
		?>
		<h3>Poruke u vašem sandučetu:</h3>
		<?
		
		$stranica = intval($_REQUEST['stranica']);
		if ($stranica==0) $stranica = 1;
		$start = ($stranica - 1) * $pageSize;
		
		$count = api_call("inbox/count");
		
		if ($count['count'] == 0) {
			?>
			<ul><li>Nemate nijednu poruku.</li></ul>
			<?
		}
		
		if ($count['count'] > $pageSize) {
			$totalPages = ($count['count'] - 1) / $pageSize + 1;
			print "<p>Stranica: ";
			for ($i=1; $i<=$totalPages; $i++) {
				if ($stranica==$i)
					print "$i ";
				else
					print "<a href=\"?sta=common/inbox&stranica=$i\">$i</a> ";
			}
			print "</p>\n";
		}
		?>
		<table border="0" width="100%" style="border:1px;border-color:silver;border-style:solid;">
			<thead>
			<tr bgcolor="#cccccc"><td width="15%"><b>Datum</b></td><td width="15%"><b>Autor</b></td><td width="70%"><b>Naslov</b></td></tr>
			</thead>
			<tbody>
			<?
		
		$messages = api_call("inbox", [ "resolve" => [ "Person" ], "messages" => $pageSize, "start" => $start])["results"];
		foreach($messages as $message) {
			$id = $message['id'];
			
			$subject = $message['subject'];
			if (strlen($subject)>60) $subject = substr($subject,0,55)."...";
			if (!preg_match("/\S/",$subject)) $subject = "[Bez naslova]";
		
			// Posiljalac
			$senderTxt = $message['sender']['name'] . " " . $message['sender']['surname'];
		
			// Fino vrijeme
			$vr = db_timestamp($message['time']);
			$vrijeme="";
			if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>, ";
			else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>, ";
			else $vrijeme .= date("j. ",$vr).$mjeseci[date("n",$vr)].", ";
			$vrijeme .= date("H:i",$vr);
		
			if ($_REQUEST['poruka'] == $id) $bgcolor="#EEEECC"; else $bgcolor="#FFFFFF";
			
			if ($message['unread']) { $b = "<b>"; $bb = "</b>"; } else { $b = $bb = ""; }
			?>
			<tr bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#EEEEEE'" onmouseout="this.bgColor='<?=$bgcolor?>'">
				<td><?=$b.$vrijeme.$bb?></td>
				<td><?=$b.$senderTxt.$bb?></td>
				<td><a href="?sta=common/inbox&poruka=<?=$id?>&stranica=<?=$stranica?>"><?=$b.$subject.$bb?></a></td>
			</tr>
			<?
		}
		
		?>
			</tbody></table>
		<?
	}
	
	?>
	</td></tr></tbody></table></center>
	<?


}


?>
