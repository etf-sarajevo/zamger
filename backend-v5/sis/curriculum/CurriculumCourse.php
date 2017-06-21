<?php

// Modul: sis/curriculum
// Klasa: CurriculumCourse
// Opis: Curriculum je u biti kolekcija objekata tipa CurriculumCourse


require_once(Config::$backend_path."sis/curriculum/CourseDescription.php");
require_once(Config::$backend_path."sis/curriculum/ElectiveSlot.php");

class CurriculumCourse {
	// If course is mandatory, CourseDescription is set and ElectiveSlot is null
	// If it's elective, it's the other way round
	public $Curriculum, $CourseDescription, $ElectiveSlot, $semester, $mandatory, $confirmed;
	
	public static function forCurriculum($curriculumId) {
		$courses = DB::query_table("SELECT plan_studija Curriculum, pasos_predmeta CourseDescription, plan_izborni_slot ElectiveSlot, semestar semester, obavezan mandatory, potvrdjen confirmed FROM plan_studija_predmet WHERE plan_studija=$curriculumId ORDER BY semestar, obavezan DESC, pasos_predmeta, plan_izborni_slot"); // FIXME: this is sorted by ids - fix in UI?
		foreach ($courses as &$course) {
			$course = Util::array_to_class($course, "CurriculumCourse", array("Curriculum", "CourseDescription", "ElectiveSlot"));
			if ($course->mandatory == 1) $course->mandatory=true; else $course->mandatory=false; // FIXME use boolean in database
			if ($course->confirmed == 1) $course->confirmed=true; else $course->confirmed=false;
			if ($course->mandatory) unset($course->ElectiveSlot); else unset($course->CourseDescription);
		}
		return $courses;
	}
}


?>
