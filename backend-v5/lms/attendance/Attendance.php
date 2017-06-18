<?php

// Modul: lms/attendance
// Klasa: Attendance
// Opis: praćenje prisustva studenta na datom času (Class)


require_once(Config::$backend_path."lms/attendance/ZClass.php");
require_once(Config::$backend_path."core/CourseOffering.php");
require_once(Config::$backend_path."core/StudentScore.php");

class Attendance {
	public $student, $ZClass, $presence;

	// This is a standard constructor
	public static function fromStudentAndClass($studentId, $classId) {
		$att = new Attendance;
		$att->student = new UnresolvedClass("Person", $studentId, $att->student);
		$att->ZClass = new UnresolvedClass("ZClass", $classId, $att->ZClass);
		return $att;
	}

	// Test if student was present at this class
	// Returns 1 if yes, 0 if no, -1 if undefined state (either present or not)
	// false if no presence data is available
	public function getPresence() {
		$this->presence = DB::get("SELECT prisutan FROM prisustvo WHERE student=".$this->student->id." AND cas=".$this->ZClass->id);
		return $this->presence;
	}
	
	// Set presence to given value (as with getPresence)
	public function setPresence ($present) {
		if ($present !== 1 && $present !== 0 && $present !== -1)
			throw new Exception("Invalid presence value $present", "701");
		
		// Is student in class?
		if (get_class($this->ZClass) == "UnresolvedClass")
			$this->ZClass->resolve();
		if (get_class($this->ZClass->Group) == "UnresolvedClass")
			$this->ZClass->Group->resolve();
		
		$found = false;
		foreach($this->ZClass->Group->members as $member_pf) {
			if ($member_pf->Person->id == $this->student->id) {
				$found = true;
				break;
			}
		}
		if (!$found)
			throw new Exception("Student " . $this->student->id . " not in group for class", "404");
		
		$has_presence = getPresence();
		if ($has_presence === false)
			DB::query("INSERT INTO prisustvo SET prisutan=$present, student=".$this->student->id.", cas=".$this->ZClass->id);
		else
			DB::query("UPDATE prisustvo SET prisutan=$present WHERE student=".$this->student->id." AND cas=".$this->ZClass->id);
		$this->presence = $present;

		$this->updateScore();
		
		Logging::log("prisustvo - student: u$student cas: c$cas prisutan: $prisutan", LogLevel::Edit);
		Logging::log2("prisustvo azurirano", $this->student->id, $this->ZClass->id, $present);
	}
	
	// Update score data related to presence
	public function updateScore() {
		// Resolve class and group so we could get CourseUnit and AcademicYear
		if (get_class($this->ZClass) == "UnresolvedClass")
			$this->ZClass->resolve();
		if (get_class($this->ZClass->Group) == "UnresolvedClass")
			$this->ZClass->Group->resolve();
		if (get_class($this->ZClass->ScoringElement) == "UnresolvedClass")
			$this->ZClass->ScoringElement->resolve();
		
		// Get CoureOffering for student
		$co = CourseOffering::forStudent($this->student->id, $this->ZClass->Group->CourseUnit->id, $this->ZClass->Group->AcademicYear->id);
		
		// Construct StudentScore object
		$ss = StudentScore::fromStudentSEandCO($this->student->id, 
							$this->ZClass->ScoringElement->id, 
							$co->id);
		
		$score = Attendance::calculateScore($this->student->id, $co->id, $this->ZClass->ScoringElement);
		
		// Update score
		$ss->setScore($score);
	}
	
	// Calculate score that a student would have for attendance
	public static function calculateScore($studentId, $courseOfferingId, $ScoringElement) {
		$max_score = $ScoringElement->max;
		$min_score = $ScoringElement->pass;
	
		$absence = DB::get("SELECT COUNT(*) FROM cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk WHERE c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$courseOfferingId and c.komponenta=" . $ScoringElement->id . " and c.id=p.cas and p.student=$studentId and p.prisutan=0");
		
		$score = 0;
		
		// Option -1 means score is proportional to number of absences
		if ($ScoringElement->option == -1) {
			$total_classes = DB::get("SELECT COUNT(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk WHERE c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$courseOfferingId and c.komponenta=" . $ScoringElement->id . " and c.id=p.cas and p.student=$studentId");
			if ($total_classes == 0)
				$score = $max_score;
			else
				$score = $min_score + round(($max_score - $min_score) * (($total_classes-$absence) / $total_classes), 2 );
			
		// Paraproporcionalni sistem TP
		} else if ($ScoringElement->option == -2) { 
			// TODO: svo prisustvo se može generalizovati na ovaj sistem, pa tako treba i uraditi
			if ($absence <= 2)
				$score = $max_score;
			else if ($absence <= 2 + ($max_score - $min_score)/2)
				$score = $max_score - ($absence-2)*2;
			else
				$score = $min_score;

		} else if ($ScoringElement->option == -3) { // Još jedan sistem TP
			$total_classes = DB::get("SELECT COUNT(*) from cas as c, labgrupa as l, prisustvo as p, ponudakursa as pk WHERE c.labgrupa=l.id and l.predmet=pk.predmet and l.akademska_godina=pk.akademska_godina and pk.id=$courseOfferingId and c.komponenta=" . $ScoringElement->id . " and c.id=p.cas and p.student=$studentId");
			
			$score = ($max_score / 13) * ($total_classes - $absence);

		// Non-negative option is maximum allowed number of absences
		} else if ($ScoringElement->option >= 0) {
			$max_absences = $ScoringElement->option;
			if ($absence > $max_absences)
				$score = $max_score;
			else
				$score = $min_score;
		}
		
		return $score;
	}
	
	// List of attendance data for student on course
	public static function forStudentOnCourse($studentId, $courseOfferingId, $scoringElementId) {
		$classes = DB::query_table("SELECT c.id id, c.labgrupa _Group, p.prisutan presence FROM cas c, student_labgrupa sl, labgrupa lg, ponudakursa pk, prisustvo p WHERE p.cas=c.id AND p.student=$studentId AND sl.student=$studentId AND sl.labgrupa=c.labgrupa AND c.komponenta=$scoringElementId AND c.labgrupa=lg.id AND lg.predmet=pk.predmet AND lg.akademska_godina=pk.akademska_godina AND pk.id=$courseOfferingId ORDER BY  _Group, c.datum, c.vrijeme");
		$results = array();
		$obj = false;
		foreach ($classes as $class) {
			if ($obj == false || $class['_Group'] != $obj->Group->id) {
				if ($obj !== false) $results[] = $obj;
				$obj = new stdClass;
				$obj->Group = new UnresolvedClass("Group", $class['_Group'], $obj->Group);
				$obj->attendance = array();
			}
			$att = Attendance::fromStudentAndClass($studentId, $class['id']);
			$att->presence = $class['presence'];
			$obj->attendance[] = $att;
		}
		$results[] = $obj;
		return $results;
	}
}

?>
