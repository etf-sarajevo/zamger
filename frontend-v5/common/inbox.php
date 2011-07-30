<?

// COMMON/INBOX + pregled poruka u sanducicu


function common_inbox() {

global $userid,$user_student, $user_nastavnik;



require_once("Config.php");

require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."core/Util.php");
require_once(Config::$backend_path."core/Programme.php");
require_once(Config::$backend_path."core/CourseUnit.php");

// Pošto je ova skripta ustvari dio common/pm modula, ovo ispod ne treba biti opcionalno
require_once(Config::$backend_path."common/pm/Message.php");



// LEGENDA tabele poruke
// Tip:
//    1 - obavjestenja
//    2 - lične poruke
// Opseg:
//    0 - svi korisnici Zamgera
//    1 - svi studenti
//    2 - svi nastavnici
//    3 - svi studenti na studiju (primalac - id studija)
//    4 - svi studenti na godini (primalac - id akademske godine)
//    5 - svi studenti na predmetu (primalac - id predmeta)
//    6 - svi studenti na labgrupi (primalac - id labgrupe)
//    7 - korisnik (primalac - user id)




// Pravimo neki okvir za sajt

?>
<center>
<table width="80%" border="0"><tr><td>

<h1>Lične poruke</h1>

<?



//////////////////////
// Slanje poruke
//////////////////////

if ($_POST['akcija']=='send' && check_csrf_token()) {

	// Ko je primalac
	$primalac = my_escape($_REQUEST['primalac']);
	$primalac = preg_replace("/\(.*?\)/","",$primalac); // Eliminišemo ime i prezime, ostavljamo samo login

	try {
		$o = Person::fromLogin($primalac);
	} catch(Exception $e) {
		niceerror("Nepoznat primalac");
		zamgerlog("nepoznat primalac poruke $primalac", 3);
		return;
	}

	$m = new Message;
	$m->toId = $o->id;
	$m->fromId = $userid;
	$m->ref = intval($_REQUEST['ref']);
	$m->subject = my_escape($_REQUEST['naslov']);
	$m->text = my_escape($_REQUEST['tekst']);

	$m->send();

	nicemessage("Poruka uspješno poslana");
	zamgerlog("poslana poruka ".$m->id." za u".$o->id,2);
}


