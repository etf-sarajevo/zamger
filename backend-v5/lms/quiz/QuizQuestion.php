<?php

// Modul: lms/quiz
// Klasa: QuizQuestion
// Opis: pitanje na kvizu


require_once(Config::$backend_path."lms/quiz/QuizAnswer.php");

class QuizQuestion {
	public $id;
	public $Quiz, $type, $text, $score, $visible;
	
	public static function fromId($id) {
		$qq = DB::query_assoc("SELECT id, kviz Quiz, tip type, tekst text, bodova score, vidljivo visible FROM kviz_pitanje WHERE id=$id");
		if (!$qq) throw new Exception("Unknown quiz question $id", "404");
		
		$qq = Util::array_to_class($qq, "QuizQuestion", array("Quiz"));
		if ($qq->visible == 1) $qq->visible=true; else $qq->visible=false; // FIXME use boolean in database
		$qq->answers = QuizAnswer::forQuestion($id);
		return $qq;
	}
	
	public static function forQuiz($quizId) {
		$questions = DB::query_varray("SELECT id FROM kviz_pitanje WHERE kviz=$quizId");
		foreach ($questions as &$q) 
			$q = new UnresolvedClass("QuizQuestion", $q, $q);
		return $questions;
	}
	
	public static function forQuizQuiz($quizId, $limit = 0, $randomize = false) {
		if ($limit > 0) $limitsql = "LIMIT $limit"; else $limitsql = "";
		if ($randomize) $randsql = "ORDER BY RAND()"; else $randsql = "";
		
		$questions = DB::query_table("SELECT id, kviz Quiz, tip type, tekst text, bodova score, vidljivo visible FROM kviz_pitanje WHERE kviz=$quizId AND vidljivo=1 $randsql $limitsql");
		foreach ($questions as &$qq) {
			$qq = Util::array_to_class($qq, "QuizQuestion", array("Quiz"));
			if ($qq->visible == 1) $qq->visible=true; else $qq->visible=false; // FIXME use boolean in database
			$qq->answers = QuizAnswer::forQuestionQuiz($qq->id);
		}
		return $questions;
	}
}

?>
