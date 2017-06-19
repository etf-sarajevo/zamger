<?php

// Modul: lms/homework
// Klasa: Assignment
// Opis: jedan zadatak u sklopu zadaće


require_once(Config::$backend_path."lib/File.php");
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

	const MODULE = "zadace"; // Name of filesystem module (dir) where homeworks are kept
	
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
	
	// Method for submitting homework intended for students
	public function submit($fileArray, $authorId) {
		if (get_class($this->Homework) == "UnresolvedClass")
			$this->Homework->resolve();
		
		// Check if it is ok to submit this homework
		if (!$this->Homework->active) {
			// Teachers are allowed to submit expired homeworks!
			if (!AccessControl::teacherLevelStudent($this->Homework->CourseUnit->id, $this->Homework->AcademicYear->id, $this->student->id))
				throw new Exception("Homework " . $this->Homework->id . " isn't active", "403");
		}
		
		if ($this->assignNo < 1 || $this->assignNo > $this->Homework->nrAssignments)
			throw new Exception("Invalid assignment number", "500");

		// Check deadling
		if ($this->Homework->deadline <= time()) {
			// Teachers are allowed to submit expired homeworks!
			if (!AccessControl::teacherLevelStudent($this->Homework->CourseUnit->id, $this->Homework->AcademicYear->id, $this->student->id))
				throw new Exception("Time for submitting this homework is over", "403");
		}
		
		// First status
		if ($this->Homework->automatedTesting) 
			$this->status = AssignmentStatus::WaitsForTesting;
		else
			$this->status = AssignmentStatus::NewHomework;
		
		// Plagiarized hw can't be resubmitted
		$old_asgn = Assignment::fromStudentHomeworkNumber($this->student->id, $this->Homework->id, $this->assignNo);
		if ($old_asgn->status == AssignmentStatus::Plagiarized)
			throw new Exception("Not allowed to resubmit plagiarized homework", "403");
		
		$this->author = new UnresolvedClass("Person", $authorId, $asgn->author);
		
		// Construct file object
		$this->filename = File::cleanUpFilename($fileArray['name']);
		$file = $this->getFile();
		
		// Test extension
		$allowed = explode(',', $this->Homework->allowedExtensions);
		$ext = $file->extension();
		if ($this->Homework->allowedExtensions != "" && !in_array($ext, $allowed))
			throw new Exception("Extension not allowed $ext", "403");

		// Add to database
		$this->add();
		
		// Diff files
		if ($old_asgn->filename) {
			$old_file = $old_asgn->getFile();
			$this->diff($old_file);
		}
	}

	// Puts data from attributes into database
	public function add() {
		// Table "zadatak" is a logging table which means that we only INSERT and never UPDATE
		DB::query("INSERT INTO zadatak SET zadaca=".$this->Homework->id.", redni_broj=".$this->assignNo.", student=".$this->student->id.", status=".$this->status.", bodova=".$this->score.", vrijeme=NOW(), komentar='".$this->comment."', izvjestaj_skripte='".$this->compileReport."', filename='".$this->filename."', userid=".$this->author->id);
		
		// Since this is a logging table, we will now find out ID and timestamp
		$this->id = DB::insert_id();
		$this->time = DB::get("SELECT UNIX_TIMESTAMP(vrijeme) FROM zadatak WHERE id=".$this->id);

		$this->updateScore();
		
		Logging::log("izmjena zadace (student u" . $this->student->id . " zadaca z" . $this->Homework->id . " zadatak " . $this->assignNo . ")", LogLevel::Edit);
		Logging::log2("izmjena zadace", $this->student->id, $this->Homework->id, $this->assignNo);
	}
	
	// Create a diff between current file and an older file given as param
	public function diff($file) {
		if (!file_exists($file->fullPath())) return;
		$old_filename = $file->fullPath();
		$new_filename = $this->fullPath();
		$basepath = $this->basePath();
		
		// Support diffing for ZIP archives (TODO other archive types)
		if (Util::ends_with($file->filename, ".zip") && Util::ends_with($this->filename, ".zip")) {
		
			// Prepare tmp dir
			$zippath = "/tmp/difftemp";
			if (!file_exists($zippath)) {
				mkdir($zippath, 0777, true);
			} else if (!is_dir($zippath)) {
				unlink($zippath);
				mkdir($zippath);
			} else {
				Util::rm_minus_r($zippath);
			}
			
			$oldpath = "$zippath/old";
			$newpath = "$zippath/new";
			mkdir ($oldpath);
			mkdir ($newpath);
			
			`unzip -j "$old_filename" -d $oldpath`;
			`unzip -j "$new_filename" -d $newpath`;
			$diff = `/usr/bin/diff -ur $oldpath $newpath`;
			$diff = Util::clear_unicode(DB::escape($diff));
		} else {
			if (file_exists("$basepath/difftemp")) 
				unlink ("$basepath/difftemp");
			rename ($old_filename, "$basepath/difftemp"); 
			$diff = `/usr/bin/diff -u $basepath/difftemp $new_filename`;
			$diff = DB::escape($diff);
			unlink ("$basepath/difftemp");
		}
		
		if (strlen($diff)>1)
			$q270 = DB::query("INSERT INTO zadatakdiff SET zadatak=" . $this->id . ", diff='$diff'");
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
	
	// Returns File object for Assignment
	public function getFile() {
		if (get_class($this->Homework) == "UnresolvedClass")
			$this->Homework->resolve();
		$cuy = CourseUnitYear::fromCourseAndYearQuick($this->Homework->CourseUnit->id, $this->Homework->AcademicYear->id);
		return new File($this->filename, $cuy, $this->student, $this->Homework->id, Assignment::MODULE);
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