// Sastavljanje poruke
if ($_REQUEST['akcija']=='compose' || $_REQUEST['akcija']=='odgovor') {
	// Odgovor na poruku
	if ($_REQUEST['akcija']=='odgovor') {
		$poruka = intval($_REQUEST['poruka']);
		$porukaO = Message::fromId($poruka);

		$o = Person::fromId($porukaO->fromId);
		$primalac = $o->login." (".$o->name." ".$o->surname.")";

		// Prepravka naslova i teksta
		$naslov = $porukaO->subject;
		if (substr($naslov,0,3) != "Re:") $naslov = "Re: ".$naslov;

		$tekst = $porukaO->text;

		// Siječemo tekst u redove dužine 80 znakova,
		for ($i=80;$i<strlen($tekst);$i+=81) {
			$k=$i-80;
			while ($k<$i && $k!==false) {
				$oldk=$k;
				$k = strpos($tekst, " ",$k+1);
			}
			if ($oldk==$i-80)
				$tekst = substr($tekst,0,$i)."\n".substr($tekst,$i);
			else
				$tekst = substr($tekst,0,$oldk)."\n".substr($tekst,$oldk+1);
		}

		// Dodajemo znak > na početak svakog reda i dva nova reda na kraj
		$tekst = "> ".str_replace("\n","\n> ", $tekst);
		$tekst .= "\n\n";

	} else {
		// Omogucujemo da se naslov, tekst i primalac zadaju preko URLa
		if ($_REQUEST['naslov']) 
			$naslov = my_escape($_REQUEST['naslov']);
		else $naslov="";
		if ($_REQUEST['tekst']) 
			$tekst = my_escape($_REQUEST['tekst']);
		else $tekst="";
		if ($_REQUEST['primalac']) 
			$primalac = my_escape($_REQUEST['primalac']);
		else $primalac="";
	}
		
	?>
	<a href="?sta=common/inbox">Nazad na inbox</a><br/>
	<h3>Slanje poruke</h3>
	<?=genform("POST")?>
	<?
	if ($_REQUEST['akcija']=='odgovor') {
		?>
		<input type="hidden" name="ref" value="<?=$poruka?>"><?
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

		// Nadji poziciju objekta
		var curleft = curtop = 0;
		var obj=ib;
		if (obj.offsetParent) {
			do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
		}

		pg.style.visibility = 'visible';
		pg.style.left=curleft;
		pg.style.top=curtop+ib.offsetHeight;

		ajah_start("index.php?c=N&sta=common/ajah&akcija=pretraga&ime="+ib.value, "", "napuni_rezultate()");
	}
	function napuni_rezultate() {
		var rp=document.getElementById('rezultati_pretrage');
		var tekst = frames['zamger_ajah'].document.body.innerHTML;
		var oldpozicija=0;
		rp.innerHTML = "";
		do {
			var pozicija = tekst.indexOf('\n',oldpozicija);
			var tmptekst = tekst.substr(oldpozicija,pozicija-oldpozicija);
			if (tmptekst.length<2) { oldpozicija=pozicija+1; continue; }
			if (tmptekst == "OK") break;
			if (tmptekst == "Nema rezultata") {
				rp.innerHTML = rp.innerHTML + "<font color=\"#AAAAAA\">(Nema rezultata)</font><br/>";
			} else {
				rp.innerHTML = rp.innerHTML+"<a href=\"javascript:postavi('"+tmptekst+"')\">"+tmptekst+"</a><br/>";
			}
			oldpozicija=pozicija+1;
		} while (pozicija>=0);
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
		setTimeout('sakrij_pretragu()',100);
	}
	</script>
	<table border="0">
		<tr><td><b>Primalac:</b></td><td><input type="text" name="primalac" id="primalac" size="40" value="<?=$primalac?>" autocomplete="off" onkeypress="startaj_timer(event);" onblur="blur_dogadjaj(event);"></td></tr>
		<tr><td colspan="2"><input type="radio" name="metoda" value="1" DISABLED> Pošalji e-mail    <input type="radio" name="metoda" value="2" CHECKED> Pošalji Zamger poruku<br/>&nbsp;<br/></td></tr>
		<tr><td><b>Naslov:</b></td><td><input type="text" name="naslov" size="40" value="<?=$naslov?>"></td></tr>
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
	<textarea name="tekst" rows="10" cols="81"><?=$tekst?></textarea>
	<br/>&nbsp;<br/>
	<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
	</form>
	<?
	print ajah_box();
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

$poruka = intval($_REQUEST['poruka']);
if ($poruka>0) {
	// Dobavljamo podatke o poruci
	$porukaO = Message::fromId($poruka);
	if (!$porukaO->forPerson($userid)) {
		niceerror("Nemate pravo pristupa ovoj poruci!");
		zamgerlog("pokusao pristupiti poruci $poruka",3);
		return;
	}

	// Posiljalac
	try {
		$osoba = Person::fromId($porukaO->fromId);
		$posiljalac = $osoba->name." ".$osoba->surname;
	} catch(Exception $e) {
		$posiljalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznatog posiljaoca $pos_id",3);
	}
	
	// Primalac
	switch ($porukaO->scope) {
		case 0:
			$primalac="Svi korisnici Zamgera";
			break;
		case 1:
			$primalac="Svi studenti";
			break;
		case 2:
			$primalac="Svi nastavnici i saradnici";
			break;
		case 3:
			try {
				$p = Programme::fromId($porukaO->toId);
				$primalac = "Svi studenti na: ".$p->name;
			} catch(Exception $e) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca ".$porukaO->toId." (opseg: studij)",3);
			}
			break;

		case 4:
			$primalac = "Svi studenti na ".$porukaO->toId.". godini studija";
			break;

		case 5:
			try {
				$p = CourseUnit::fromId($porukaO->toId);
				$primalac = "Svi studenti na predmetu: ".$p->name;
			} catch(Exception $e) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca ".$porukaO->toId." (opseg: predmet)",3);
			}
			break;

		case 5:
			try {
				$p = CourseUnit::fromId($porukaO->toId);
				$primalac = "Svi studenti na predmetu: ".$p->name;
			} catch(Exception $e) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca ".$porukaO->toId." (opseg: predmet)",3);
			}
			break;

		case 6:
			// Ako je naveden ovaj opseg znači da se koristi sljedeći modul
			require_once(Config::$backend_path."lms/attendance/Group.php");
			try {
				$l = Group::fromId($porukaO->toId);
				$p = CourseUnit::fromId($l->courseUnitId);
				$primalac = "Svi studenti u grupi: ".$l->name." (".$p->name.")";
			} catch(Exception $e) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca ".$porukaO->toId." (opseg: grupa)",3);
			}
			break;

		case 7:
			try {
				$p = Person::fromId($porukaO->toId);
				$primalac = $p->name." ".$p->surname;
			} catch(Exception $e) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca ".$porukaO->toId." (opseg: korisnik)",3);
			}
			break;

		default:
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznat opseg ".$porukaO->scope,3);
	}

	// Fini datum
	$vrijeme = "";
	$vr = $porukaO->time;
	if (date("d.m.Y",$vr) == date("d.m.Y")) 
		$vrijeme = "<i>danas</i> - ";
	else if (date("d.m.Y",$vr+3600*24) == date("d.m.Y")) 
		$vrijeme = "<i>juče</i> - ";
	$vrijeme .= $dani[date("w",$vr)].date(", j. ",$vr).$mjeseci[date("n",$vr)].date(" Y. H:i",$vr);

	// Naslov
	$tip = $porukaO->type;
	if ($tip == 1) {
		$naslov = "O B A V J E Š T E N J E";
		$tekst = $porukaO->subject . "\n\n";
	} else {
		$naslov = $porukaO->subject;
		if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
		$tekst = "";
	}

	?><h3>Prikaz poruke</h3>
	<table cellspacing="0" cellpadding="0" border="0"  style="border:1px;border-color:silver;border-style:solid;"><tr><td bgcolor="#f2f2f2">
		<table border="0">
			<tr><td><b>Vrijeme slanja:</b></td><td><?=$vrijeme?></td></tr>
			<tr><td><b>Pošiljalac:</b></td><td><?=$posiljalac?></td></tr>
			<tr><td><b>Primalac:</b></td><td><?=$primalac?></td></tr>
			<tr><td><b>Naslov:</b></td><td><?=$naslov?> (<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$poruka?>">odgovori</a>)</td></tr>
		</table>
	</td></tr><tr><td>
		<br/>
		<table border="0" cellpadding="5"><tr><td>
		<?
		$tekst .= $porukaO->text; // Dodajemo na eventualni naslov obavještenja

		// Parsiranje linkova
		$i=0;
		while (strpos($tekst,"http://",$i)!==false || strpos($tekst,"https://",$i)!==false) {
			$j = strpos($tekst,"http://",$i);
			if ($j==false) $j = strpos($tekst,"https://",$i);
			$k = strpos($tekst," ",$j);
			$k2 = strpos($tekst,"\n",$j);
			if ($k2<$k && $k2!=0) $k=$k2;
			if ($k==0) $k=$k2;
			if ($k==0) { $k=strlen($tekst);}

			do {
				$k--;
				$a = substr($tekst,$k,1);
			} while ($a=="."||$a=="," || $a==")" || $a=="!");
			$k++;
			if ($k-$j<9) { $i=$j+1; continue; }
			$url = substr($tekst,$j,$k-$j);
			$tekst = substr($tekst,0,$j). "<a href=\"$url\" target=\"_blank\">$url</a>". substr($tekst,$k);
			$i = $j+strlen($url)+28;
		}

		// Zamjenjujemo nove redove znakom <br>
		$tekst =  str_replace("\n","<br />\n",$tekst);

		print $tekst;
		?>
		</td><tr></table>
	</td></tr></table>
	<br/><br/>
	<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$poruka?>">Odgovorite na poruku</a>
	<br/><hr><br/><?
}



