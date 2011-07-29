<?php

// Modul: lms/homework
// Klasa: Assignment
// Opis: jedan zadatak u sklopu zadaće


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."core/AcademicYear.php");
require_once(Config::$backend_path."lms/homework/Homework.php");

class Assignment {
	public $id;
	public $homeworkId, $assignNo, $studentId, $status, $score, $time, $comment, $compileReport /* ovo treba spojiti sa comment */, $filename, $authorId;
	
	public static function fromId($id) {
/* nije napravljeno ništa... */
	}
	
	public static function fromStudentHomeworkNumber($studentId, $homeworkId, $assignmentNumber) {
		$q10 = DB::query("select id, status, bodova, UNIX_TIMESTAMP(vrijeme), komentar, izvjestaj_skripte, filename, userid from zadatak where student=$studentId and zadaca=$homeworkId and redni_broj=$assignmentNumber order by id desc limit 1"); // since this is a logging table, we need latest ID
		if (mysql_num_rows($q10)<1) {
			throw new Exception("no such assignment");
		}

		$a = new Assignment;
		$a->id = mysql_result($q10,0,0);
		$a->homeworkId = $homeworkId;
		$a->assignNo = $assignmentNumber;
		$a->studentId = $studentId;
		$a->status = mysql_result($q10,0,1);
		$a->score = mysql_result($q10,0,2);
		$a->time = mysql_result($q10,0,3);
		$a->comment = mysql_result($q10,0,4);
		$a->compileReport = mysql_result($q10,0,5);
		$a->filename = mysql_result($q10,0,6);
		$a->authorId = mysql_result($q10,0,7);
		// TODO dodati ostalo

		return $a;
	}

	// Puts data from attributes into database
	public function addAssignment() {
		$q10 = DB::query("insert into zadatak set zadaca=".$this->homeworkId.", redni_broj=".$this->assignNo.", student=".$this->studentId.", status=".$this->status.", bodova=".$this->score.", vrijeme=NOW(), komentar='".$this->comment."', izvjestaj_skripte='".$this->compileReport."', filename='".$this->filename."', userid=".$this->authorId);
		// Since this is a logging table, we will now find out ID and timestamp

		$q20 = DB::query("select id, UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=".$this->homeworkId." and redni_broj=".$this->assignNo." and student=".$this->studentId." and status=".$this->status." and bodova=".$this->score." and komentar='".$this->comment."' and izvjestaj_skripte='".$this->compileReport."' and filename='".$this->filename."' and userid=".$this->authorId." order by id desc limit 1");
		$this->id = mysql_result($q20,0,0);
		$this->time = mysql_result($q20,0,1);
	}
}

?>
