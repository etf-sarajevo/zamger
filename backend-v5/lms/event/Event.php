<?php

// Modul: lms/event
// Klasa: Event
// Opis: prijavljivanje za događaje

// TODO: Ovaj modul je trenutno fiksiran na prijavu ispita, treba ga generalizovati na opšte događaje


class Event {
	public $id;
	public $dateTime, $maxStudents, $deadline, $CourseUnit, $AcademicYear, $details;
	// $zclassId -- dodati link na čas umjesto kako je sada, link sa časa na kviz
	
	public static function fromId($id) {
		$evt = DB::query_assoc("SELECT it.id id, it.datumvrijeme dateTime, it.maxstudenata maxStudents, it.deadline deadline, i.predmet CourseUnit, i.akademska_godina AcademicYear, i.id Exam FROM ispit_termin it, ispit i WHERE it.id=$id AND it.ispit=i.id");
		if (!$evt) throw new Exception("Unknown event $evt", "404");
		
		$evt = Util::array_to_class($evt, "Event", array("CourseUnit", "AcademicYear"));
		$evt->students = $evt->getStudents();
		$evt->details = new UnresolvedClass("Exam", $evt->Exam, $evt->details); // FIXME
		unset($evt->Exam);
		return $evt;
	}
	
	// List of students subscribed to event
	public function getStudents() {
		$students = DB::query_varray("SELECT student FROM student_ispit_termin WHERE ispit_termin=" . $this->id);
		foreach($students as &$student)
			$student = new UnresolvedClass("Person", $student, $student);
		return $students;
	}
	
	// Register student for event
	public function register($studentId, $checkStudent = true, $checkLimits = true) {
		// Test if time/number limits are exceeded
		if ($checkLimits) {
			if (count($this->students) >= $this->maxStudents)
				throw new Exception("Maximum number of students for event reached", "403");
			if (time() >= $this->dateTime)
				throw new Exception("Time for registering for this event is over", "403");
		}
		
		// Test if student can be registered for event
		if ($checkStudent) {
			if (!CourseOffering::forStudent($studentId, $this->CourseUnit->id, $this->AcademicYear->id))
				throw new Exception("Student $studentId not enrolled in course for event", "403");
			
			foreach($this->students as $registered)
				if ($registered->id == $studentId)
					throw new Exception("Student $studentId already registered for event", "403");
		}
		
		DB::query("INSERT INTO student_ispit_termin SET student=$studentId, ispit_termin=" . $this->id);
		
		// Ensure reference is correctly inserted into array
		$count = count($this->students);
		$this->students[$count] = 0;
		$this->students[$count] = new UnresolvedClass("Person", $studentId, $this->students[$count]);
		return true;
	}
	
	// Unregister student from event
	// If student never registered, function returns false
	public function unregister($studentId) {
		// We don't test anything
		// Unregistering from event that one can't register back for should be handled in UI
		DB::query("DELETE FROM student_ispit_termin WHERE student=$studentId AND ispit_termin=" . $this->id);
		for($i=0; $i<count($this->students); $i++)
			if ($this->students[$i]->id == $studentId) unset($this->students[$i]);
		return (DB::affected_rows() > 0);
	}
	
	// List upcoming events (with deadline in future) that given student can register for, in current academic year 
	public static function upcomingForStudent($studentId) {
		$evts = DB::query_table("SELECT it.id id, it.datumvrijeme dateTime, it.maxstudenata maxStudents, it.deadline deadline, i.predmet CourseUnit, i.akademska_godina AcademicYear, i.id Exam FROM ispit_termin it, ispit i, student_predmet sp, ponudakursa pk, akademska_godina ag WHERE it.ispit=i.id AND i.predmet=pk.predmet AND i.akademska_godina=ag.id AND sp.predmet=pk.id AND sp.student=$studentId AND pk.akademska_godina=ag.id AND ag.aktuelna=1 AND it.deadline>NOW() ORDER BY dateTime");
		foreach($evts as &$evt) {
			$evt = Util::array_to_class($evt, "Event", array("CourseUnit", "AcademicYear"));
			// Return number of students so that student can know if there is room left
			$evt->registered = count($evt->getStudents());
			$evt->details = new UnresolvedClass("Exam", $evt->Exam, $evt->details); // FIXME
			unset($evt->Exam);
		}
		return $evts;
	}
	
	// List events for which student is already registered, in current academic year 
	// This will also return a number of events from the past, which can be filtered in UI if neccessary
	public static function registeredForStudent($studentId) {
		$evts = DB::query_table("SELECT it.id id, it.datumvrijeme dateTime, it.maxstudenata maxStudents, it.deadline deadline, i.predmet CourseUnit, i.akademska_godina AcademicYear, i.id Exam FROM ispit_termin it, ispit i, student_ispit_termin sit, akademska_godina ag WHERE it.ispit=i.id AND it.id=sit.ispit_termin AND sit.student=$studentId AND i.akademska_godina=ag.id AND ag.aktuelna=1 ORDER BY dateTime");
		foreach($evts as &$evt) {
			$evt = Util::array_to_class($evt, "Event", array("CourseUnit", "AcademicYear"));
			// Return number of students so that student can know if there is room left
			$evt->registered = count($evt->getStudents());
			$evt->details = new UnresolvedClass("Exam", $evt->Exam, $evt->details); // FIXME
			unset($evt->Exam);
		}
		return $evts;
	}

}

?>
