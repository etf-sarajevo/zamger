<?php

// Modul: lms/quiz
// Klasa: QuizResult
// Opis: rezultat jednog studenta na kvizu


class QuizResult {
	public $student, $Quiz, $finished, $score, $timeActivated;
	
	public static function fromStudentAndQuiz($studentId, $quizId) {
		$qr = DB::query_assoc("SELECT student, kviz Quiz, dovrsen finished, bodova score, UNIX_TIMESTAMP(vrijeme_aktivacije) timeActivated FROM kviz_student WHERE student=$studentId AND kviz=$quizId");
		if (!$qr) throw new Exception("Unknown quiz result for student $studentId and quiz $quizId", "404");
		
		$qr = Util::array_to_class($qr, "QuizResult", array("Quiz"));
		$qr->student = new UnresolvedClass("Person", $qr->student, $qr->student);
		if ($qr->finished == 1) $qr->finished=true; else $qr->finished=false; // FIXME use boolean in database
		return $qr;
	}
	
	public function update() {
		if ($this->finished) $finsql="1"; else $finsql="0";
		DB::query("UPDATE kviz_student SET dovrsen=$finsql, bodova=".$this->score.", vrijeme_aktivacije=FROM_UNIXTIME(".$this->timeActivated.") WHERE student=".$this->studentId." and kviz=".$this->quizId);
	}
	
	public function add() {
		if ($this->finished) $finsql="1"; else $finsql="0";
		DB::query("INSERT INTO kviz_student SET student=".$this->studentId.", kviz=".$this->quizId.", dovrsen=$finsql, bodova=".$this->score.", vrijeme_aktivacije=NOW()");
	}
	
	public function delete() {
		DB::query("DELETE FROM kviz_student WHERE student=".$this->studentId." and kviz=".$this->quizId);
	}
}

?>