//////////////////////
// INBOX i OUTBOX
//////////////////////

$velstranice = 20; // Broj poruka po stranici

$stranica=intval($_REQUEST['stranica']);
if ($stranica==0) $stranica=1;

if ($_REQUEST['mode']=="outbox") {
	print "<h3>Poslane poruke:</h3>\n";
	$poruke = Message::getOutboxForPerson($userid);
	$outboxUrl = "&mode=outbox";
	$naslovKolone = "Primalac";
} else {
	print "<h3>Poruke u vašem sandučetu:</h3>\n";
	$poruke = Message::getLatestForPerson($userid, 0, true /* isStudent FIXME */);
	$outboxUrl = "";
	$naslovKolone = "Autor";
}

// Spisak stranica
if (count($poruke) > $velstranice) {
	$broj_stranica = (count($poruke)-1) / $velstranice + 1;
	print "<p>Stranica: \n";
	for ($i=1; $i<=$broj_stranica; $i++) {
		if ($stranica==$i)
			print "$i \n";
		else
			print "<a href=\"?sta=common/inbox&stranica=$i$outboxUrl\">$i</a> \n";
	}
	print "</p>\n";
}

if (count($poruke) == 0) {
	print "<li>Nemate nijednu poruku.</li>\n";
	return;
}

	?>
	<table border="0" width="100%" style="border:1px;border-color:silver;border-style:solid;">
		<thead>
		<tr bgcolor="#cccccc"><td width="15%"><b>Datum</b></td><td width="15%"><b><?=$naslovKolone?></b></td><td width="70%"><b>Naslov</b></td></tr>
		</thead>
		<tbody>
	<?

