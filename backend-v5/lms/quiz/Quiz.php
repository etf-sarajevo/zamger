<?php

// Modul: lms/quiz
// Klasa: Quiz
// Opis: kvizovi


require_once(Config::$backend_path."core/CourseUnit.php");
require_once(Config::$backend_path."lms/quiz/QuizQuestion.php");
require_once(Config::$backend_path."lms/quiz/QuizResult.php");

class Quiz {
	public $id;
	public $name, $CourseUnit, $AcademicYear, $Group, $timeBegin, $timeEnd, $active, $ipAddressRanges, $passPoints, $nrQuestions, $duration; /* in seconds */
	// $zclassId -- dodati link na čas umjesto kako je sada, link sa časa na kviz
	
	public static function fromId($id) {
		$quiz = DB::query_assoc("SELECT id, naziv name, predmet CourseUnit, akademska_godina AcademicYear, labgrupa _Group, UNIX_TIMESTAMP(vrijeme_pocetak) timeBegin, UNIX_TIMESTAMP(vrijeme_kraj) timeEnd, aktivan active, ip_adrese ipAddressRanges, prolaz_bodova passPoints, broj_pitanja nrQuestions, trajanje_kviza duration FROM kviz WHERE id=$id");
		if (!$quiz) throw new Exception("Unknown quiz $id", "404");
		
		$quiz['Group'] = $quiz['_Group']; unset($quiz['_Group']); // SQL reserved word
		$quiz = Util::array_to_class($quiz, "Quiz", array("CourseUnit", "AcademicYear", "Group"));
		if ($quiz->active == 1) $quiz->active=true; else $quiz->active=false; // FIXME use boolean in database
		$quiz->questions = QuizQuestion::forQuiz($id);
		return $quiz;
	}
	
	// Allow student to take quiz
	public static function take($studentId, $quizId) {
		try {
			// If student never took this quiz, below will throw an exception
			$qr = QuizResult::fromStudentAndQuiz($studentId, $quizId);
			throw new Exception("Quiz already taken", "703");
		} catch(Exception $e) {
			// Proceed to taking quiz
		}
		
		$quiz = DB::query_assoc("SELECT id, naziv name, predmet CourseUnit, akademska_godina AcademicYear, labgrupa _Group, UNIX_TIMESTAMP(vrijeme_pocetak) timeBegin, UNIX_TIMESTAMP(vrijeme_kraj) timeEnd, aktivan active, ip_adrese ipAddressRanges, prolaz_bodova passPoints, broj_pitanja nrQuestions, trajanje_kviza duration FROM kviz WHERE id=$quizId");
		if (!$quiz) throw new Exception("Unknown quiz $quizId", "404");
		
		$quiz['Group'] = $quiz['_Group']; unset($quiz['_Group']); // SQL reserved word
		$quiz = Util::array_to_class($quiz, "Quiz", array("CourseUnit", "AcademicYear", "Group"));
		if ($quiz->active == 1) $quiz->active=true; else $quiz->active=false; // FIXME use boolean in database
		$quiz->questions = QuizQuestion::takeQuiz($id, $quiz->nrQuestions, true);

		// Create a QuizResult object with finished=false
		$quiz->result = QuizResult::create($studentId, $quizId);

		return $quiz;
	}

	
	// Student submits quiz results by calling submit method below on Quiz object
	// It's assumed that object is returned by Quiz::take() and that QuizAnswer objects
	// which user thinks are correct are filled with the attribute QuizAnswer::correct=true
	public function submit($studentId) {
		// First check if there's QuizResult with finished=false
		try {
			$qr = QuizResult::fromStudentAndQuiz($studentId, $id);
			if ($qr->finished)
				throw new Exception("Quiz already taken", "703");
		} catch(Exception $e) {
			throw new Exception("Student $studentId never started quiz " . $this->id, "404");
		}

		// Next check token
		if ($this->result->token != $qr->token)
			throw new Exception("Invalid token", "403");

		// Since this object may come from user, the only info we implicitely trust is 
		// quiz id and token
		// So we get a brand new quiz object
		$quiz = Quiz::fromId($this->id);
		if (time() > $quiz->timeEnd)
			throw new Exception("Sorry, quiz " . $quiz->id . " has expired", "403");
		if (time() - $qr->timeActivated > $quiz->duration)
			throw new Exception("You took too long to finish quiz " . $quiz->id, "403");
			
		// Resolve questions and answers
		$score = 0;
		foreach ($quiz->questions as &$qq) {
			$qq->resolve();
			$correct_answer = 0;
			foreach($qq->answers as &$qa) {
				$qa->resolve();
				if ($qa->correct) $correct_answer = $qa->id;
			}
			
			// Find matching question/answer in user submitted data
			foreach($this->questions as $quq)
				if ($quq->id == $qq->id)
					foreach($quq->answers as $qua)
						if ($qua->correct && $qua->id == $correct_answer) $score++;
		}
		
		// Set score and finished status for QuizResult
		$qr->finished = true;
		$qr->score = $score;
		$qr->update();
		$quiz->result = $qr;
		
		return $quiz;
	}
	
