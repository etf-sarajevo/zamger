<?php
//dobavljanje parametara sa html forme
$subject = "Zamger: Dnevni izvjestaj upisanih ocjena" ;

//kreiranje konekcije na bazu
$link = mysql_connect('mysql2.com.ba', 'mostic', 'mersiha');
if (!$link) {
    die('<i>Konekcija na Zamger bazu nije uspjela; razlog: </i>' . mysql_error());
}
else {
echo '<i>Uspjesna konekcija na Zamger bazu.</i>';
echo "<br>";
echo "<br>";
}

//dobavljanje liste profesora koji su angazovani sa njihovim ID-om i EMAILom
$result = mysql_query('SELECT DISTINCT o.id,o.ime,o.prezime,o.email FROM zamger.osoba o INNER JOIN zamger.angazman a ON a.osoba = o.id WHERE a.angazman_status = 1');

if (!$result) {
    die('Invalid query: ' . mysql_error());
}

while ($row = mysql_fetch_assoc($result)) { //otvori while($row)
$message = "Postovani/a prof. {$row['prezime']}, \\n";
$message .= "U nastavku maila se nalazi spisak unesenih ocjena na predmetima koje Vi predajete, a unesene su unutar prethodna 24 sata. \\n";

//dobavljanje podataka o ocjenama unesenim u prethodna 24 sata
$result2 = mysql_query("SELECT o.id AS student_id,o.ime AS student_ime,o.prezime AS student_prezime,o.brindexa AS student_brindexa,o.email AS student_email,p.id AS predmet_id,p.naziv AS predmet_naziv,ko.ocjena AS ocjena,ko.datum AS datum,os.id AS nastavnik_id,os.ime AS nastavnik_ime,os.prezime as nastavnik_prezime,l.userid AS unosilac_id,unosilac.ime AS unosilac_ime,unosilac.prezime AS unosilac_prezime 
FROM zamger.osoba o INNER JOIN zamger.konacna_ocjena ko ON ko.student = o.id INNER JOIN zamger.predmet p ON ko.predmet = p.id INNER JOIN zamger.angazman a ON a.predmet = p.id INNER JOIN zamger.angazman_status a_st ON a.angazman_status = a_st.id INNER JOIN zamger.osoba os ON a.osoba = os.id INNER JOIN zamger.log l INNER JOIN zamger.osoba unosilac ON l.userid = unosilac.id 
WHERE DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= ko.datum 
AND a_st.id = 1 
AND os.id = {$row['id']} 
AND substr(l.dogadjaj,locate('pp',l.dogadjaj)+2,locate(',',l.dogadjaj)-(locate('pp',l.dogadjaj)+2)) = p.id 
AND substr(l.dogadjaj,locate(' u',l.dogadjaj)+2,locate(')',l.dogadjaj)-(locate(' u',l.dogadjaj)+2)) = o.id 
AND substr(l.dogadjaj,locate('ocjena',l.dogadjaj)+7,locate(' (',l.dogadjaj)-(locate('ocjena',l.dogadjaj)+7)) = ko.ocjena 
AND DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= l.vrijeme 
ORDER BY predmet_naziv ASC,ocjena DESC,student_prezime ASC,unosilac_prezime ASC");

if (!$result2) {
    die('Invalid query: ' . mysql_error());
}

if (mysql_num_rows($result2) > 0) { //otvori if
$redniBroj = 1;
$nazivPredmeta = "";

//kreiranje body-ja emaila
while ($row2 = mysql_fetch_assoc($result2)) { //otvori while($row2)
	if ($row2['predmet_naziv'] != $nazivPredmeta) {
		$message.="\\n";
		$nazivPredmeta = $row2['predmet_naziv'];
		$message.="Predmet: $nazivPredmeta \\n";
		$redniBroj = 1;
		}
	$message.="$redniBroj. {$row2['student_ime']} {$row2['student_prezime']}, indeks broj {$row2['student_brindexa']} ";
	$message.="je upisao/-la ocjenu: {$row2['ocjena']} ";
	$datum = date("j.m. G:i", strtotime($row2['datum']));
	$message.="(upisano: $datum, {$row2['unosilac_ime']} {$row2['unosilac_prezime']})";
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

//zatvaranje konekcije na bazu
if ($link) {
mysql_close($link);
echo '<i>Konekcija na Zamger bazu terminirana.</i>';
}

?> 