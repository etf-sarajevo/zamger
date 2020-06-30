<?php

// Modul: lms/homework
// Klasa: Assignment
// Opis: jedan zadatak u sklopu zadaće


require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."lib/File.php");
require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."lms/attendance/Group.php");

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
		$hw = Homework::fromId($homeworkId); // Test if homework exists
	
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
	
	// Construct a ZIP file with all assignments
	public static function getAllAssignments($homeworkId, $assignmentNumber, $filenamesOption) {
		$hw = Homework::fromId($homeworkId);
		$hw->ProgrammingLanguage->resolve();
		$ext = $hw->ProgrammingLanguage->extension;
		$allStudents = Group::virtualForCourse($hw->CourseUnit->id, $hw->AcademicYear->id);
		
		$files = $filenames = array();
		foreach($allStudents->members as $member) {
			$asgn = Assignment::fromStudentHomeworkNumber($member->student->id, $homeworkId, $assignmentNumber);
			$asgn->Homework = $hw; // Avoid resolving homework
			$files[] = $asgn->getFile();
			
			if ($filenamesOption == "fullname") {
				$member->student->resolve();
				$filenames[] = File::cleanUpFilename( $member->student->surname . "_" .$member->student->name . "_" . $member->student->studentIdNr . $ext);
			}
			else if ($filenamesOption == "login") {
				$member->student->resolve();
				$filenames[] = File::cleanUpFilename( $member->student->login . $ext);
			}
			else if ($filenamesOption == "person_id") {
				$filenames[] = $member->student->id . $ext;
			}
			else {
				$filenames[] = $asgn->id . $ext;
			}
		}
		
		$zip = File::temporary(".zip");
		$zip->addToZip($files, $filenames);
		return $zip;
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
		
		$this->author = new UnresolvedClass("Person", $authorId, $old_asgn->author);
		
		// Construct file object
		$this->filename = File::cleanUpFilename($fileArray['name']);
		// COMPATIBILITY: Force name to be in certain format
		if (!$this->Homework->attachment) {
			if (get_class($this->Homework->ProgrammingLanguage) == "UnresolvedClass")
				$this->Homework->ProgrammingLanguage->resolve();
			$this->filename = $this->assignNo . $this->Homework->ProgrammingLanguage->extension;
		}
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
			$diff = Assignment::diff($old_asgn->getFile()->fullPath(), $fileArray['tmp_name']);

			if (strlen($diff)>1) {
				$diff = Util::clear_unicode(DB::escape($diff));
				DB::query("INSERT INTO zadatakdiff SET zadatak=" . $this->id . ", diff='$diff'");
			}
		}
		
		// Finally move file into place
		$destination = $file->fullPath(); // This will also create directory
		if (file_exists($destination)) unlink ($destination);
		rename($fileArray['tmp_name'], $destination);
		//chmod($destination, 0640);
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
	
	// Create a diff between two files (full paths are params)
	public static function diff($oldFile, $newFile) {
		if (!file_exists($oldFile) || !file_exists($newFile)) return;
		
		// Prepare tmp location
		$diff_path = "/tmp/difftemp";
		if (file_exists($diff_path)) {
			if (is_dir($diff_path))
				File::rm_minus_r($diff_path);
			else
				unlink($diff_path);
		}
		
		// Support diffing for ZIP archives (TODO other archive types)
		if (Util::ends_with($oldFile, ".zip") && Util::ends_with($newFile, ".zip")) {
			mkdir($diff_path, 0777, true);
			
			$oldpath = "$diff_path/old";
			$newpath = "$diff_path/new";
			mkdir ($oldpath);
			mkdir ($newpath);
			
			`unzip -j "$oldFile" -d $oldpath`;
			`unzip -j "$newFile" -d $newpath`;
			$diff = `/usr/bin/diff -ur $oldpath $newpath`;
		} else {
			$diff = `/usr/bin/diff -u $oldFile $newFile 2>&1`;
		}
		return $diff;
	}
	
	// Update score data related to homeworks
	public function updateScore() {
		// Resolve homework so we could get CourseUnit and AcademicYear
		if (get_class($this->Homework) == "UnresolvedClass")
			$this->Homework->resolve();
		
		// Get CourseOffering for student
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
			if ($scoringElementId != 0 && $hw->ScoringElement->id != $scoringElementId) continue;
			for ($i=1; $i<$hw->nrAssignments; $i++)
				$result[] = Assignment::fromStudentHomeworkNumber($studentId, $hw->id, $i);
		}
		return $result;
	}

	// List of assignments for student on course unit
	public static function forStudentOnCourseUnit($studentId, $courseUnitId, $academicYearId=0, $scoringElementId=0) {
		if ($academicYearId == 0)
			$academicYearId = AcademicYear::getCurrent()->id;

		$co = CourseOffering::forStudent($studentId, $courseUnitId, $academicYearId);
		if (!$co) throw new Exception("Student $studentId not enrolled in course $courseUnitId, year $academicYearId", "404");

		return Assignment::forStudentOnCourse($studentId, $co->id, $scoringElementId);
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
