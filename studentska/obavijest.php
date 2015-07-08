<?

// STUDENTSKA/OBAVIJEST + slanje obavjestenja za studentsku sluzbu

// v3.9.1.0 (2008/09/02) + Kopiran common/inbox u studentska/obavijest
// v3.9.1.1 (2008/10/03) + Postrozen uslov za slanje na POST
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/10) + Omoguci administratoru da posalje poruku svim korisnicima Zamgera; bilo moguce izmijeniti sve poruke (ukljucujuci privatne) preko IDa poruke; pored toga, onemoguceno editovanje poruka za opsege koje normalno nije moguce slati (vidjecemo koliko ce to predstavljati problem); bilo moguce slanje poruke sa bilo kojim opsegom kroz spoofing URLa; uskladjivanje koda za slanje maila sa izmjenama u nastavnik/obavjestenje; onemogucen spamming (slanje maila svim studentima ili svim nastavnicima)
// v4.0.9.1 (2009/04/29) + Prebacujem tabelu poruka (opseg 5) sa ponudekursa na predmet (neki studenti ce mozda dobiti dvije identicne poruke)
// v4.0.9.2 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet



function studentska_obavijest() {

global $userid,$conf_ldap_domain,$user_siteadmin,$conf_skr_naziv_institucije_genitiv;

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
//    8 - svi studenti na godini studija (primalac - idstudija*10+godina_studija)



// Podaci potrebni kasnije

// Zadnja akademska godina
$q20 = myquery("select id,naziv from akademska_godina where aktuelna=1 order by id desc limit 1");
$ag = mysql_result($q20,0,0);
$ag_naziv = mysql_result($q20,0,1);

// Studij koji student trenutno sluša
$studij=0;
if ($user_student) {
	$q30 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
	if (mysql_num_rows($q30)>0) {
		$studij = mysql_result($q30,0,0);
	}
}



// Pravimo neki okvir za sajt

?>
<center>
<table width="80%" border="0"><tr><td>

<h1>Slanje obavijesti</h1>

<?



//////////////////////
// Slanje poruke
//////////////////////

if ($_POST['akcija']=='send' && check_csrf_token()) {
	// Ko je primalac
	$primalac = intval($_REQUEST['primalac']);
	$opseg = intval($_REQUEST['opseg']);
	$poruka = intval($_REQUEST['poruka']);

	// Pogrešan opseg
	if ($opseg!=1 && $opseg!=2 && $opseg!=3 && $opseg!=5 && $opseg!=8 && ($opseg!=0 || !$user_siteadmin)) {
		niceerror("Nemate pravo slanja poruke sa tim opsegom");
		zamgerlog("pokusaj slanja/izmjene poruke sa opsegom $opseg",3);
		zamgerlog2("pokusaj slanja/izmjene poruke sa opsegom", $opseg);
		return;
	}

	$naslov = my_escape($_REQUEST['naslov']);
	$tekst = my_escape($_REQUEST['tekst']);
	if ($_REQUEST['email']) $email=1; else $email=0;

	if ($poruka>0) {
		// Editovanje poruke
		$q310 = myquery("update poruka set tip=1, opseg=$opseg, primalac=$primalac, naslov='$naslov', tekst='$tekst' where id=$poruka");
		nicemessage("Obavijest uspješno izmijenjena");
		zamgerlog("izmijenjena obavijest $poruka",2);
		zamgerlog2("izmijenjena poruka", $poruka);
	} else {
		// Nova obavijest
		$q310 = myquery("insert into poruka set tip=1, opseg=$opseg, primalac=$primalac, posiljalac=$userid, vrijeme=NOW(), naslov='$naslov', tekst='$tekst'");
		$id_poruke = mysql_insert_id();

		// Saljem mail...
		if ($email && ($opseg==3 || $opseg==5)) { // nema spamanja!

			// Podaci za konverziju naših slova
			$nasaslova = array("č", "ć", "đ", "š", "ž", "Č", "Ć", "Đ", "Š", "Ž");
			$beznasihslova = array("c", "c", "d", "s", "z", "C", "C", "D", "S", "Z");

			if ($opseg == 3) {
				$upit = "select o.email, a.login, o.ime, o.prezime from osoba as o, auth as a, student_studij as ss, akademska_godina as ag where ss.student=o.id and ss.student=a.id and ss.studij=$primalac and ss.akademska_godina=ag.id and ag.aktuelna=1";
				$q320 = myquery("select naziv from studij where id=$primalac");
				$subject = "OBAVJEŠTENJE: Svi studenti na ".mysql_result($q320,0,0);

			} else if ($opseg == 5) {
				// Saljemo mail samo studentima na aktuelnoj akademskoj godini
				$upit = "select o.email, a.login, o.ime, o.prezime from osoba as o, auth as a, student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$primalac and pk.akademska_godina=$ag and sp.student=o.id and sp.student=a.id";
				$q330 = myquery("select naziv from predmet where id=$primalac");
				$subject = "OBAVJEŠTENJE: Svi studenti na ".mysql_result($q330,0,0);
			}

			// Subject email poruke
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
			
 			$mail_body = "\n=== OBAVJEŠTENJE ZA STUDENTE ===\n\nStudentska služba $conf_skr_naziv_institucije_genitiv poslala vam je sljedeće obavještenje:\n\n$naslov\n\n$tekst";

			// Podaci za from polje
			$q9 = myquery("select o.ime, o.prezime from osoba as o where o.id=$userid");
			$from = mysql_result($q9,0,0)." ".mysql_result($q9,0,1);
			$from = str_replace($nasaslova, $beznasihslova, $from);

			$q9a = myquery("SELECT adresa FROM email WHERE osoba=$userid ORDER BY sistemska DESC, id");
			if (mysql_num_rows($q9a)<1) {
				niceerror("Ne možemo poslati mail jer nemate definisanu adresu.");
				print "Da bi se mail mogao poslati, mora biti definisana odlazna adresa (adresa pošiljaoca). Molimo vas da u vašem <a href=\"?sta=common/profil\">profilu</a> podesite vašu e-mail adresu.";
				return 0;
			}
			$from .= " <".mysql_result($q9a,0,0).">";

			$add_header = "From: $from\r\nContent-Type: text/plain; charset=utf-8\r\n";

			$broj=0;
			$q7 = myquery($upit);

			while ($r7 = mysql_fetch_row($q7)) {
				$student_id = $r7[0];
				$student_ime_prezime = str_replace($nasaslova, $beznasihslova, "$r7[1] $r7[2]");

				// Određujemo email adrese studenta
				$q9b = myquery("SELECT adresa FROM email WHERE osoba=$student_id ORDER BY sistemska DESC, id");
				$mail_to = "";
				$mail_cc = "";
				// Prvu adresu stavljamo u To: a sve ostale u Cc: kako bi mail server otkrio eventualne aliase
				while ($r9b = mysql_fetch_row($q9b)) {
					if ($mail_to == "") $mail_to = $r9b[0];
					$mail_cc .= "$student_ime_prezime <$r9b[0]>; ";
				}

				if ($mail_to != "") { // Da li student ima ijednu adresu?
					mail($mail_to, $subject, $mail_body, "$add_header"."Cc: $mail_cc");
					nicemessage ("Mail poslan za $student_ime_prezime &lt;$mail_to&gt;");
				}
			}

		} // if ($email==1)...

		nicemessage("Obavijest uspješno poslana");
		zamgerlog("poslana obavijest, opseg $opseg primalac $primalac",2);
		zamgerlog2("poslana poruka", $id_poruke);
	}
}



if ($_REQUEST['akcija']=='compose' || $_REQUEST['akcija']=='izmjena') {
	$opseg=0;
	if ($_REQUEST['akcija']=='izmjena') {
		$poruka = intval($_REQUEST['poruka']);
		$q200 = myquery("select primalac, naslov, tekst, opseg from poruka where id=$poruka and tip=1");
		if (mysql_num_rows($q200) < 1) {
			niceerror("Poruka ne postoji");
			zamgerlog("pokusaj izmjene na nepostojece poruke $poruka",3);
			return;
		}

		// Pogrešan opseg
		if ($opseg!=1 && $opseg!=2 && $opseg!=3 && $opseg!=5 && $opseg!=8 && ($opseg!=0 || !$user_siteadmin)) {
			niceerror("Nemate pravo izmjene ove poruke");
			zamgerlog("pokusaj izmjene poruke $poruka sa opsegom $opseg",3);
			return;
		}
		
		// Prepravka naslova i teksta
		$primalac = mysql_result($q200,0,0);
		$naslov = mysql_result($q200,0,1);
		$tekst = mysql_result($q200,0,2);
		$opseg = mysql_result($q200,0,3);
	}
		
	?>
	<a href="?sta=studentska/obavijest">Nazad na obavijesti</a><br/>
	<h3>Slanje obavijesti</h3>
	<?=genform("POST")?>
	<?
/*	if ($_REQUEST['akcija']=='izmjena') {
		?>
		<input type="hidden" name="ref" value="<?=$poruka?>"><?
	}*/
	?>
	<input type="hidden" name="akcija" value="send">
	<script language="JavaScript">
	function spisak_primalaca(opseg) {
		var lista=document.getElementById('primalac');
		while (lista.length>0)
			lista.options[0]=null;
		if (opseg==0 || opseg==1 || opseg==2) {
			// Nista
		} else if (opseg==3) {
			<?
			$q210 = myquery("select id,naziv from studij");
			while ($r210 = mysql_fetch_row($q210)) {
				print "	lista.options[lista.length]=new Option(\"$r210[1]\",\"$r210[0]\"";
				if ($opseg==3 && $primalac==$r210[0]) print ",true";
				print ");\n";
			}
			?>
		} else if (opseg==8) {
			<?
			// Prvi ciklus
			for ($i=1; $i<=3; $i++) {
				$kod = -10-$i;
				print "	lista.options[lista.length]=new Option(\"Svi studiji, Prvi ciklus, $i. godina\",\"$kod\"";
				if ($opseg==8 && $primalac==$kod) print ",true";
				print ");\n";
			}
			// Drugi ciklus
			for ($i=1; $i<=2; $i++) {
				$kod = -20-$i;
				print "	lista.options[lista.length]=new Option(\"Svi studiji, Drugi ciklus, $i. godina\",\"$kod\"";
				if ($opseg==8 && $primalac==$kod) print ",true";
				print ");\n";
			}
			// Ostali
			$q210 = myquery("select s.id, s.naziv, ts.trajanje from studij as s, tipstudija as ts where s.moguc_upis=1 and s.tipstudija=ts.id");
			while ($r210 = mysql_fetch_row($q210)) {
				$trajanje_godina = ($r210[2]+1) / 2;
				for ($i=1; $i<=$trajanje_godina; $i++) {
					$kod = $r210[0] * 10 + $i;
					print "	lista.options[lista.length]=new Option(\"$r210[1], $i. godina\",\"$kod\"";
					if ($opseg==3 && $primalac==$kod) print ",true";
					print ");\n";
				}
			}
			?>
		} else if (opseg==4) {
			// Godini!?
		} else if (opseg==5) {
			<?
			$q220 = myquery("select p.id, p.naziv, s.kratkinaziv from ponudakursa as pk, predmet as p, studij as s where pk.predmet=p.id and pk.studij=s.id and pk.akademska_godina=$ag order by pk.studij, pk.semestar, p.naziv");
			while ($r220 = mysql_fetch_row($q220)) {
				print "	lista.options[lista.length]=new Option(\"$r220[1] ($r220[2])\",\"$r220[0]\"";
				if ($opseg==5 && $primalac==$r220[0]) print ",true";
				print ");\n";
			}
			?>
		}
	}
	</script>

	<p><b>Tip primaoca:</b> 
		<select name="opseg" id="opseg" onchange="spisak_primalaca(this.value)"><?
	if ($user_siteadmin) { ?>
		<option value="0" <? if ($opseg==0) print "selected"; ?>>Svi korisnici Zamgera</option><? } ?>
		<option value="1" <? if ($opseg==1) print "selected"; ?>>Svi studenti</option>
		<option value="2">Svi nastavnici</option>
		<option value="3" <? if ($opseg==3) print "selected"; ?>>Svi studenti na studiju</option>
		<option value="8" <? if ($opseg==8) print "selected"; ?>>Svi studenti na godini studija</option>
		<option value="5" <? if ($opseg==5) print "selected"; ?>>Svi studenti na predmetu</option>
		</select><br/>
	&nbsp;<br/>
	<b>Primalac:</b>
		<select name="primalac" id="primalac"></select>
	</p>

	<? if ($opseg==3 || $opseg==5 || $opseg==8) {
		?><script language="JavaScript">
		spisak_primalaca(<?=$opseg?>);
		</script><?
	}
	?>
	<input type="checkbox" name="email" value="1"> Slanje e-maila
	</p>

	<br/>
	Skraćeni tekst obavijesti:<br/>
	<textarea name="naslov" rows="10" cols="81"><?=$naslov?></textarea>
	<br/>&nbsp;<br/>
	Nastavak teksta obavijesti:<br/>
	<textarea name="tekst" rows="10" cols="81"><?=$tekst?></textarea>
	<br/>&nbsp;<br/>
	<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
	</form>
	<?
	return;
}



?>
<p><a href="?sta=studentska/obavijest&akcija=compose">Pošalji novu obavijest</a></p>
<?



//////////////////////
// Čitanje poruke
//////////////////////


$mjeseci = array("", "januar", "februar", "mart", "april", "maj", "juni", "juli", "avgust", "septembar", "oktobar", "novembar", "decembar");

$dani = array("Nedjelja", "Ponedjeljak", "Utorak", "Srijeda", "Četvrtak", "Petak", "Subota");

$poruka = intval($_REQUEST['poruka']);
if ($poruka>0) {
	// Dobavljamo podatke o poruci
	$q10 = myquery("select opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), naslov, tekst from poruka where id=$poruka and tip=1");
	if (mysql_num_rows($q10)<1) {
		niceerror("Poruka ne postoji");
		zamgerlog("pristup nepostojecoj poruci $poruka",3);
		zamgerlog2("nepostojeca poruka", $poruka);
		return;
	}

	// Posiljalac
	$opseg =  mysql_result($q10,0,0);
	$prim_id = mysql_result($q10,0,1);
	$pos_id = mysql_result($q10,0,2);

	$q20 = myquery("select ime,prezime from osoba where id=$pos_id");
	if (mysql_num_rows($q20)<1) {
		$posiljalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznatog posiljaoca $pos_id",3);
		zamgerlog2("poruka ima nepoznatog posiljaoca", $poruka);
	} else
		$posiljalac = mysql_result($q20,0,0)." ".mysql_result($q20,0,1);

	// Primalac
	if ($opseg==0)
		$primalac="Svi korisnici Zamgera";
	else if ($opseg==1)
		$primalac="Svi studenti";
	else if ($opseg==2)
		$primalac="Svi nastavnici i saradnici";
	else if ($opseg==3) {
		$q30 = myquery("select naziv from studij where id=$prim_id");
		if (mysql_num_rows($q30)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: studij)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: studij)", $poruka);
		} else {
			$primalac = "Svi studenti na: ".mysql_result($q30,0,0);
		}
	}
	else if ($opseg==4) {
		$q40 = myquery("select naziv from akademska_godina where id=$prim_id");
		if (mysql_num_rows($q40)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: akademska godina)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: akademska godina)", $poruka);
		} else {
			$primalac = "Svi studenti na akademskoj godini: ".mysql_result($q40,0,0);
		}
	}
	else if ($opseg==5) {
		$q50 = myquery("select naziv from predmet where id=$prim_id");
		if (mysql_num_rows($q50)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: predmet)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: predmet)", $poruka);
		} else {
			$primalac = "Svi studenti na predmetu: ".mysql_result($q50,0,0);
		}
	}
	else if ($opseg==6) {
		$q55 = myquery("select p.naziv,l.naziv from predmet as p, labgrupa as l where l.id=$prim_id and l.predmet=p.id");
		if (mysql_num_rows($q55)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: labgrupa)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: labgrupa)", $poruka);
		} else {
			$primalac = "Svi studenti u grupi ".mysql_result($q55,0,1)." (".mysql_result($q55,0,0).")";
		}
	}
	else if ($opseg==7) {
		$q60 = myquery("select ime,prezime from osoba where id=$prim_id");
		if (mysql_num_rows($q60)<1) {
			$primalac = "Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: korisnik)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: korisnik)", $poruka);
		} else
			$primalac = mysql_result($q60,0,0)." ".mysql_result($q60,0,1);
	}
	else if ($opseg==8) {
		$studij = intval($prim_id / 10);
		if ($studij == -1) {
			$godina = -($prim_id+10);
			$primalac = "Svi studenti na: Prvom ciklusu studija, $godina. godina";
		} else if ($studij == -2) {
			$godina = -($prim_id+20);
			$primalac = "Svi studenti na: Drugom ciklusu studija, $godina. godina";
		} else {
			$godina = $prim_id%10;
			$q30 = myquery("select naziv from studij where id=$studij");
			if (mysql_num_rows($q30)<1) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: godina studija)",3);
				zamgerlog2("poruka ima nepoznatog primaoca (opseg: godina studija)", $poruka, $prim_id);
			} else {
				$primalac = "Svi studenti na: ".mysql_result($q30,0,0).", $godina. godina";
			}
		}
	}
	else {
		$primalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznat opseg $opseg",3);
		zamgerlog2("poruka ima nepoznat opseg", $poruka);
	}

	// Fini datum
	$vr = mysql_result($q10,0,3);
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i> - ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i> - ";
	$vrijeme .= $dani[date("w",$vr)].date(", j. ",$vr).$mjeseci[date("n",$vr)].date(" Y. H:i",$vr);

	// Naslov
	$naslov = mysql_result($q10,0,4);
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	?><h3>Prikaz obavijesti</h3>
	<table cellspacing="0" cellpadding="0" border="0"  style="border:1px;border-color:silver;border-style:solid;"><tr><td bgcolor="#f2f2f2">
		<table border="0">
			<tr><td><b>Vrijeme slanja:</b></td><td><?=$vrijeme?></td></tr>
			<tr><td><b>Pošiljalac:</b></td><td><?=$posiljalac?></td></tr>
			<tr><td><b>Primalac:</b></td><td><?=$primalac?></td></tr><?
	if (($opseg==0 && $user_siteadmin) || $opseg==1 || $opseg==2 || $opseg==3 || $opseg==5) { ?>
			<tr><td>&nbsp;</td><td><a href="?sta=studentska/obavijest&akcija=izmjena&poruka=<?=$poruka?>">Izmijeni ovo obavještenje</a></td></tr>
	<? } ?>
		</table>
	</td></tr><tr><td>
		<br/>
		<table border="0" cellpadding="5"><tr><td>
		<?
		print str_replace("\n","<br/>\n",$naslov);
		?>
		</td><tr></table>
	</td></tr><tr><td>
		<br/>
		<table border="0" cellpadding="5"><tr><td>
		<?
		print str_replace("\n","<br/>\n",mysql_result($q10,0,5));
		?>
		</td><tr></table>
	</td></tr></table>
	<br/>
	<br/><hr><br/><?
}



