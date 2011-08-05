<?php

// Modul: lms/poll
// Klasa: PollResult
// Opis: jedan popunjen anketni listić

// TODO naći bolji naziv od result "listić?"

require_once(Config::$backend_path."core/DB.php");

class PollResult {
	public $id;
	public $pollId, $time, $hashCode, $finished, $courseUnitId, $academicYearId, $programmeId, $semester, $studentId; // FIXME većina ovih podataka su nepotrebni, treba odrediti tačno koji

	public static function fromId($id) {
		$q10 = DB::query("select anketa, vrijeme, unique_id, zavrsena, predmet, akademska_godina, studij, semestar, student from anketa_rezultat where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown poll result");
		}
		
		$p = new PollResult;
		$p->id = $id;
		$p->pollId = mysql_result($q10,0,0);
		$p->time = mysql_result($q10,0,1);
		$p->hashCode = mysql_result($q10,0,2);
		if (mysql_result($q10,0,3) == "Y") $p->finished=true; else $p->finished=false;
		$p->courseUnitId = mysql_result($q10,0,4);
		$p->academicYearId = mysql_result($q10,0,5);
		$p->programmeId = mysql_result($q10,0,6);
		$p->semester = mysql_result($q10,0,7);
		$p->studentId = mysql_result($q10,0,8);
		
		return $p;
	}

	public static function fromHash($hashCode) {
		$q10 = DB::query("select anketa, vrijeme, id, zavrsena, predmet, akademska_godina, studij, semestar, student from anketa_rezultat where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown poll result");
		}
		
		$p = new PollResult;
		$p->hashCode= $hashCode;
		$p->pollId = mysql_result($q10,0,0);
		$p->time = mysql_result($q10,0,1);
		$p->id = mysql_result($q10,0,2);
		if (mysql_result($q10,0,3) == "Y") $p->finished=true; else $p->finished=false;
		$p->courseUnitId = mysql_result($q10,0,4);
		$p->academicYearId = mysql_result($q10,0,5);
		$p->programmeId = mysql_result($q10,0,6);
		$p->semester = mysql_result($q10,0,7);
		$p->studentId = mysql_result($q10,0,8);
		
		return $p;
	}
	
	public static function fromStudentAndPoll($studentId, $pollId) {
		$q10 = DB::query("select id, vrijeme, unique_id, zavrsena, predmet, akademska_godina, studij, semestar from anketa_rezultat where anketa=$pollId and student=$studentId");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown poll result");
		}
		
		$p = new PollResult;
		$p->id = mysql_result($q10,0,0);
		$p->pollId = $pollId;
		$p->time = mysql_result($q10,0,1);
		$p->hashCode = mysql_result($q10,0,2);
		if (mysql_result($q10,0,3) == "Y") $p->finished=true; else $p->finished=false;
		$p->courseUnitId = mysql_result($q10,0,4);
		$p->academicYearId = mysql_result($q10,0,5);
		$p->programmeId = mysql_result($q10,0,6);
		$p->semester = mysql_result($q10,0,7);
		$p->studentId = $studentId;
		
		return $p;		
	}
	
	// Add new PollResult item to database
	public function add() {
		if ($this->finished) $finsql="'Y'"; else $finsql="'N'";
		$q10 = DB::query("insert into anketa_rezultat set anketa=".$this->pollId.", vrijeme=NOW(), unique_id='".$this->hashCode."', zavrsena=$finsql, predmet=".$this->courseUnitId.", akademska_godina=".$this->academicYearId.", studij=".$this->programmeId.", semestar=".$this->semester.", student=".$this->studentId);
		$this->id = mysql_insert_id();
		$q20 = DB::query("select UNIX_TIMESTAMP(vrijeme) from anketa_rezultat where id=".$this->id);
		$this->time = mysql_result($q20,0,0);
	}
	
	// Update PollResult
	public function update() {
		if ($this->id < 1) throw new Exception("id not set, can't update");
		
		if ($this->finished) $finsql="'Y'"; else $finsql="'N'";
		$q10 = DB::query("update anketa_rezultat set anketa=".$this->pollId.", vrijeme=FROM_UNIXTIME(".$this->time."), unique_id='".$this->hashCode."', zavrsena=$finsql, predmet=".$this->courseUnitId.", akademska_godina=".$this->academicYearId.", studij=".$this->programmeId.", semestar=".$this->semester.", student=".$this->studentId." where id=".$this->id);
	}
}

?>