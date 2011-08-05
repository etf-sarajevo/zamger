<?php

// Modul: lms/poll
// Klasa: PollQuestion
// Opis: pitanje na nekoj anketi

// TODO ovo treba preurediti tako da postoji many-to-many relacija između ankete i pitanja, tako da se isto pitanje može ponavljati na više anketa... onda treba skontati kako to u GUIju riješiti

require_once(Config::$backend_path."core/DB.php");

class PollQuestion {
	public $id;
	public $pollId, $typeId /* TODO preći na enum */, $text;

	public static function fromId($id) {
		$q10 = DB::query("select anketa, tip_pitanja, tekst from anketa_pitanje where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown poll question");
		}
		
		$pq = new PollQuestion;
		$pq->id = $id;
		$pq->pollId = mysql_result($q10,0,0);
		$pq->typeId = mysql_result($q10,0,1);
		$pq->text = mysql_result($q10,0,2);

		return $pq;
	}

	// Get all questions in a poll
	public static function getAllForPoll($pollId) {
		$q10 = DB::query("select id, tip_pitanja, tekst from anketa_pitanje where anketa=$pollId order by id"); 
		// It's assumed that questions are ordered by id in the order that they are supposed to appear in a poll - FIXME there should be an ordering value
		$questions = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$pq = new PollQuestion;
			$pq->id = $r10[0];
			$pq->pollId = $pollId;
			$pq->typeId = $r10[1];
			$pq->text = $r10[2];
			
			array_push($questions, $pq);
		}
		return $questions;
	}
	
	public function setAnswerRank($pollResultId, $response) {
		if ($this->typeId != 1) return;
		if ($response > 0) // 0 means "no response" and we just won't insert it into database
			$q10 = DB::query("insert into anketa_odgovor_rank set rezultat=$pollResultId, pitanje=".$this->id.", izbor_id=$response");
	}
	
	public function setAnswerEssay($pollResultId, $response) {
		if ($this->typeId != 2) return;
		if (preg_match("/\w/", $response))  // Skip blank comments
			$q10 = DB::query("insert into anketa_odgovor_text set rezultat=$pollResultId, pitanje=".$this->id.", odgovor='$response'");
	}
	
	public function setAnswerChoice($pollResultId, $response, $writein) {
		if ($this->typeId != 3 && $this->typeId != 4) return;
		
		// Does this answer allow writein response
		$q10 = DB::query("select dopisani_odgovor from anketa_izbori_pitanja where pitanje=".$this->id." and id=$response");
		if (mysql_result($q10,0,0)==1) {
			$q20 = myquery("insert into anketa_odgovor_dopisani set rezultat=$pollResultId, pitanje=".$this->id.", odgovor='$writein'");
		}
		$q30 = DB::query("insert into anketa_odgovor_izbori set rezultat=$pollResultId, pitanje=".$this->id.", izbor_id=$response");
	}
}

?>