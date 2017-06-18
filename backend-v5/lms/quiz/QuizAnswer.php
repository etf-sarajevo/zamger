<?php

// Modul: lms/quiz
// Klasa: QuizAnswer
// Opis: odgovor na pitanje na kvizu


class QuizAnswer {
	public $id;
	public $QuizQuestion, $text, $correct, $visible;
	
	public static function fromId($id) {
		$qa = DB::query_assoc("SELECT id, kviz_pitanje QuizQuestion, tekst text, tacan correct, vidljiv visible FROM kviz_odgovor WHERE id=$id");
		if (!$qa) throw new Exception("Unknown quiz answer $id", "404");
		
		$qa = Util::array_to_class($qa, "QuizAnswer", array("QuizQuestion"));
		if ($qa->correct == 1) $qa->correct=true; else $qa->correct=false; // FIXME use boolean in database
		if ($qa->visible == 1) $qa->visible=true; else $qa->visible=false; // FIXME use boolean in database
		return $qa;
	}
	
	// Get answers to question
	public static function forQuestion($questionId) {
		$answers = DB::query_varray("SELECT id FROM kviz_odgovor WHERE kviz_pitanje=$questionId");
		foreach ($answers as &$qa)
			$qa = new UnresolvedClass("QuizAnswer", $qa, $qa);
		return $answers;
	}
	
	// Get answers to question in quiz mode (only visible, resolved, don't show which one is correct)
	public static function forQuestionQuiz($questionId) {
		$answers = DB::query_table("SELECT id, kviz_pitanje QuizQuestion, tekst text, vidljiv visible FROM kviz_odgovor WHERE kviz_pitanje=$questionId AND vidljiv=1 ORDER BY RAND()");
		foreach ($answers as &$qa) {
			$qa = Util::array_to_class($qa, "QuizAnswer", array("QuizQuestion"));
			if ($qa->visible == 1) $qa->visible=true; else $qa->visible=false; // FIXME use boolean in database
		}
		return $answers;
	}
}

?>