	// Gets no more than $limit quizzes, ordered by deadline descending
	// Only list active quizzes in groups that student is in
	// Limit 0 means no limit
	public static function getLatestForStudent($studentId, $limit = 0) {
		if ($limit > 0) $sql_limit = "LIMIT $limit"; else $sql_limit = "";
		$quizzes = DB::query_table("SELECT k.id id, k.naziv name, k.predmet CourseUnit, k.akademska_godina AcademicYear, k.labgrupa _Group, UNIX_TIMESTAMP(k.vrijeme_pocetak) timeBegin, UNIX_TIMESTAMP(k.vrijeme_kraj) timeEnd, k.aktivan active, k.ip_adrese ipAddressRanges, k.prolaz_bodova passPoints, k.broj_pitanja nrQuestions, k.trajanje_kviza duration FROM kviz as k, student_predmet as sp, ponudakursa as pk, predmet as p 
		WHERE sp.student=$studentId AND sp.predmet=pk.id AND pk.predmet=k.predmet AND pk.predmet=p.id AND pk.akademska_godina=k.akademska_godina AND k.vrijeme_pocetak<NOW() AND k.vrijeme_kraj>NOW() AND k.aktivan=1
		AND (SELECT COUNT(*) FROM konacna_ocjena ko WHERE ko.student=$studentId and ko.predmet=p.id and ko.ocjena>=6)=0
		ORDER BY k.vrijeme_pocetak DESC
		$sql_limit");
		foreach ($quizzes as &$quiz) {
			$quiz['Group'] = $quiz['_Group']; unset($quiz['_Group']); // SQL reserved word
			$quiz = Util::array_to_class($quiz, "Quiz", array("CourseUnit", "AcademicYear", "Group"));
			if ($quiz->active == 1) $quiz->active=true; else $quiz->active=false; // FIXME use boolean in database
		}
		
		return $quizzes;
	}
	
	public static function isIpInRange($ipAddress, $ipRanges) {
	
		// Hack za činjenicu da je long tip u PHPu signed
		// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
		function ip2float($ip) {
			return (float)sprintf("%u",ip2long($ip));
		}

		$blokovi = explode(",", $ipRanges);
		foreach ($blokovi as $blok) {
			if (strstr($blok, "/")) { // adresa u CIDR formatu
				// Npr. 192.168.0.1/24
				// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
				list ($baza, $maska) = explode("/", $blok);
				$moja_f = ip2float($ipAddress);
				$baza_f = ip2float($baza);
				$netmask_dec = bindec( str_pad('', $maska, '1') . str_pad('', 32-$maska, '0') );
				$wildcard_dec = pow(2, (32-$maska)) - 1;
				$netmask_dec = ~ $wildcard_dec; 
				if (($moja_f & $netmask_dec) == ($baza_f & $netmask_dec))
					return true;
			}

			else if (strstr($blok, "-")) { // Raspon sa crticom
				// Npr. 10.0.0.1 - 10.0.0.15
				list ($prva, $zadnja) = explode("-", $blok);
				$moja_f = ip2float($ipAddress);
				$prva_f = ip2float($prva);
				$zadnja_f = ip2float($zadnja);
				if (($moja_f >= $prva_f) && ($moja_f <= $zadnja_f))
					return true;

			} else { // Pojedinačna adresa
				if ($ipAddress == $blok)
					return true;
			}
		}
		return false;
	}

	
	// List of homeworks published for course
	public static function fromCourse($courseUnitId, $academicYearId=0) {
		if ($academicYearId == 0)
			$quizzes = DB::query_table("SELECT kviz.id id, kviz.naziv name, predmet CourseUnit, kviz.akademska_godina AcademicYear, labgrupa _Group, UNIX_TIMESTAMP(vrijeme_pocetak) timeBegin, UNIX_TIMESTAMP(vrijeme_kraj) timeEnd, aktivan active, ip_adrese ipAddressRanges, prolaz_bodova passPoints, broj_pitanja nrQuestions, trajanje_kviza duration FROM kviz, akademska_godina ag WHERE predmet=$courseUnitId and akademska_godina=ag.id AND ag.aktuelna=1 ORDER BY kviz.naziv");
		else
			$quizzes = DB::query_table("SELECT id, naziv name, predmet CourseUnit, akademska_godina AcademicYear, labgrupa _Group, UNIX_TIMESTAMP(vrijeme_pocetak) timeBegin, UNIX_TIMESTAMP(vrijeme_kraj) timeEnd, aktivan active, ip_adrese ipAddressRanges, prolaz_bodova passPoints, broj_pitanja nrQuestions, trajanje_kviza duration FROM kviz WHERE predmet=$courseUnitId and akademska_godina=$academicYearId ORDER BY naziv");
		foreach ($quizzes as &$quiz) {
			$quiz['Group'] = $quiz['_Group']; unset($quiz['_Group']); // SQL reserved word
			$quiz = Util::array_to_class($quiz, "Quiz", array("CourseUnit", "AcademicYear", "Group"));
			if ($quiz->active == 1) $quiz->active=true; else $quiz->active=false; // FIXME use boolean in database
			$quiz->questions = QuizQuestion::forQuiz($quiz->id);
		}
		return $quizzes;
	}
}

?>
