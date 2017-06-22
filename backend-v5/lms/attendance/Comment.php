<?php

// Modul: lms/attendance
// Klasa: Comment
// Opis: tekstualni komentari na aktivnost studenta u grupi



class Comment {
	public $id;
	public $student, $teacher, $Group, $dateTime, $text;
	
	public static function fromId($id) {
		$com = DB::query_assoc("SELECT id, student, nastavnik teacher, labgrupa Group, UNIX_TIMESTAMP(datum) dateTime, komentar text FROM komentar WHERE id=$id");
		if (!$com) throw new Exception("Unknown comment $id", "404");
		
		$com = Util::array_to_class($com, "Comment", array("Group"));
		$com->student = new UnresolvedClass("Person", $com->student, $com->student);
		$com->teacher = new UnresolvedClass("Person", $com->teacher, $com->teacher);
		return $com;
	}
	
	// List of comments for student in a group, ordered by date
	public static function forStudentInGroup($studentId, $groupId) {
		$coms = DB::query_table("SELECT id, student, nastavnik teacher, labgrupa Group, UNIX_TIMESTAMP(datum) dateTime, komentar text FROM komentar WHERE student=$studentId AND labgrupa=$groupId ORDER BY datum");
		foreach($coms as &$com) {
			$com = Util::array_to_class($com, "Comment", array("Group"));
			$com->student = new UnresolvedClass("Person", $com->student, $com->student);
			$com->teacher = new UnresolvedClass("Person", $com->teacher, $com->teacher);
		}
		return $coms;
	}
	
	// Insert comment as new comment in database
	public function add() {
		$text = DB::escape($this->text);
		DB::query("INSERT INTO komentar SET student=" . $this->student->id . ", nastavnik=" . $this->teacher->id . ", labgrupa=" . $this->Group->id . ", datum=NOW(), komentar='$text'");
		$this->id = DB::insert_id();
		$this->dateTime = time();
	}
	
	// Delete comment 
	public function delete() {
		DB::query("DELETE FROM komentar WHERE id=" . $this->id);
	}
	
	// Delete comment 
	public function update() {
		DB::query("UPDATE komentar SET student=" . $this->student->id . ", nastavnik=" . $this->teacher->id . ", labgrupa=" . $this->Group->id . ", datum=NOW(), komentar='$text' WHERE id=" . $this->id);
		$this->dateTime = time();
	}
	
	// Delete comment 
	public function validate() {
		$grp = new Group;
		$grp->id = $this->Group->id;
		if (!$grp->isMember($this->student->id))
			throw new Exception("Student " . $this->student->id . " not member of group " . $grp->id, "403");
		return true;
	}
	
	// List of exams held on a course, ordered by date
	public static function deleteAllforStudentInGroup($studentId, $groupId) {
		DB::query("DELETE FROM komentar WHERE student=$studentId AND labgrupa=$groupId");
	}
}

?>
