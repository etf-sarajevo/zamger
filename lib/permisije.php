<?

// LIB/PERMISIJE - funkcije za provjeru prava pristupa modulima


require_once("lib/student_predmet.php"); // zbog student_labgrupe, osim toga daj_ponudu_kursa je jedna vrsta provjere permisija

// Da li nastavnik ima pravo pristupa podacima studenta na predmetu i akademskoj godini
// Ako je $student=0, odnosi se na sve studente
function nastavnik_pravo_pristupa($predmet, $ag, $student=0) {
	global $userid;

	$q20 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q20)>0) {
		$ok = true;
		// Postoji li ograničenje na tom predmetu
		if (db_result($q20,0,0) == "asistent") {
			$labgrupe = student_labgrupe($student, $predmet, $ag);
			$ok = nastavnik_ogranicenje($predmet, $ag, $student);
		}
	}
	return $ok;
}


// Da li nastavnik ima ograničenje na labgrupu u kojoj je student
function nastavnik_ogranicenje($predmet, $ag, $student=0) {
	global $userid;

	$q50 = db_query("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	if (db_num_rows($q50) < 1) return true;
	if ($student == 0) return false;
	
	$labgrupe = student_labgrupe($student, $predmet, $ag, false);
	if (count($labgrupe) == 0) return false;
	
	while ($r50 = db_fetch_row($q50))
		foreach($labgrupe as $lg)
			if ($r50[0] == $lg) return true;
	
	return false;
}


?>
