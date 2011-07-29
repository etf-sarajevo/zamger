<?php

// Modul: lms/attendance
// Klasa: Attendance
// Opis: praćenje prisustva studenta na datom času (Class)


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Logging.php");
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/ScoringElement.php");

require_once(Config::$backend_path."lms/attendance/ZClass.php");

class Attendance {
	public $studentId, $classId;
	public $zclass, $portfolio, $scoringElement;
	
	public static function fromStudentAndClass($studentId, $classId) {
		$s = new Attendance;
		$s->studentId = $studentId;
		$s->classId = $classId;
		$s->zclass = ZClass::fromId($classId);
		$s->portfolio = Portfolio::fromCourseUnit($studentId, $s->zclass->group->courseUnitId, $s->zclass->group->academicYearId);
		$s->scoringElement = ScoringElement::fromId($s->zclass->scoringElementId);
		return $s;
	}

	// Test if student was present at this clas
	// Returns 1 if yes, 0 if no, -1 if undefined
	public function getPresence() {
		$q1 = DB::query("select prisutan from prisustvo where student=".$this->studentId." and cas=".$this->classId);
		if (mysql_num_rows($q1)<1) return -1; // unkown state
		return mysql_result($q1,0,0);
	}
	
	// Set presence to given boolean value (setting to undefined state not currently supported)
	public function setPresence ($present) {
		// TODO ovdje dodati provjeru Group::isTeacher()
		if ($present) $presentsql=1; else $presentsql=0;
		$q1 = DB::query("select prisutan from prisustvo where student=".$this->studentId." and cas=".$this->classId);
		if (mysql_num_rows($q1)<1) 
			$q2 = DB::query("insert into prisustvo set prisutan=$presentsql, student=".$this->studentId.", cas=".$this->classId);
		else
			$q3 = DB::query("update prisustvo set prisutan=$presentsql where student=".$this->studentId." and cas=".$this->classId);

		$this->updateScore();
		
		Logging::log("prisustvo - student: u$student cas: c$cas prisutan: $prisutan",2); // nivo 2 - edit
	}
	
	public function updateScore() {
		$cuid = $this->zclass->group->courseUnitId;
		$ayid = $this->zclass->group->academicYearId;
		$seid = $this->zclass->scoringElementId;
		$q10 = DB::query("select count(*) from prisustvo as p, cas as c, labgrupa as lg where p.student=".$this->studentId." and p.prisutan=0 and p.cas=c.id and c.labgrupa=lg.id and lg.predmet=$cuid and lg.akademska_godina=$ayid and c.komponenta=$seid");
		if ( mysql_result($q10,0,0) > $this->scoringElement->option )
			$this->portfolio->setScore( $seid, 0 );
		else
			$this->portfolio->setScore( $seid, $this->scoringElement->max );
	}
	
}

?>