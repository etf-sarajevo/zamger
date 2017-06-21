<?php

// Modul: sis/curriculum
// Klasa: CourseDescription
// Opis: pasoš predmeta


class CourseDescription {
	public $id;
	public $CourseUnit, $accepted, $suggestedBy, $dateTimeSuggested, $suggestionComment, $code, $name, $nameEn, $ects, $lectureHours, $tutorialHours, $practiceHours, $courseOutcomes, $courseOutcomesEn, $moduleContent, $moduleContentEn, $recommendedLiterature, $additionalLiterature, $didacticMethods, $didacticMethodsEn, $assessment, $assessmentEn, $notes, $notesEn;
	
	public static function fromId($id) {
		$cd = DB::query_assoc("SELECT id, predmet CourseUnit, usvojen accepted, predlozio suggestedBy, UNIX_TIMESTAMP(vrijeme_prijedloga) dateTimeSuggested, komentar_prijedloga suggestionComment, sifra code, naziv name, naziv_en nameEn, ects, sati_predavanja lectureHours, sati_vjezbi practiceHours, sati_tutorijala tutorialHours, cilj_kursa courseOutcomes, cilj_kursa_en courseOutcomesEn, program moduleContent, program_en moduleContentEn, obavezna_literatura recommendedLiterature, dopunska_literatura additionalLiterature, didakticke_metode didacticMethods, didakticke_metode_en didacticMethodsEn, nacin_provjere_znanja assessment, nacin_provjere_znanja_en assessmentEn, napomene notes, napomene_en notesEn FROM pasos_predmeta WHERE id=$id");
		if (!$cd) throw new Exception("Unknown course description $id", "404");
		
		$cd = Util::array_to_class($cd, "CourseDescription", array("CourseUnit"));
		$cd->suggestedBy = new UnresolvedClass("Person", $cd->suggestedBy, $cd->suggestedBy);
		if ($cd->accepted == 1) $cd->accepted=true; else $cd->accepted=false; // FIXME use boolean in database
		
		// Hide information relevant only for studentska
		if (!AccessControl::privilege("studentska")) {
			unset($cd->suggestedBy);
			unset($cd->dateTimeSuggested);
			unset($cd->suggestionComment);
		}
		
		return $cd;
	}
}


?>
