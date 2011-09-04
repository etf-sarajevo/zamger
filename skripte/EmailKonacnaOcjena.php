<?php
require("../lib/libvedran.php");
require("../lib/zamger.php");
require("../lib/config.php");

if($conf_email) {


//dobavljanje parametara sa html forme
$subject = "Zamger: Dnevni izvjestaj upisanih ocjena" ;

//kreiranje konekcije na bazu



dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


//dobavljanje liste profesora koji su angazovani sa njihovim ID-om i EMAILom
$result = mysql_query('SELECT DISTINCT o.id,o.ime,o.prezime,o.email FROM zamger.osoba o INNER JOIN zamger.angazman a ON a.osoba = o.id WHERE a.angazman_status = 1');

if (!$result) {
    die('Invalid query: ' . mysql_error());
}

while ($row = mysql_fetch_assoc($result)) { //otvori while($row)
$message = "Postovani/a prof. {$row['prezime']}, \\n";
$message .= "U nastavku maila se nalazi spisak unesenih ocjena na predmetima koje Vi predajete, a unesene su unutar prethodna 24 sata. \\n";

//dobavljanje podataka o ocjenama unesenim u prethodna 24 sata
$result2 = mysql_query("SELECT o.id AS student_id,o.ime AS student_ime,o.prezime AS student_prezime,o.brindexa AS student_brindexa,o.email AS student_email,p.id AS predmet_id,p.naziv AS predmet_naziv,ko.ocjena AS ocjena,ko.datum AS datum,os.id AS nastavnik_id,os.ime AS nastavnik_ime,os.prezime as nastavnik_prezime
FROM zamger.osoba o INNER JOIN zamger.konacna_ocjena ko ON ko.student = o.id INNER JOIN zamger.predmet p ON ko.predmet = p.id INNER JOIN zamger.angazman a ON a.predmet = p.id INNER JOIN zamger.angazman_status a_st ON a.angazman_status = a_st.id INNER JOIN zamger.osoba os ON a.osoba = os.id 
WHERE DATE_SUB(NOW(),INTERVAL 1 DAY) <= ko.datum 
AND a_st.id = 1 
AND os.id = {$row['id']} 
ORDER BY predmet_naziv ASC,ocjena DESC,student_prezime ASC");

if (!$result2) {
    die('Invalid query: ' . mysql_error());
}

if (mysql_num_rows($result2) > 0) { //otvori if
$redniBroj = 1;
$nazivPredmeta = "";

//kreiranje body-ja emaila
while ($row2 = mysql_fetch_assoc($result2)) { //otvori while($row2)
        $logzapis = "dodana ocjena ".$row2["ocjena"]." (predmet pp".$row2["predmet_id"].", student u".$row2["student_id"].")";

        $q = mysql_query("SELECT l.userid, o.ime, o.prezime FROM zamger.log l INNER JOIN zamger.osoba o on l.userid=o.id WHERE l.dogadjaj LIKE '%{$logzapis}%' AND DATE_SUB(NOW(),INTERVAL 1 DAY) <= l.vrijeme");

if (!$q) {
    die('Invalid query: ' . mysql_error());
}
        if (mysql_num_rows($q)>0) {
	        $unosilac = mysql_result($q,0,1)." ".mysql_result($q,0,2);
	} else {
                $logzapis2 = " u ".$row2["ocjena"]." (predmet pp".$row2["predmet_id"].", student u".$row2["student_id"].")";
                $q2 = mysql_query("SELECT l.userid, o.ime, o.prezime FROM zamger.log l INNER JOIN zamger.osoba o on l.userid=o.id WHERE l.dogadjaj LIKE '%{$logzapis2}%' AND DATE_SUB(NOW(),INTERVAL 1 DAY) <= l.vrijeme");
if (!$q2) {
    die('Invalid query: ' . mysql_error());
}
if (mysql_num_rows($q2)>0) {
	        $unosilac = mysql_result($q2,0,1)." ".mysql_result($q2,0,2);
	} else {
                echo "Problem sa povezivanjem unosioca ocjene jednog sloga.";
               }
        }

	if ($row2['predmet_naziv'] != $nazivPredmeta) {
		$message.="\\n";
		$nazivPredmeta = $row2['predmet_naziv'];
		$message.="Predmet: $nazivPredmeta \\n";
		$redniBroj = 1;
		}
	$message.="$redniBroj. {$row2['student_ime']} {$row2['student_prezime']}, indeks broj {$row2['student_brindexa']} ";
	$message.="je upisao/-la ocjenu: {$row2['ocjena']} ";
	$datum = date("j.m. G:i", strtotime($row2['datum']));
	$message.="(upisano: $datum, $unosilac)";
	$message.="\\n";
	$redniBroj++;

}                                             //zatvori while($row2)
	$message.="\\n";
	$message.="Ugodan ostatak dana, \\n";
	$message.="Zamger@ETF.";

//echo onoga sto je poslano na screen!
echo "---------------------------------------------------------------------------------------------<br />";
echo "<i>Poslana poruka prof. {$row['ime']} {$row['prezime']} na adresu: {$row['email']} sa sadrzajem: </i><br />";
echo "<i>- - - - - - - Pocetak poruke - - - - - - -</i>";
echo "$message";
echo "<br /><i>- - - - - - - Kraj poruke - - - - - - -</i><br />";

//slanje maila
$to = $row['email'];
$subject = $subject;
$body = $message;
if (mail($to, $subject, $body)) {
   echo '<i>Poruka je uspjesno poslana.</i>';
   echo "<br /><br />";
  } else {
   echo("<i>Message delivery failed...</i>");
   echo "<br /><br />";
  }

} //zatvori if
else {
  echo "<br /><i>Poruka prof. {$row['ime']} {$row['prezime']} na adresu {$row['email']} nije poslana jer ne postoji niti jedna unesena ocjena u prethodna 24 sata na predmetu/-ima za koji je odgovoran/-na profesor/-ica.</i>";
  echo "<br />";
}
} //zatvori while($row)


?> 
}
else echo '<i>Onemoguæeno slanje emaila. Molimo provjerite vaše konfiguracijske postavke u config.php</i>';
?> 