//////////////////////
// OUTBOX
//////////////////////


	
?>
<h3>Poslana obavještenja</h3>
<table border="0" width="100%" style="border:1px;border-color:silver;border-style:solid;">
	<thead>
	<tr bgcolor="#cccccc"><td width="15%"><b>Datum</b></td><td width="15%"><b>Pošiljalac</b></td><td width="30%"><b>Primalac</b></td><td width="40%"><b>Naslov</b></td></tr>
	</thead>
	<tbody>
<?


$vrijeme_poruke = array();

$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, posiljalac from poruka where tip=1 order by vrijeme desc");
while ($r100 = mysql_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$prim_id = $r100[3];
	$pos_id = $r100[5];

	$vrijeme_poruke[$id]=$r100[1];
	$naslov = $r100[4];
	if (strlen($naslov)>60) $naslov = substr($naslov,0,55)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Primalac
	if ($opseg==0)
		$primalac="Svi korisnici Zamgera";
	else if ($opseg==1)
		$primalac="Svi studenti";
	else if ($opseg==2)
		$primalac="Svi nastavnici i saradnici";
	else if ($opseg==3) {
		$q30 = myquery("select naziv from studij where id=$prim_id");
		if (mysql_num_rows($q30)<1) {
			$primalac="Nepoznat studij!?";
		} else {
			$primalac = "Svi studenti na:<br/> ".mysql_result($q30,0,0);
		}
	}
	else if ($opseg==4) {
		$q40 = myquery("select naziv from akademska_godina where id=$prim_id");
		if (mysql_num_rows($q40)<1) {
			$primalac="Nepoznata akademska godina!?";
		} else {
			$primalac = "Svi studenti na akademskoj godini:<br/> ".mysql_result($q40,0,0);
		}
	}
	else if ($opseg==5) {
		$q50 = myquery("select p.naziv,i.kratki_naziv from predmet as p, institucija as i where p.id=$prim_id and p.institucija=i.id");
		if (mysql_num_rows($q50)<1) {
			$primalac="Nepoznat predmet!?";
		} else {
			$primalac = "Svi studenti na predmetu:<br/> ".mysql_result($q50,0,0)." (".mysql_result($q50,0,1).")";
		}
	}
	else if ($opseg==6) {
		$q55 = myquery("select p.naziv,l.naziv from predmet as p, labgrupa as l where l.id=$prim_id and l.predmet=p.id");
		if (mysql_num_rows($q55)<1) {
			$primalac="Nepoznata labgrupa!?";
		} else {
			$primalac = "Svi studenti u grupi<br/> ".mysql_result($q55,0,1)." (".mysql_result($q55,0,0).")";
		}
	}
	else if ($opseg==7) {
		$q60 = myquery("select ime,prezime from osoba where id=$prim_id");
		if (mysql_num_rows($q60)<1) {
			$primalac = "Nepoznata osoba!?";
		} else
			$primalac = mysql_result($q60,0,0)." ".mysql_result($q60,0,1);
	}
	else if ($opseg==8) {
		$studij = intval($prim_id / 10);
		if ($studij == -1) {
			$godina = -($prim_id+10);
			$primalac = "Svi studenti na: I ciklus, $godina. godina";
		} else if ($studij == -2) {
			$godina = -($prim_id+20);
			$primalac = "Svi studenti na: II ciklus, $godina. godina";
		} else {
			$godina = $prim_id%10;
			$q30 = myquery("select s.kratkinaziv, ts.ciklus from studij as s, tipstudija as ts where s.id=$studij and s.tipstudija=ts.id");
			if (mysql_num_rows($q30)<1) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: godina studija)",3);
				zamgerlog2("poruka ima nepoznatog primaoca (opseg: godina studija)", $poruka, $prim_id);
			} else {
				$primalac = "Svi studenti na: ".mysql_result($q30,0,0).", $godina. godina ".mysql_result($q30,0,1)." ciklus";
			}
		}
	}
	else {
		$primalac = "Nepoznato!?";
	}

	// Posiljalac
	$q70 = myquery("select ime,prezime from osoba where id=$pos_id");
	if (mysql_num_rows($q70)<1) {
		$posiljalac = "Nepoznata osoba!?";
	} else
		$posiljalac = mysql_result($q70,0,0)." ".mysql_result($q70,0,1);
	

	// Fino vrijeme
	$vr = $vrijeme_poruke[$id];
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>, ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>, ";
	else $vrijeme .= date("j. ",$vr).$mjeseci[date("n",$vr)].", ";
	$vrijeme .= date("H:i",$vr);

	if ($_REQUEST['poruka'] == $id) $bgcolor="#EEEECC"; else $bgcolor="#FFFFFF";

	$code_poruke[$id]="<tr bgcolor=\"$bgcolor\" onmouseover=\"this.bgColor='#EEEEEE'\" onmouseout=\"this.bgColor='$bgcolor'\"><td>$vrijeme</td><td>$posiljalac</td><td>$primalac</td><td><a href=\"?sta=studentska/obavijest&poruka=$id&mode=outbox\">$naslov</a></td></tr>\n";
}

// Sortiramo po vremenu
arsort($vrijeme_poruke);
$count=0;
foreach ($vrijeme_poruke as $id=>$vrijeme) {
	print $code_poruke[$id];
	$count++;
	// if ($count==20) break; // prikazujemo 20 poruka  -- TODO: stranice
}
if ($count==0) {
	print "<li>Nemate nijednu poruku.</li>\n";
}

print "</tbody></table>";

?>
</td></tr></table></center>
<?




}


?>
