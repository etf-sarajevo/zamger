<?php

// Modul: lms/homework
// Klasa: Assignment
// Opis: jedan zadatak u sklopu zadaće


require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/StudentScore.php");

abstract class AssignmentStatus {
	const NotSent = 0;
	const WaitsForTesting = 1;
	const Plagiarized = 2;
	const DoesntCompile = 3;
	const NewHomework = 4;
	const Reviewed = 5;
}

class Assignment {

	public $id;
	public $Homework, $assignNo, $student, $status, $score, $time, $comment, $compileReport /* ovo treba spojiti sa comment */, $filename, $author;
	
	public static function fromId($id) {
		$asgn = DB::query_assoc("SELECT id, zadaca Homework, redni_broj assignNo, student, status, bodova score, UNIX_TIMESTAMP(vrijeme) time, komentar comment, izvjestaj_skripte compileReport, filename, userid author FROM zadatak WHERE id=$id");
		if (!$asgn) throw new Exception("Unknown assignment status $id", "404");
		
		$asgn = Util::array_to_class($asgn, "Assignment", array("Homework"));
		$asgn->student = new UnresolvedClass("Person", $asgn->student, $asgn->student);
		$asgn->author = new UnresolvedClass("Person", $asgn->author, $asgn->author);
		return $asgn;
	}
	
	public static function fromStudentHomeworkNumber($studentId, $homeworkId, $assignmentNumber) {
		$asgn = DB::query_assoc("SELECT id, zadaca Homework, redni_broj assignNo, student, status, bodova score, UNIX_TIMESTAMP(vrijeme) time, komentar comment, izvjestaj_skripte compileReport, filename, userid author FROM zadatak WHERE student=$studentId and zadaca=$homeworkId AND redni_broj=$assignmentNumber ORDER BY id DESC LIMIT 1"); // since this is a logging table, we need latest ID
		if (!$asgn)
			// Student never sent homework
			$asgn = array("Homework" => $homeworkId, "assignNo" => $assignmentNumber, "student" => $studentId, 
				"status" => AssignmentStatus::NotSent, "score" => 0);
		
		$asgn = Util::array_to_class($asgn, "Assignment", array("Homework"));
		$asgn->student = new UnresolvedClass("Person", $asgn->student, $asgn->student);
		$asgn->author = new UnresolvedClass("Person", $asgn->author, $asgn->author);
		return $asgn;
	}

	// Puts data from attributes into database
	public function addAssignment() {
		DB::query("INSERT INTO zadatak SET zadaca=".$this->Homework->id.", redni_broj=".$this->assignNo.", student=".$this->student->id.", status=".$this->status.", bodova=".$this->score.", vrijeme=NOW(), komentar='".$this->comment."', izvjestaj_skripte='".$this->compileReport."', filename='".$this->filename."', userid=".$this->author->id);
		
		// Since this is a logging table, we will now find out ID and timestamp
		$this->id = DB::insert_id();
		$this->time = DB::get("SELECT UNIX_TIMESTAMP(vrijeme) FROM zadatak WHERE id=".$this->id);

		$this->updateScore();
		
		Logging::log("izmjena zadace (student u" . $this->student->id . " zadaca z" . $this->Homework->id . " zadatak " . $this->assignNo . ")", LogLevel::Edit);
		Logging::log2("izmjena zadace", $this->student->id, $this->Homework->id, $this->assignNo);
	}
	
	// Update score data related to homeworks
	public function updateScore() {
		// Resolve homework so we could get CourseUnit and AcademicYear
		if (get_class($this->Homework) == "UnresolvedClass")
			$this->Homework->resolve();
		
		// Get CoureOffering for student
		$co = CourseOffering::forStudent($this->student->id, $this->Homework->CourseUnit->id, $this->Homework->AcademicYear->id);
		
		// Construct StudentScore object
		$ss = StudentScore::fromStudentSEandCO($this->student->id, 
							$this->Homework->ScoringElement->id, 
							$co->id);
		
		$score = Assignment::calculateScore($this->student->id, $co->id, $this->Homework->ScoringElement);
		
		// Update score
		$ss->setScore($score);
	}
	
	// List of assignments for student on course
	public static function forStudentOnCourse($studentId, $courseOfferingId, $scoringElementId) {
		$homeworks = Homework::fromCourseOffering($courseOfferingId);
		$result = array();
		foreach($homeworks as $hw) {
			if ($hw->ScoringElement->id != $scoringElementId) continue;
			for ($i=1; $i<$hw->nrAssignments; $i++)
				$result[] = Assignment::fromStudentHomeworkNumber($studentId, $hw->id, $i);
		}
		return $result;
	}

	// Calculate score that a student would have for homeworks
	public static function calculateScore($studentId, $courseOfferingId, $ScoringElement) {
		$homeworks = Homework::fromCourseOffering($courseOfferingId);
		$totalScore = 0;
		foreach($homeworks as $hw) {
			if ($hw->ScoringElement->id != $ScoringElement->id) continue;
			for ($i=1; $i<$hw->nrAssignments; $i++) {
				$asgn = Assignment::fromStudentHomeworkNumber($studentId, $hw->id, $i);
				if ($asgn->status == AssignmentStatus::Reviewed) $totalScore += $asgn->score;
			}
		}
		return $totalScore;
	}
}

?>
