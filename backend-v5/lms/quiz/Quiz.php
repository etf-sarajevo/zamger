<?php

// Modul: lms/quiz
// Klasa: Quiz
// Opis: kvizovi


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");

class Quiz {
	public $id;
	public $name, $courseUnitId, $academicYearId, $groupId, $scoringElementId, $timeBegin, $timeEnd, $active;
	// $zclassId -- dodati link na čas umjesto kako je sada, link sa časa na kviz
	public $courseUnit;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, predmet, akademska_godina, labgrupa, UNIX_TIMESTAMP(vrijeme_pocetak), UNIX_TIMESTAMP(vrijeme_kraj), aktivan from kviz where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepoznat kviz");
		}
		$q = new Quiz;
		$q->id = $id;
		$q->name = mysql_result($q10,0,0);
		$q->courseUnitId = mysql_result($q10,0,1);
		$q->academicYearId = mysql_result($q10,0,2);
		$q->groupId = mysql_result($q10,0,3);
		$q->timeBegin = mysql_result($q10,0,4);
		$q->timeEnd = mysql_result($q10,0,5);
		if (mysql_result($q10,0,5) == 1) $q->active = true; else $q->active = false;
		// TODO dodati ostalo
		
		$q->courseUnit = 0;
		
		return $q;
	}
	
	// Gets no more than $limit quizzes, ordered by deadline descending
	public static function getLatestForStudent($student, $limit) {
		$q10 = DB::query("
SELECT k.id, k.naziv, k.predmet, k.akademska_godina, UNIX_TIMESTAMP(k.vrijeme_pocetak), k.labgrupa, p.naziv FROM kviz as k, student_predmet as sp, ponudakursa as pk, predmet as p 
WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=k.predmet AND pk.predmet=p.id AND pk.akademska_godina=k.akademska_godina AND k.vrijeme_pocetak<NOW() AND k.vrijeme_kraj>NOW() AND k.aktivan=1
ORDER BY k.vrijeme_pocetak DESC
LIMIT $limit");
		$quizes = array();
		while ($r10 = mysql_fetch_row($q10)) {
			// Skip if student has passing grade (optimize?)
			$q15a = DB::query("select count(*) from konacna_ocjena where predmet=$r15[2] and ocjena>=6");
			if (mysql_result($q15a,0,0)>0) continue;

			// Skip if group defined and student not a member
			if ($r10[5] != 0) {
				// If defined, we can assume that module lms/attendance is installed
				require_once(Config::$backend_path."lms/attendance/Group.php");
				$g = new Group;
				$g->id = $k->groupId;
				if (!$g->isMember($student)) continue; // Not a member
			}

			$q = new Quiz;
			$q->id = $r10[0];
			$q->name = $r10[1];
			$q->courseUnitId = $r10[2];
			$q->academicYearId = $r10[3];
			$q->timeBegin = $r10[4];
			$q->groupId = $r10[5];
			
			$q->courseUnit = new CourseUnit;
			$q->courseUnit->id = $r10[2];
			$q->courseUnit->name = $r10[6];
			// TODO dodati ostalo
		
			array_push($quizes, $q);
		}
		
		return $quizes;
	}
}

?>
