<?

// IZVJESTAJ/DUPLE_PRIJAVE - studenti prijavljeni na više ispita u istom roku



function izvjestaj_duple_prijave() {


global $userid, $user_studentska, $user_siteadmin;


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
