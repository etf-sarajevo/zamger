<?php

// Modul: lms/exam
// Klasa: Exam
// Opis: jedan ispit



class Exam {
	public $id;
	public $CourseUnit, $AcademicYear, $date, $publishedDateTime, $ScoringElement;
	
	public static function fromId($id) {
		$exam = DB::query_assoc("SELECT id, predmet CourseUnit, akademska_godina AcademicYear, UNIX_TIMESTAMP(datum) date, UNIX_TIMESTAMP(vrijemeobjave) publishedDateTime, komponenta ScoringElement FROM ispit WHERE id=$id");
		if (!$exam) throw new Exception("Unknown exam $id", "404");
		
		return Util::array_to_class($exam, "Exam", array("CourseUnit", "AcademicYear", "ScoringElement"));
	}
	
	// List of exams held on a course, ordered by date
	public static function fromCourseAndYear($courseUnitId, $academicYearId=0) {
		if ($academicYearId == 0)
			$exams = DB::query_table("SELECT i.id id, i.predmet CourseUnit, i.akademska_godina AcademicYear, UNIX_TIMESTAMP(i.datum) date, UNIX_TIMESTAMP(i.vrijemeobjave) publishedDateTime, i.komponenta ScoringElement FROM ispit i, akademska_godina ag WHERE i.predmet=$courseUnitId AND i.akademska_godina=ag.id AND ag.aktuelna=1 ORDER BY date, ScoringElement");
		else
			$exams = DB::query_table("SELECT id, predmet CourseUnit, akademska_godina AcademicYear, UNIX_TIMESTAMP(datum) date, UNIX_TIMESTAMP(vrijemeobjave) publishedDateTime, komponenta ScoringElement FROM ispit WHERE predmet=$courseUnitId AND akademska_godina=$academicYearId ORDER BY date, ScoringElement");
		
		foreach($exams as &$exam)
			$exam = Util::array_to_class($exam, "Exam", array("CourseUnit", "AcademicYear", "ScoringElement"));
		return $exams;
	}
}

?>
