<?

// IZVJESTAJ/DUPLE_PRIJAVE - studenti prijavljeni na više ispita u istom roku



function izvjestaj_duple_prijave() {


global $userid, $user_studentska, $user_siteadmin;

?>

<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?


// Ulazni parametri
$dan     = int_param('dan');
$mjesec  = int_param('mjesec');
$godina  = int_param('godina');


// Prava pristupa
if (!$user_studentska && !$user_siteadmin) {
	biguglyerror("Nemate pravo pristupa ovom izvještaju");
	return;
}


$qispiti = db_query("SELECT id, predmet FROM ispit WHERE datum='$godina-$mjesec-$dan'");
if (db_num_rows($qispiti) == 0) {
	biguglyerror("Nema ispita na ovaj datum?");
	return;
}

$studenti = $duple_prijave = array();

while(db_fetch2($qispiti, $ispit, $predmet)) {
	if (!array_key_exists($predmet, $studenti))
		$studenti[$predmet] = array();
	
	$qtermin = db_query("SELECT id FROM ispit_termin WHERE ispit=$ispit");
	while(db_fetch1($qtermin, $termin)) {
		$qstudent = db_query("SELECT student FROM student_ispit_termin WHERE ispit_termin=$termin");
		while(db_fetch1($qstudent, $student)) {
			if (!in_array($student, $studenti[$predmet]))
				$studenti[$predmet][] = $student;
			
			foreach(array_keys($studenti) as $predmet2) {
				if ($predmet2 == $predmet) break;
				if (in_array($student, $studenti[$predmet2])) {
					// Postoji dupla prijava
					if (!array_key_exists($student, $duple_prijave))
						$duple_prijave[$student] = array( $predmet2 );
					else if (!in_array($predmet2, $duple_prijave[$student]))
						$duple_prijave[$student][] = $predmet2;
					if (!in_array($predmet, $duple_prijave[$student]))
						$duple_prijave[$student][] = $predmet;
				}
			}
		}
	}
}

?>
<ul>
<?

$imena_predmeta_cache = array();

if (int_param('po_predmetu')) {
	print "<h3>Broj prijavljenih studenata po predmetu</h3>\n";
	$aktuelna_ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
	$ispis_varijanta = array();
	foreach($studenti as $predmet => $sp) {
		$qp = db_query("SELECT s.naziv, pk.semestar, p.naziv FROM predmet p, ponudakursa pk, studij s WHERE p.id=$predmet AND p.id=pk.predmet AND pk.akademska_godina=$aktuelna_ag AND pk.studij=s.id LIMIT 1");
		$qpredmet = db_fetch_row($qp);
		$ispis_varijanta[$qpredmet[1]][$qpredmet[0]][] = $qpredmet[2] . " - ".count($sp);
	}
	ksort($ispis_varijanta);
	
	foreach($ispis_varijanta as $semestar => $ispis) {
		foreach ($ispis as $studij => $ispis2) {
			print "<p><b>$semestar. semestar, $studij</b><br>";
			foreach($ispis2 as $ispis3) {
				print "- $ispis3<br>\n";
			}
		}
	}
	exit(0);
}

print "<h3>Dvostruke prijave</h3>\n";

foreach($duple_prijave as $student => $predmeti) {
	$sd = db_query_assoc("SELECT ime, prezime, brindexa FROM osoba WHERE id=$student");
	?>
	<li><?=$sd['prezime']?> <?=$sd['ime']?> (<?=$sd['brindexa']?>): 
	<?
	foreach($predmeti as $predmet) {
		if (!array_key_exists($predmet, $imena_predmeta_cache))
			$imena_predmeta_cache[$predmet] = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
		print $imena_predmeta_cache[$predmet] . ", ";
	}
	?>
	</li>
	<?
}

?>
</ul>
<?

}

?>
