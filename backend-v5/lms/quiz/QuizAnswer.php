<?php

// Modul: lms/quiz
// Klasa: QuizAnswer
// Opis: odgovor na pitanje na kvizu


require_once(Config::$backend_path."core/DB.php");

class QuizAnswer {
	public $id;
	public $questionId, $text, $correct, $visible;
	
	public static function fromId($id) {
		$q10 = DB::query("select kviz_pitanje, tekst, tacan, vidljiv from kviz_odgovor where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("no such answer");
		}
		$qa = new QuizAnswer;
		$qa->id = $id;
		$qa->questionId = mysql_result($q10,0,0);
		$qa->text = mysql_result($q10,0,1);
		if (mysql_result($q10,0,2) == 1) $qa->correct = true; else $qa->correct = false;
		if (mysql_result($q10,0,3) == 1) $qa->visible = true; else $qa->visible = false;
		
		return $qa;
	}
	
	public static function getAllForQuestion($questionId, $randomize = false) {
		if ($randomize) $randsql = "ORDER BY RAND()"; else $randsql = "";

		$q10 = DB::query("select id, tekst, tacan, vidljiv from kviz_odgovor where kviz_pitanje=$questionId $randsql");
		$answers = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$qa = new QuizAnswer;
			$qa->id = $r10[0];
			$qa->questionId = $questionId;
			$qa->text = $r10[1];
			if ($r10[2] == 1) $qa->correct = true; else $qa->correct = false;
			if ($r10[3] == 1) $qa->visible = true; else $qa->visible = false;
		
			array_push($answers, $qa);
		}
		
		return $answers;
	}
}

?>
