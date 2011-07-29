<?php

// Modul: lms/exam
// Klasa: Exam
// Opis: jedan ispit


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/ScoringElement.php");

class Exam {
	public $id;
	public $courseUnitId, $academicYearId, $date, $publishedDateTime, $scoringElementId;
	public $courseUnit, $academicYear, $scoringElement;
	
	public static function fromId($id) {
		$q10 = DB::query("select i.predmet, i.akademska_godina, UNIX_TIMESTAMP(i.datum), UNIX_TIMESTAMP(i.vrijemeobjave), i.komponenta, k.gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija, k.uslov from ispit as i, komponenta as k where i.id=$id and i.komponenta=k.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepoznat ispit");
		}
		$e = new Exam;
		$e->id = $id;
		$e->courseUnitId = mysql_result($q10,0,0);
		$e->academicYearId = mysql_result($q10,0,1);
		$e->date = mysql_result($q10,0,2);
		$e->publishedDateTime = mysql_result($q10,0,3);
		$e->scoringElementId = mysql_result($q10,0,4);

		$e->scoringElement = new ScoringElement;
		$e->scoringElement->guiName = mysql_result($q10,0,5);
		$e->scoringElement->type = mysql_result($q10,0,6);
		$e->scoringElement->max = mysql_result($q10,0,7);
		$e->scoringElement->pass = mysql_result($q10,0,8);
		$e->scoringElement->option = mysql_result($q10,0,9);
		if (mysql_result($q10,0,10) == 1) $e->scoringElement->mandatory = true; else $e->scoringElement->mandatory = false;

		// To be instatiated as neccessary
		$e->courseUnit = 0;
		$e->academicYear = 0;

		return $e;
	}
	
	// List of exams held on a course, ordered by date
	public static function fromCourse($courseUnitId, $academicYearId) {
		$q10 = DB::query("select i.id, UNIX_TIMESTAMP(i.datum), UNIX_TIMESTAMP(i.vrijemeobjave), i.komponenta, k.gui_naziv, k.tipkomponente, k.maxbodova, k.prolaz, k.opcija, k.uslov from ispit as i, komponenta as k where i.predmet=$courseUnitId and i.akademska_godina=$academicYearId and i.komponenta=k.id order by i.datum, i.komponenta");
		$exams = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$e = new Exam;
			$e->id = $r10[0];
			$e->courseUnitId = $courseUnitId;
			$e->academicYearId = $academicYearId;
			$e->date = $r10[1];
			$e->publishedDateTime = $r10[2];
			$e->scoringElementId = $r10[3];
	
			$e->scoringElement = new ScoringElement;
			$e->scoringElement->guiName = $r10[4];
			$e->scoringElement->type = $r10[5];
			$e->scoringElement->max = $r10[6];
			$e->scoringElement->pass = $r10[7];
			$e->scoringElement->option = $r10[9];
			if ($r10[10] == 1) $e->scoringElement->mandatory = true; else $e->scoringElement->mandatory = false;
	
			// To be instatiated as neccessary
			$e->courseUnit = 0;
			$e->academicYear = 0;

			array_push($exams, $e);
		}

		return $exams;
	}
}

?>
