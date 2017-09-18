<?

// LIB/PLAN_STUDIJA - funkcije za rad sa planom studija 


// Ova funkcija vraća asocijativni niz sa razrađenim planom studija
// Njena osnovna funkcionalnost je da "spljošti" izborne slotove uz izbjegavanje duplikata,
// što se ne može SQLom ili ja ne znam kako
function predmeti_na_planu($plan_studija, $semestar=0) 
{
	$rezultati = array();
	
	$upit_dodaj = "";
	if ($semestar > 0) $upit_dodaj .= "AND semestar=$semestar";
	
	$q10 = db_query("SELECT semestar, obavezan, pasos_predmeta, plan_izborni_slot FROM plan_studija_predmet WHERE plan_studija=$plan_studija $upit_dodaj");
	while ($rezultat = db_fetch_assoc($q10)) {
		if ($rezultat['obavezan'] == 1) { // obavezan
			$rezultat['predmet'] = db_query_assoc("SELECT predmet id, naziv, ects FROM pasos_predmeta WHERE id=".$rezultat['pasos_predmeta']);
			$rezultati[] = $rezultat;
			
		} else { // izborni
			$pis = $rezultat['plan_izborni_slot'];
			
			// Da li je već bio?
			$pronadjen = false;
			foreach ($rezultati as &$slog) {
				if ($slog['plan_izborni_slot'] == $pis && $slog['semestar'] == $rezultat['semestar']) {
					$pronadjen = true;
					$slog['ponavljanja']++;
				}
			}
			
			if (!$pronadjen) {
				// uzimamo sve predmete u slotu $plan_izborni_slot
				$rezultat['predmet'] = db_query_table("select pp.predmet id, pp.naziv naziv, pp.ects ects from pasos_predmeta as pp, plan_izborni_slot as pis where pis.id=$pis and pis.pasos_predmeta=pp.id");
				$rezultat['ponavljanja'] = 1;
				$rezultati[] = $rezultat;
			}
		}
	}
	return $rezultati;
}

?>
