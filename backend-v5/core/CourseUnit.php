<?php

// Modul: core
// Klasa: CourseUnit
// Opis: predmet


class CourseUnit {
	public $id;
	public $code, $name, $abbrev, $ects, $Institution, $lectureHours, $tutorialHours, $practiceHours;
	
	public static function fromId($id) {
		$course = DB::query_assoc("select id, sifra code, naziv name, kratki_naziv abbrev, ects, institucija Institution, sati_predavanja lectureHours, sati_tutorijala tutorialHours, sati_vjezbi practiceHours from predmet where id=$id");
		if (!$course) throw new Exception("Unknown course unit", "404");
		return Util::array_to_class($course, "CourseUnit", array("Institution"));
	}
}
