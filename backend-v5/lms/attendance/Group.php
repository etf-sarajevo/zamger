<?php

// Modul: lms/attendance
// Klasa: Group
// Opis: nastavna grupa


require_once(Config::$backend_path."core/Portfolio.php");

class Group {
	public $id;
	public $name, $type, $CourseUnit, $AcademicYear, $virtual;
	
	public static function fromId($id, $details=false, $members=true) {
		$grp = DB::query_assoc("SELECT id, naziv name, tip type, predmet CourseUnit, akademska_godina AcademicYear, virtualna virtual FROM labgrupa WHERE id=$id");
		
		if (!$grp) throw new Exception("Unknown group $id", "404");
		$grp = Util::array_to_class($grp, "Group", array("CourseUnit", "AcademicYear"));
		if ($grp->virtual == 1) $grp->virtual=true; else $grp->virtual=false; // FIXME use boolean in database
		if ($members) $grp->members = $grp->getMembers($details);
		return $grp;
	}

	// Populate members attribute with a list of members
	public function getMembers($details = false) {
		$members = DB::query_varray("SELECT student FROM student_labgrupa WHERE labgrupa=".$this->id);
		foreach($members as &$member) {
			// Using fromCourseUnit to get CourseOffering so we could get grade&score
			$obj = Portfolio::fromCourseUnit($member, $this->CourseUnit->id, $this->AcademicYear->id);
			$obj->getGrade();
			$obj->getScore($details);
			
			// Unresolve CourseOffering because it's redundant here
			$obj->CourseOffering = new UnresolvedClass("CourseOffering", $obj->CourseOffering->id, $obj->CourseOffering);
			
			// Remove redundant data from score
			foreach ($obj->score as &$score) {
				unset($score->student);
				unset($score->CourseOffering);
				if ($details)
					$score->ScoringElement = new UnresolvedClass("ScoringElement", $score->ScoringElement->id, $score->ScoringElement);
				if ($details)
					foreach($score->details as &$detail)
						unset($detail->student);
			}
			
			$member = $obj;
		}
		return $members;
	}
	
	// Test if student with given id is a member of group
	public function isMember($studentId) {
		if (isset($this->members)) {
			foreach($this->members as $member)
				if ($studentId == $member->Person->id) return true;
			return false;
		}
		$member = DB::get("SELECT count(*) FROM student_labgrupa WHERE student=$studentId AND labgrupa=".$this->id);
		if ($member == 0) return false;
		return true;
	}
	
	// Add new student to group
	// If onlyOne parameter is true, student can be enrolled in only one non-virtual group per course and will be removed from others
	public function addMember($studentId, $onlyOne = true) {
		if ($this->isMember($studentId)) 
			throw new Exception("Student $studentId already a member of group " . $this->id, "403");
			
		if ($onlyOne) {
			$groups = fromStudentAndCourse($studentId, $this->CourseUnit->id, $this->AcademicYear->id);
			foreach($groups as $group)
				if (!$group->virtual) $group->removeMember($studentId);
		}
		
		DB::query("INSERT INTO student_labgrupa SET student=$student, labgrupa=" . $this->id);
		Logging::log("student u$student upisan u grupu g" . $this->id, 2);
		Logging::log2("student upisan u grupu", $studentId, $this->id);
		return true;
	}
	
	// Remove member from group
	public function removeMember($studentId) {
		if (!$this->isMember($studentId)) 
			throw new Exception("Student $studentId not a member of group " . $this->id, "403");
		
		$casovi = DB::query_varray("SELECT id FROM cas WHERE labgrupa=$labgrupa");
		foreach($casovi as $cas)
			DB::query("DELETE FROM prisustvo WHERE student=$student AND cas=$r10[0]");
			
		// Komentari
		DB::query("DELETE FROM komentar WHERE student=$student AND labgrupa=$labgrupa");

		// Ispis iz labgrupe
		if ($labgrupa>0) $q30 = db_query("delete from student_labgrupa where student=$student and labgrupa=$labgrupa");
		DB::query("DELETE FROM student_labgrupa WHERE student=$student AND labgrupa=" . $this->id);
		
		Logging::log("student u$studentId ispisan iz grupe g" . $this->id, 2);
		Logging::log2("student ispisan sa grupe", $studentId, $this->id);
		return true;
	}

	// Get groups that student is a member of for given course unit
	public static function fromStudentAndCourse($studentId, $courseUnitId, $academicYearId=0) {
		if ($academicYearId == 0)
			$academicYearId = AcademicYear::getCurrent()->id;
		$groups = DB::query_table("SELECT l.id id, l.naziv name, l.tip type, l.predmet CourseUnit, l.akademska_godina AcademicYear, l.virtualna virtual FROM student_labgrupa as sl, labgrupa as l WHERE l.predmet=$courseUnitId and l.akademska_godina=$academicYearId and l.id=sl.labgrupa and sl.student=$studentId");
		foreach($groups as &$grp) {
			$grp = Util::array_to_class($grp, "Group", array("CourseUnit", "AcademicYear"));
			if ($grp->virtual == 1) $grp->virtual=true; else $grp->virtual=false; // FIXME use boolean in database
		}
		return $groups;
	}

	// Get the virtual group for course and year
	public static function virtualForCourse($courseUnitId, $academicYearId=0) {
		if ($academicYearId == 0)
			$academicYearId = AcademicYear::getCurrent()->id;

		// Assumption: there is only one virtual group on course
		$grp = DB::query_assoc("SELECT id, naziv name, tip type, predmet CourseUnit, akademska_godina AcademicYear, virtualna virtual FROM labgrupa WHERE predmet=$courseUnitId AND akademska_godina=$academicYearId AND virtualna=1");
		
		if (!$grp) throw new Exception("No virtual group at course $courseUnitId, year $academicYearId", "404");
		$grp = Util::array_to_class($grp, "Group", array("CourseUnit", "AcademicYear"));
		if ($grp->virtual == 1) $grp->virtual=true; else $grp->virtual=false; // FIXME use boolean in database
		$grp->members = $grp->getMembers();
		return $grp;
	}

	// All groups on course and year, 
	// third parameter decides whether the virtual group "all students" will be included
	public static function forCourseAndYear($courseUnitId, $academicYearId=0, $includeVirtual=false, $getMembers=false) {
		if ($academicYearId == 0)
			$academicYearId = AcademicYear::getCurrent()->id;
		
		$query_add = "";
		if (!$includeVirtual) $query_add = " AND virtualna=0";
		
		$groups = DB::query_table("SELECT id, naziv name, tip type, predmet CourseUnit, akademska_godina AcademicYear, virtualna virtual FROM labgrupa WHERE predmet=$courseUnitId AND akademska_godina=$academicYearId $query_add");
		foreach($groups as &$grp) {
			$grp = Util::array_to_class($grp, "Group", array("CourseUnit", "AcademicYear"));
			if ($grp->virtual == 1) $grp->virtual=true; else $grp->virtual=false; // FIXME use boolean in database
			if ($getMembers) $grp->members = $grp->getMembers();
		}
		return $groups;
	}
}

?>