$count=0;
foreach ($poruke as $p) {
	// Skraćujemo naslov
	$naslov = Util::ellipsize($p->subject, 60, 10);
	//$naslov = $p->subject;
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Posiljalac
	try {
		if ($_REQUEST['mode']=="outbox")
			$osoba = Person::fromId($p->toId);
		else
			$osoba = Person::fromId($p->fromId);
		$posiljalac = $osoba->name." ".$osoba->surname;
	} catch(Exception $e) {
		$posiljalac = "Nepoznato! Prijavite grešku";
	}

	// Fino vrijeme
	$vr = $p->time;
	$vrijeme="";
	if ( date("d.m.Y", $vr) == date("d.m.Y") )
		$vrijeme = "<i>danas</i>, ";
	else if ( date("d.m.Y", $vr+3600*24) == date("d.m.Y") ) 
		$vrijeme = "<i>juče</i>, ";
	else {
		$vrijeme .= date("j. ",$vr) . $mjeseci[date("n",$vr)];
		if ( date("Y", $vr) != date("Y") ) $vrijeme .= date(" Y.", $vr);
		$vrijeme .=  ", ";
	}
	$vrijeme .= date("H:i",$vr);

	if ($_REQUEST['poruka'] == $p->id) $bgcolor="#EEEECC"; else $bgcolor="#FFFFFF";

	//$count++;
	$count++;
	if ($count>($stranica-1)*$velstranice && $count<=$stranica*$velstranice) {
		?>
		<tr bgcolor="<?=$bgcolor?>" onmouseover="this.bgColor='#EEEEEE'" onmouseout="this.bgColor='<?=$bgcolor?>'">
			<td><?=$vrijeme?></td>
			<td><?=$posiljalac?></td>
			<td><a href="?sta=common/inbox&poruka=<?=$p->id?>&stranica=<?=$stranica?><?=$outboxUrl?>"><?=$naslov?></a></td>
		</tr>
		<?
	}
}


	?>
		</tbody>
	</table>

	<!-- Kraj vanjske tabele -->
	</td></tr></table></center>
	<?


}


?>