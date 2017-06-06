<?php

// Modul: core
// Klasa: CourseOffering
// Opis: ponuda kursa na datom studiju i semestru u datoj ak. godini


class CourseOffering {
	public $id;
	public $CourseUnit, $AcademicYear, $Programme, $semester, $mandatory;
	
	public static function fromId($id) {
		$co = DB::query_assoc("SELECT id, predmet CourseUnit, akademska_godina AcademicYear, studij Programme, semestar semester, obavezan mandatory FROM ponudakursa WHERE id=$id");
		
		if (!$co) throw new Exception("Unknown course offer", "404");
		$co = Util::array_to_class($co, "CourseOffering", array("CourseUnit", "AcademicYear", "Programme"));
		if ($co->mandatory == 1) $co->mandatory=true; else $co->mandatory=false; // FIXME use boolean in database
		return $co;
	}

	// List of all offers for course and year
	// If year is ommitted or zero, return offers for all years
	public static function fromCourseAndYear($courseUnitId, $academicYearId=0) {
		$sql = "SELECT id, predmet CourseUnit, akademska_godina AcademicYear, studij Programme, semestar semester, obavezan mandatory FROM ponudakursa WHERE predmet=$courseUnitId";
		if ($academicYearId > 0) $sql .= " AND akademska_godina=$academicYearId";
		$sql .= " ORDER BY id";
		$course_offers = DB::query_table($sql);
		
		foreach($course_offers as &$co) {
			$co = Util::array_to_class($co, "CourseOffering", array("CourseUnit", "AcademicYear", "Programme"));
			if ($co->mandatory == 1) $co->mandatory=true; else $co->mandatory=false; // FIXME use boolean in database
		}
		return $course_offers;
	}

	// List all courses offered at specified academic year, programme and semester
	// ordered by name alphabetically
	// 0 means all
	public static function getCoursesOffered($academicYearId = 0, $programmeId = 0, $semestar = 0) {
		$sql = "";
		if ($academicYearId > 0) $sql .= " and pk.akademska_godina=$academicYearId";
		if ($programmeId > 0) $sql .= " and pk.studij=$programmeId";
		if ($semestar > 0) $sql .= " and pk.semestar=$semestar";

		$course_offers = DB::query_table("SELECT pk.id id, pk.predmet CourseUnit, pk.akademska_godina AcademicYear, pk.studij Programme, pk.semestar semester, pk.obavezan mandatory from predmet as p, ponudakursa as pk where pk.predmet=p.id $sql order by p.naziv");
		foreach($course_offers as &$co) {
			$co = Util::array_to_class($co, "CourseOffering", array("CourseUnit", "AcademicYear", "Programme"));
			if ($co->mandatory == 1) $co->mandatory=true; else $co->mandatory=false; // FIXME use boolean in database
		}
		return $course_offers;
	}

	// Get the specific offering of a course that student is enrolled in
	// Returns false if there is no match
	// This method is also efficient way to verify if student is enrolled to given course in given year
	public static function forStudent($studentId, $courseUnitId, $academicYearId) {
		$co = DB::query_assoc("SELECT pk.id id, pk.predmet CourseUnit, pk.akademska_godina AcademicYear, pk.studij Programme, pk.semestar semester, pk.obavezan mandatory FROM ponudakursa pk, student_predmet sp WHERE pk.predmet=$courseUnitId AND pk.akademska_godina=$academicYearId AND pk.id=sp.predmet AND sp.student=$studentId");
		if (!$co) return false;
		
		$co = Util::array_to_class($co, "CourseOffering", array("CourseUnit", "AcademicYear", "Programme"));
		if ($co->mandatory == 1) $co->mandatory=true; else $co->mandatory=false; // FIXME use boolean in database
		return $co;
	}

}
