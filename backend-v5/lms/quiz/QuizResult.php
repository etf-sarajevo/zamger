<?php

// Modul: lms/quiz
// Klasa: QuizResult
// Opis: rezultat jednog studenta na kvizu


class QuizResult {
	public $student, $Quiz, $finished, $score, $timeActivated, $token;

	const TOKEN_LENGTH = 20; // Lenght of quiz token string
	
	public static function fromStudentAndQuiz($studentId, $quizId) {
		$qr = DB::query_assoc("SELECT student, kviz Quiz, dovrsen finished, bodova score, UNIX_TIMESTAMP(vrijeme_aktivacije) timeActivated, token FROM kviz_student WHERE student=$studentId AND kviz=$quizId");
		if (!$qr) throw new Exception("Student $studentId never took quiz $quizId", "404");
		
		$qr = Util::array_to_class($qr, "QuizResult", array("Quiz"));
		$qr->student = new UnresolvedClass("Person", $qr->student, $qr->student);
		if ($qr->finished == 1) $qr->finished=true; else $qr->finished=false; // FIXME use boolean in database
		return $qr;
	}
	
	// Creates new QuizResult, sets finished status to false (student started taking the quiz)
	public static function create($studentId, $quizId) {
		$qr = new QuizResult;
		$qr->student = new UnresolvedClass("Person", $studentId, $qr->student);
		$qr->Quiz = new UnresolvedClass("Quiz", $quizId, $qr->Quiz);
		$qr->finished = false;
		$qr->score = 0;

		// Find a unique token
		do {
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			$token = "";
			for ($i=0; $i<QuizResult::TOKEN_LENGTH; $i++) 
				$token .= $characters[rand(0,strlen($characters))];
			$token_exists = DB::query("SELECT COUNT(*) FROM kviz_student WHERE token='$token'");
		} while ($token_exists > 0);

		$qr->token = $token;
		$qr->add();
		return $qr;
	}
	
	public function update() {
		if ($this->finished) $finsql="1"; else $finsql="0";
		DB::query("UPDATE kviz_student SET dovrsen=$finsql, bodova=".$this->score.", vrijeme_aktivacije=FROM_UNIXTIME(".$this->timeActivated."), token='".$this->token."' WHERE student=".$this->studentId." and kviz=".$this->quizId);
	}

	// Add method is private - we want others to use QuizResult::create()
	private function add() {
		if ($this->finished) $finsql="1"; else $finsql="0";
		DB::query("INSERT INTO kviz_student SET student=".$this->studentId.", kviz=".$this->quizId.", dovrsen=$finsql, bodova=".$this->score.", vrijeme_aktivacije=NOW(), token='".$this->token."'");
	}
	
	public function delete() {
		DB::query("DELETE FROM kviz_student WHERE student=".$this->studentId." and kviz=".$this->quizId);
	}
}

?>
