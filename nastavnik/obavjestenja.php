<?

// NASTAVNIK/OBAVJESTENJA - slanje obavjestenja studentima

// v3.9.1.0 (2008/02/22) + Novi modul: nastavnik/obavjestenja
// v3.9.1.1 (2008/09/03) + Dodajem podrsku za email
// v3.9.1.2 (2008/10/02) + Modul nije ispisivao stara obavjestenja ako na predmetu nisu definisane labgrupe; popravljen logging


function nastavnik_obavjestenja() {

global $userid,$user_siteadmin,$conf_ldap_domain;



$predmet=intval($_REQUEST['predmet']);
if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

//$tab=$_REQUEST['tab'];
//if ($tab=="") $tab="Opcije";

//logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo pristupa

if (!$user_siteadmin) {
	$q10 = myquery("select np.admin from nastavnik_predmet as np where np.nastavnik=$userid and np.predmet=$predmet");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("nastavnik/obavjestenja privilegije (predmet p$predmet)",3);
		biguglyerror("Nemate pravo pristupa");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Obavještenja za studente</h3></p>

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
$obrisi =  intval($_REQUEST['obrisi']);


// Novo obavještenje / izmjena obavještenja

if ($_REQUEST['akcija']=='novo') {
	$naslov = my_escape($_REQUEST['naslov']);
	$tekst = my_escape($_REQUEST['tekst']);
	$primalac = intval($_REQUEST['primalac']);
	$io = intval($_REQUEST['izmjena_obavjestenja']);

	if (strlen($naslov)<5) {
		zamgerlog("tekst vijesti je prekratak ($naslov)",3);
		niceerror("Tekst vijesti je prekratak");
	} else {
		if ($io>0) {
			$q6 = myquery("update poruka set tip=1, opseg=5, primalac=$predmet, posiljalac=$userid, ref=0, naslov='$naslov', tekst='$tekst' where id=$io");
			zamgerlog("izmjena obavjestenja (id $io)",2);
		} else {
			if ($primalac>0) {
				$q6 = myquery("insert into poruka set tip=1, opseg=6, primalac=$primalac, posiljalac=$userid, ref=0, naslov='$naslov', tekst='$tekst'");

				// Spisak studenata u grupi
				$upit = "select o.email, a.login, o.ime, o.prezime from osoba as o, auth as a, student_labgrupa as sl where sl.labgrupa=$primalac and sl.student=o.id and sl.student=a.id";
			} else {
				$q6 = myquery("insert into poruka set tip=1, opseg=5, primalac=$predmet, posiljalac=$userid, ref=0, naslov='$naslov', tekst='$tekst'");

				// Spisak studenata na predmetu
				$upit = "select o.email, a.login, o.ime, o.prezime from osoba as o, auth as a, student_predmet as sp where sp.predmet=$predmet and sp.student=o.id and sp.student=a.id";
			}

			// Saljem mail studentima

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

 			$mail_body = "\n=== OBAVJEŠTENJE ZA STUDENTE ===\n\nNastavnik ili saradnik na predmetu $predmet_naziv poslao vam je sljedeće obavještenje:\n\n$naslov\n\n$tekst";

			$q9 = myquery("select o.ime, o.prezime, o.email, a.login from osoba as o, auth as a where o.id=$userid and a.id=$userid");
			$imeprezime = mysql_result($q9,0,0)." ".mysql_result($q9,0,1);
			$email = mysql_result($q9,0,2);
			if (!(strpos($email,"@"))) $email = mysql_result($q9,0,3) . $conf_ldap_domain;
			
			$add_header = "From: $email ($imeprezime)\r\nContent-Type: text/plain; charset=utf-8\r\n";

			$mailto = "";
			$broj=0;
			$q7 = myquery($upit);
			while ($r7 = mysql_fetch_row($q7)) {
				$mailto .= "$r7[1]$conf_ldap_domain ($r7[2] $r7[3]); ";
				$broj++;
				if ($r7[0]!="$r7[1]$conf_ldap_domain") {
					$mailto .= "$r7[0] ($r7[2] $r7[3]); ";
					$broj++;
				}
				if ($broj>10) {
					mail("vljubovic@etf.unsa.ba", $subject, $mail_body, "$add_header"."Bcc: $mailto");
					$mailto=""; $broj=0;
				}
			}
			if ($broj>0)
				mail("vljubovic@etf.unsa.ba", $subject, $mail_body, "$add_header"."Bcc: $mailto");

			zamgerlog("novo obavjestenje (predmet p$predmet)",2);
		}

		$naslov=$tekst="";
	}
}


// Stara obavjestenja

$q10 = myquery("select distinct p.id, UNIX_TIMESTAMP(p.vrijeme), p.naslov, p.tekst, p.opseg, p.primalac from poruka as p, labgrupa as l where p.tip=1 and (p.opseg=5 and p.primalac=$predmet or p.opseg=6 and p.primalac=l.id and l.predmet=$predmet) order by vrijeme");
if (mysql_num_rows($q10)>0) {
	print "<p>Do sada unesena obavještenja:</p>\n<ul>\n";
} else {
	print "<p>Do sada niste unijeli nijedno obavještenje.</p>";
}
while ($r10 = mysql_fetch_row($q10)) {
	if ($obrisi == $r10[0]) {
		$q20 = myquery("delete from poruka where id=$obrisi");
		zamgerlog("obrisano obavjestenje (id $obrisi)",2);
		continue;
	}
	print "<li><b>(".date("d.m.Y",$r10[1]).")</b> ".$r10[2];
	if (strlen($r10[3])>0) {
		if ($citava==$r10[0])
			print "<br/><br/>".$r10[3];
		else
			print " (<a href=\"?sta=nastavnik/obavjestenja&predmet=$predmet&citava=$r10[0]\">Dalje...</a>)";
	}
	if ($izmijeni == $r10[0]) {
		$naslov = $r10[2];
		$tekst = $r10[3];
		if ($r10[4]==5)
			$labgrupa=0;
		else
			$labgrupa=$r10[5];
	}
	print "<br/> <a href=\"?sta=nastavnik/obavjestenja&predmet=$predmet&izmijeni=$r10[0]\">[Izmijeni]</a> <a href=\"?sta=nastavnik/obavjestenja&predmet=$predmet&obrisi=$r10[0]\">[Obriši]</a></li>\n";
}
if (mysql_num_rows($q10)>0) {
	print "</ul>\n";
}


// Formular za novo obavještenje

?>
<hr>
<form action="index.php" method="POST">
<input type="hidden" name="sta" value="nastavnik/obavjestenja">
<input type="hidden" name="predmet" value="<?=$predmet?>">
<input type="hidden" name="akcija" value="novo">
<? if ($izmijeni>0) { ?>
<input type="hidden" name="izmjena_obavjestenja" value="<?=$izmijeni?>">
<p><b>Izmjena postojećeg obavještenja</b></p>
<? } else {
?>
<p><b>Unos novog obavještenja</b></p>
<? } ?>
<p>Obavještenje za: <select name="primalac" class="default"><option value="0">Sve studente</option>
<?
$q20 = myquery("select id,naziv from labgrupa where predmet=$predmet order by naziv");
while ($r20 = mysql_fetch_row($q20)) {
	if ($r20[0]==$labgrupa) $sel="SELECTED"; else $sel="";
	?><option value="<?=$r20[0]?>" <?=$sel?>><?=$r20[1]?></option>
	<?
}
?>
</select></p>
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