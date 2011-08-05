<?php

// Modul: lms/poll
// Klasa: PollAnswer
// Opis: odgovor na pojedinačno pitanje

require_once(Config::$backend_path."core/DB.php");

class PollAnswer {
	public $id;
	public $pollQuestionId, $text, $allowsWritein;
	
	// Get list of possible answers for MCSA and MCMA questions
	public static function forQuestion($pollQuestionId) {
		$q10 = DB::query("select id, izbor, dopisani_odgovor from anketa_izbori_pitanja where pitanje=$pollQuestionId");
		$answers = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$pa = new PollAnswer;
			$pa->id = $r10[0];
			$pa->pollQuestionId = $pollQuestionId;
			$pa->text = $r10[1];
			if ($r10[2] == 1) $pa->allowsWritein=true; else $pa->allowsWritein=false;
			
			array_push($answers, $pa);
		}
		return $answers;
	}
}

?>