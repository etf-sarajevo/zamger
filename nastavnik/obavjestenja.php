<?

// NASTAVNIK/OBAVJESTENJA - slanje obavjestenja studentima

// v3.9.1.0 (2008/02/22) + Novi modul: nastavnik/obavjestenja
// v3.9.1.1 (2008/09/03) + Dodajem podrsku za email
// v3.9.1.2 (2008/10/02) + Modul nije ispisivao stara obavjestenja ako na predmetu nisu definisane labgrupe; popravljen logging; prebacena forma na genform() radi sigurnosnih aspekata istog; onemoguceno koristenje GET za kreiranje obavjestenja
// v3.9.1.3 (2008/12/01) + Slanje maila je sada opcionalno; ispravljeno vise bugova u slanju maila: salji mail svakom studentu zasebno (drugacije nije radilo :( ); vracanje naslova i teksta u ne-escapovan oblik prije slanja maila; ukinuta nasa slova u imenima; pobrisao svoju adresu iz koda
// v3.9.1.4 (2008/12/23) + Brisanje obavjestenja prebaceno na POST radi zastite od CSRF (bug 53)
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/23) + Nastavnicki moduli sada primaju predmet i akademsku godinu (ag) umjesto ponudekursa; prebacujem tabelu poruka (opseg 5) sa ponudekursa na predmet; provjera spoofinga kod brisanja obavjestenja
// v4.0.9.3 (2009/09/30) + Brisanje obavjestenja nije radilo zbog glupe greske u JavaScriptu i zbog toga sto provjera prava brisanja nije uzimala u obzir opseg labgrupa


function nastavnik_obavjestenja() {

global $userid,$user_siteadmin,$conf_ldap_domain;



// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = mysql_result($q10,0,0);



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)=="asistent") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
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
//    6 - korisnik (primalac - user id)


// Parametri

$naslov = $tekst = "";

$citava = intval($_REQUEST['citava']);
$izmijeni = intval($_REQUEST['izmijeni']);


// Brisanje obavjestenja

if ($_POST['akcija']=="obrisi_obavjestenje" && check_csrf_token()) {
	$obavjestenje = intval($_POST['obavjestenje']);

	// Provjera predmeta
	$q15 = myquery("select primalac, opseg from poruka where id=$obavjestenje");

	if (mysql_num_rows($q15)<1) {
		zamgerlog("poruka $obavjestenje ne postoji",3);
		zamgerlog2("nepostojeca poruka", $obavjestenje);
		nicemessage("Pogrešan ID poruke! Poruka nije obrisana");
	} else {
		// Provjeravamo prava za brisanje
		$primalac=mysql_result($q15,0,0);
		$opseg=mysql_result($q15,0,1);
		if ($opseg==5 && $primalac!=$predmet) {
			zamgerlog("poruka $obavjestenje nije za predmet pp$predmet nego pp$primalac",3);
			zamgerlog2("primalac poruke ne odgovara predmetu", $obavjestenje, $predmet, $ag);
			nicemessage("Pogrešan ID poruke! Poruka nije obrisana");
			return;
		} else if ($opseg==6) {
			$q17 = myquery("select predmet, akademska_godina from labgrupa where id=$primalac");
			if (mysql_result($q17,0,0)!=$predmet || mysql_result($q17,0,1)!=$ag) {
				zamgerlog("poruka $obavjestenje je za labgrupu $primalac koja nije sa pp$predmet",3);
				zamgerlog2("primalac poruke ne odgovara labgrupi", $obavjestenje, $predmet, $ag);
				nicemessage("Pogrešan ID poruke! Poruka nije obrisana");
				return;
			}
		}

		$q20 = myquery("delete from poruka where id=$obavjestenje");
		zamgerlog("obrisano obavjestenje (id $obavjestenje )",2);
		zamgerlog2("obrisana poruka", $obavjestenje);
	}
}



// Novo obavještenje / izmjena obavještenja

