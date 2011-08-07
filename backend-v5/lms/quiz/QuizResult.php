<?php

// Modul: lms/quiz
// Klasa: QuizResult
// Opis: rezultat jednog studenta na kvizu


require_once(Config::$backend_path."core/DB.php");

class QuizResult {
	public $studentId, $quizId, $finished, $score, $timeActivated;
	
	public static function fromStudentAndQuiz($studentId, $quizId) {
		$q10 = DB::query("select dovrsen, bodova, UNIX_TIMESTAMP(vrijeme_aktivacije) from kviz_student where student=$studentId and kviz=$quizId");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("this student didn't take the quiz");
		}
		$qr = new QuizResult;
		$qr->studentId = $studentId;
		$qr->quizId = $quizId;
		if (mysql_result($q10,0,0) == 1) $qr->finished = true; else $qr->finished = false;
		$qr->score = mysql_result($q10,0,1);
		$qr->timeActivated = mysql_result($q10,0,2);
		
		return $qr;
	}
	
	public function update() {
		if ($this->finished) $finsql="1"; else $finsql="0";
		$q10 = DB::query("update kviz_student set dovrsen=$finsql, bodova=".$this->score.", vrijeme_aktivacije=FROM_UNIXTIME(".$this->timeActivated.") where student=".$this->studentId." and kviz=".$this->quizId);
	}
	
	public function add() {
		if ($this->finished) $finsql="1"; else $finsql="0";
		$q10 = DB::query("insert into kviz_student set student=".$this->studentId.", kviz=".$this->quizId.", dovrsen=$finsql, bodova=".$this->score.", vrijeme_aktivacije=NOW()");
	}
}

?>
