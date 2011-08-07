<?php

// Modul: lms/quiz
// Klasa: QuizQuestion
// Opis: pitanje na kvizu


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");

class QuizQuestion {
	public $id;
	public $quizId, $type, $text, $score, $visible;
	
	public static function fromId($id) {
		$q10 = DB::query("select kviz, tip, tekst, bodova, vidljivo from kviz_pitanje where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("no such question");
		}
		$qq = new QuizQuestion;
		$qq->id = $id;
		$qq->quizId = mysql_result($q10,0,0);
		$qq->type = mysql_result($q10,0,1);
		$qq->text = mysql_result($q10,0,2);
		$qq->score = mysql_result($q10,0,3);
		if (mysql_result($q10,0,4) == 1) $qq->visible = true; else $qq->visible = false;
		
		return $qq;
	}
	
	public static function getAllForQuiz($quizId, $limit = 0, $randomize = false) {
		if ($limit > 0) $limitsql = "LIMIT $limit"; else $limitsql = "";
		if ($randomize) $randsql = "ORDER BY RAND()"; else $randsql = "";

		$q10 = DB::query("SELECT id, tip, tekst, bodova, vidljivo from kviz_pitanje where kviz=$quizId $randsql $limitsql");
		$questions = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$qq = new QuizQuestion;
			$qq->id = $r10[0];
			$qq->quizId = $quizId;
			$qq->type = $r10[1];
			$qq->text = $r10[2];
			$qq->score = $r10[3];
			if ($r10[4] == 1) $qq->visible = true; else $qq->visible = false;
		
			array_push($questions, $qq);
		}
		
		return $questions;
	}
}

?>
