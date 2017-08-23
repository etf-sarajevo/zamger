<?

// LIB/PLAN_STUDIJA - funkcije za rad sa planom studija 


// Ova funkcija vraća asocijativni niz sa razrađenim planom studija
// Njena osnovna funkcionalnost je da "spljošti" izborne slotove uz izbjegavanje duplikata,
// što se ne može SQLom ili ja ne znam kako
function predmeti_na_planu($plan_studija, $semestar) 
{
	$rezultat = array();
	$q10 = db_query("select semestar, obavezan, pasos_predmeta, plan_izborni_slot from plan_studija_predmet where plan_studija=$plan_studija and semestar=$semestar");
	while (db_fetch4($q10, $semestar, $obavezan, $pasos_predmeta, $plan_izborni_slot)) {
		if ($obavezan == 1) { // obavezan
			$rezultat[] = db_query_assoc("SELECT predmet, naziv, ects, 1 obavezan FROM pasos_predmeta WHERE id=$pasos_predmeta");
			
		} else { // izborni
			// uzimamo sve predmete u slotu $plan_izborni_slot
			$q20 = db_query("select pp.predmet, pp.naziv, pp.ects from pasos_predmeta as pp, plan_izborni_slot as pis where pis.id=$plan_izborni_slot and pis.pasos_predmeta=pp.id");
			while ($predmet = db_fetch_assoc($q20)) {
				// Nećemo više puta kreirati isti predmet
				$predmet['obavezan'] = 0;
				if (in_array($predmet, $rezultat)) continue;
				$rezultat[] = $predmet;
			}
		}
	}
	return $rezultat;
}

?>


