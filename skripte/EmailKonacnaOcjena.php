<?php

require("../www/lib/libvedran.php");
require("../www/lib/zamger.php");
require("../www/lib/config.php");

$prikaziMailove = true;
$intervalDana = 1;
// Ne salji mailove
$dryRun = false; 

if($conf_email) {


//dobavljanje parametara sa html forme
$subject = "Zamger: Dnevni izvjestaj upisanih ocjena" ;

//kreiranje konekcije na bazu



dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


//dobavljanje liste profesora koji su angazovani sa njihovim ID-om i EMAILom
$result = mysql_query('SELECT DISTINCT o.id, o.ime, o.prezime, o.email, o.spol FROM osoba AS o, angazman AS a, akademska_godina AS ag WHERE a.osoba = o.id AND a.angazman_status=1 AND a.akademska_godina=ag.id AND ag.aktuelna=1');

if (!$result) {
	die('Invalid query: ' . mysql_error());
}

while ($row = mysql_fetch_assoc($result)) { //otvori while($row)
	print "Prof. ".$row['ime']." ".$row['prezime'].": ";

	// Da li je za prof definisan email?
	if ($row['email'] == "") {
		print "nema email - preskacem\n";
		continue;
	}
	
	$biloOcjena = false;

	// Zaglavlje maila
	if ($row['spol'] == 'Z' || ($row['spol'] == '' && spol($row['ime'])=="Z"))
		$message = "Postovana prof. {$row['prezime']}, \n";
	else
		$message = "Postovani prof. {$row['prezime']}, \n";

	if ($intervalDana == 1)
		$intervalTekst = "prethodna 24 sata";
	else
		$intervalTekst = "prethodnih $intervalDana dana";
	
	$message .= "U nastavku maila se nalazi spisak unesenih ocjena na predmetima koje Vi predajete, a unesene su unutar $intervalTekst. U zagradi pored svake ocjene dat je tačan datum i vrijeme unosa te osoba pod čijim korisničkim nalogom je ocjena unijeta.\n";

	// Spisak predmeta na kojima je prof. angazovan u tekucoj a.g.
	$q100 = mysql_query("SELECT p.id, p.naziv, ag.id
FROM predmet AS p, angazman AS a, akademska_godina AS ag
WHERE a.osoba = {$row['id']} AND a.predmet=p.id AND a.akademska_godina=ag.id AND ag.aktuelna=1 AND a.angazman_status=1 ORDER BY p.naziv") or die ("SQL greska: ".mysql_error());
	while ($r100 = mysql_fetch_row($q100)) {
	
		// Preskačemo završni rad FIXME
		$nazivPredmeta = $r100[1];
		if (substr($nazivPredmeta,0,12) == "Završni rad") continue;

		print "\n* ".substr($nazivPredmeta,0,20)."... :";
		
		//dobavljanje podataka o ocjenama unesenim u prethodna 24 sata
		$q110 = mysql_query("SELECT o.id AS student_id, o.ime AS ime, o.prezime AS prezime, o.brindexa AS brindexa, o.email AS email, ko.ocjena AS ocjena, UNIX_TIMESTAMP(ko.datum) AS datum
		FROM konacna_ocjena AS ko, osoba AS o
		WHERE ko.predmet=$r100[0] AND ko.akademska_godina=$r100[2] AND ko.datum >= DATE_SUB(NOW(), INTERVAL $intervalDana DAY) AND ko.student=o.id
		ORDER BY prezime, ime") or die ("SQL greska: ".mysql_error());
		if (mysql_num_rows($q110)<1) {
			print " nema novih ocjena.";
			continue;
		}
	
		$biloOcjena = true;
		$message .= "\n";
		$message.="Predmet: $nazivPredmeta \n";
		$redniBroj = 1;

		while ($row2 = mysql_fetch_assoc($q110)) { //otvori while($row2)
			// Tražimo unositelja ocjene u logovima
			$unosilac = "";
			$logzapis = "AJAH ko - dodana ocjena ".$row2["ocjena"]." (predmet pp".$r100[0].", student u".$row2["student_id"].")";

			$q = mysql_query("SELECT l.userid, o.ime, o.prezime FROM log AS l, osoba AS o WHERE l.userid=o.id AND l.dogadjaj='{$logzapis}' AND DATE_SUB(NOW(),INTERVAL $intervalDana DAY) <= l.vrijeme") or die ("SQL greska 1: ".mysql_error());
			if (mysql_num_rows($q)>0) {
				$unosilac = mysql_result($q,0,1)." ".mysql_result($q,0,2);
			}
			if ($unosilac=="") {
				$logzapis2 = " u ".$row2["ocjena"]." (predmet pp".$r100[0].", student u".$row2["student_id"].")";
				$q2 = mysql_query("SELECT l.userid, o.ime, o.prezime FROM log AS l, osoba AS o WHERE l.userid=o.id AND l.dogadjaj LIKE '%{$logzapis2}%' AND DATE_SUB(NOW(),INTERVAL $intervalDana DAY) <= l.vrijeme") or die ("SQL greska 2: ".mysql_error());
				if (mysql_num_rows($q2)>0) {
					$unosilac = mysql_result($q2,0,1)." ".mysql_result($q2,0,2);
				}
			}
			if ($unosilac=="") {
				$logzapis2 = "masovno upisane ocjene na predmet pp".$r100[0];
				$q2 = mysql_query("SELECT l.userid, o.ime, o.prezime FROM log AS l, osoba AS o WHERE l.userid=o.id AND l.dogadjaj='$logzapis2' AND UNIX_TIMESTAMP(l.vrijeme)={$row2['datum']}") or die ("SQL greska 3: ".mysql_error());
				if (mysql_num_rows($q2)>0) {
					$unosilac = mysql_result($q2,0,1)." ".mysql_result($q2,0,2);
				} 
				else {
					//echo "Problem sa povezivanjem unosioca ocjene jednog sloga.\n";
					$unosilac = "nepoznat unosilac";
				}
			}

			$message .= "$redniBroj. {$row2['prezime']} {$row2['ime']} ({$row2['brindexa']}), ";
			$message .= "ocjena: {$row2['ocjena']} ";
			$datum = date("d.m.Y. G:i", $row2['datum']);
			$message .= "($datum, $unosilac)";
			$message .= "\n";
			$redniBroj++;
		}                                             //zatvori while($row2)
		print " ".($redniBroj-1)." ocjena.";
	} // while ($r100)
	
	$message .= "\n";
	$message .= "Ugodan ostatak dana, \n";
	$message .= "Zamger@ETF.";

	if ($biloOcjena) {
		//echo onoga sto je poslano na screen!
		if ($prikaziMailove) {
			echo "\n-------------------------------------------------------------------------------\n";
			echo "Poslana poruka prof. {$row['ime']} {$row['prezime']} na adresu: {$row['email']} sa sadrzajem: \n";
			echo "- - - - - - - Pocetak poruke - - - - - - -\n";
			//echo str_replace("\\n", "\n", $message);
			echo $message;
			echo "\n- - - - - - - Kraj poruke - - - - - - -\n";
		}

		//slanje maila
		$to = $row['email'];
		//$to = "vljubovic@etf.unsa.ba";
		$subject = $subject;
		$body = $message;
		$headers = 'From: '. $conf_admin_email . "\r\n" .
		'Reply-To: ' . $conf_admin_email . "\r\n" .
		'Content-Type: text/plain; charset=utf-8' . "\r\n" .
		'Content-Transfer-Encoding: 8bit' . "\r\n";
		if (!$dryRun && mail($to, $subject, $body, $headers)) {
			echo "\n! Mail poslan.\n";
		} else {
			echo "\n! Greska pri slanju maila.\n";
		}

	} //zatvori if

	else {
		echo "\n! Poruka na adresu {$row['email']} nije poslana - nema unesenih ocjena.\n";
	}
} //zatvori while($row)



} else echo 'Onemoguceno slanje emaila. Molimo provjerite vase konfiguracijske postavke u config.php\n';
?> 