if ($_POST['akcija']=='novo' && check_csrf_token()) {
	$naslov = my_escape($_REQUEST['naslov']);
	$tekst = my_escape($_REQUEST['tekst']);
	$primalac = intval($_REQUEST['primalac']);
	if ($_REQUEST['email']) $email=1; else $email=0;
	$io = intval($_REQUEST['izmjena_obavjestenja']);

	if (strlen($naslov)<5) {
		zamgerlog("tekst vijesti je prekratak ($naslov)",3);
		zamgerlog2("tekst poruke je prekratak", 0, 0, 0, $naslov);
		niceerror("Tekst vijesti je prekratak");
	} else {
		if ($io>0) {
			$q6 = myquery("update poruka set tip=1, opseg=5, primalac=$predmet, posiljalac=$userid, ref=0, naslov='$naslov', tekst='$tekst' where id=$io");
			zamgerlog("izmjena obavjestenja (id $io)",2);
			zamgerlog2("poruka izmijenjena", $io);
		} else {
			if ($primalac>0) {
				$q6 = myquery("insert into poruka set tip=1, opseg=6, primalac=$primalac, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='$naslov', tekst='$tekst'");
				$io = mysql_insert_id();

				// Upit za spisak studenata u grupi
				$upit = "select o.id, o.ime, o.prezime from osoba as o, student_labgrupa as sl where sl.labgrupa=$primalac and sl.student=o.id";
			} else {
				$q6 = myquery("insert into poruka set tip=1, opseg=5, primalac=$predmet, posiljalac=$userid, vrijeme=NOW(), ref=0, naslov='$naslov', tekst='$tekst'");
				$io = mysql_insert_id();

				// Upit za spisak studenata na predmetu
				$upit = "select o.id, o.ime, o.prezime from osoba as o, student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag and sp.student=o.id";
			}

			// Šaljem mail studentima
			if ($email==1) {

				// Podaci za konverziju naših slova
				$nasaslova = array("č", "ć", "đ", "š", "ž", "Č", "Ć", "Đ", "Š", "Ž");
				$beznasihslova = array("c", "c", "d", "s", "z", "C", "C", "D", "S", "Z");

				// Subject email poruke
				$subject = "OBAVJEŠTENJE: $predmet_naziv";
				if ($primalac>0) {
					$q8 = myquery("select naziv from labgrupa where id=$primalac");
					$subject .= " (".mysql_result($q8,0,0).")";
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

			zamgerlog("novo obavjestenje (predmet pp$predmet)",2);
			zamgerlog2("nova poruka poslana", $io);
		}

		$naslov=$tekst="";
	}
}


// Stara obavjestenja

// Obavjestenja od proslih akademskih godina nisu relevantna:

$q5 = myquery("select naziv from akademska_godina where id=$ag");
$manjidatum = intval(mysql_result($q5,0,0))."-09-01";
$vecidatum = intval(mysql_result($q5,0,0)+1)."-10-01";


$q10 = myquery("select distinct p.id, UNIX_TIMESTAMP(p.vrijeme), p.naslov, p.tekst, p.opseg, p.primalac from poruka as p, labgrupa as l where p.tip=1 and (p.opseg=5 and p.primalac=$predmet and p.vrijeme>'$manjidatum' and p.vrijeme<'$vecidatum' or p.opseg=6 and p.primalac=l.id and l.predmet=$predmet and l.akademska_godina=$ag) order by vrijeme");
if (mysql_num_rows($q10)>0) {
	print "<p>Do sada unesena obavještenja:</p>\n<ul>\n";
} else {
	print "<p>Do sada niste unijeli nijedno obavještenje.</p>";
}
while ($r10 = mysql_fetch_row($q10)) {
	if ($obrisi == $r10[0]) {
		$q20 = myquery("delete from poruka where id=$obrisi");
		zamgerlog("obrisano obavjestenje (id $obrisi)",2);
		zamgerlog2("obrisana poruka", $obrisi);
		continue;
	}
	print "<li><b>(".date("d.m.Y",$r10[1]).")</b> ".$r10[2];
	$tekst_poruke = str_replace("\n", "<br/>", $r10[3]);
	if (strlen($tekst_poruke)>0) {
		if ($citava==$r10[0])
			print "<br/><br/>".$tekst_poruke;
		else
			print " (<a href=\"?sta=nastavnik/obavjestenja&predmet=$predmet&ag=$ag&citava=$r10[0]\">Dalje...</a>)";
	}
	if ($izmijeni == $r10[0]) {
		$naslov = $r10[2];
		$tekst = $r10[3];
		if ($r10[4]==5)
			$labgrupa=0;
		else
			$labgrupa=$r10[5];
	}
	print "<br/> <a href=\"?sta=nastavnik/obavjestenja&predmet=$predmet&ag=$ag&izmijeni=$r10[0]\">[Izmijeni]</a> <a href=\"javascript:onclick=upozorenje('$r10[0]')\">[Obriši]</a></li>\n";
}
if (mysql_num_rows($q10)>0) {
	print "</ul>\n";
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
$q20 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag order by naziv");
while ($r20 = mysql_fetch_row($q20)) {
	if ($r20[0]==$labgrupa) $sel="SELECTED"; else $sel="";
	?><option value="<?=$r20[0]?>" <?=$sel?>><?=$r20[1]?></option>
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
