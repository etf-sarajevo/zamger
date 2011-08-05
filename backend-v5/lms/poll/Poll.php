<?php

// Modul: lms/poll
// Klasa: Poll
// Opis: jedna anketa

require_once(Config::$backend_path."core/DB.php");

class Poll {
	public $id;
	public $name, $description, $openDate, $closeDate, $editable;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, opis, UNIX_TIMESTAMP(datum_otvaranja), UNIX_TIMESTAMP(datum_zatvaranja), editable from anketa_anketa where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown poll");
		}
		
		$p = new Poll;
		$p->id = $id;
		$p->name = mysql_result($q10,0,0);
		$p->description = mysql_result($q10,0,1);
		$p->openDate = mysql_result($q10,0,2);
		$p->closeDate = mysql_result($q10,0,3);
		if (mysql_result($q10,0,4) == 1) $p->editable=true; else $p->editable=false;
		
		return $p;
	}
	
	// Gets just one poll that is active for all courses in current academic year 
	// (usually there aren't more such polls)
	public static function getActiveForAllCourses() {
		$q10 = DB::query("select aa.id, aa.naziv, aa.opis, UNIX_TIMESTAMP(aa.datum_otvaranja), UNIX_TIMESTAMP(aa.datum_zatvaranja), aa.editable from anketa_anketa as aa, akademska_godina as ag, anketa_predmet as ap where where aa.id=ap.anketa and ap.aktivna=1 and ap.predmet=0 and aa.akademska_godina=ag.id and ag.aktuelna=1 order by id desc limit 1");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("no currently active poll");
		}
		
		$p = new Poll;
		$p->id = mysql_result($q10,0,0);
		$p->name = mysql_result($q10,0,1);
		$p->description = mysql_result($q10,0,2);
		$p->openDate = mysql_result($q10,0,3);
		$p->closeDate = mysql_result($q10,0,4);
		if (mysql_result($q10,0,5) == 1) $p->editable=true; else $p->editable=false;
		
		return $p;
	}

	// Gets just one poll that is active for given course and given academic year
	public static function getActiveForCourse($courseUnitId, $academicYearid) {
		$q10 = DB::query("select aa.id, aa.naziv, aa.opis, UNIX_TIMESTAMP(aa.datum_otvaranja), UNIX_TIMESTAMP(aa.datum_zatvaranja), aa.editable from anketa_anketa as aa, anketa_predmet as ap where where aa.id=ap.anketa and ap.aktivna=1 and ((ap.predmet=$courseUnitId and ap.akademska_godina=$academicYearid) or ap.predmet=0) order by aa.id desc limit 1");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("no currently active poll");
		}
		
		$p = new Poll;
		$p->id = mysql_result($q10,0,0);
		$p->name = mysql_result($q10,0,1);
		$p->description = mysql_result($q10,0,2);
		$p->openDate = mysql_result($q10,0,3);
		$p->closeDate = mysql_result($q10,0,4);
		if (mysql_result($q10,0,5) == 1) $p->editable=true; else $p->editable=false;
		
		return $p;
	}
	
	// Returns true if poll is active for all courses OR for given course, otherwise returns false
	public function isActiveForCourse($courseUnitId, $academicYearId) {
		$q10 = DB::query("select aktivna from anketa_predmet where predmet=0 and anketa=".$this->id);
		if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)!=1) {
			$q20 = DB::query("select aktivna from anketa_predmet where predmet=$courseUnitId and akademska_godina=$academicYearId and anketa=".$this->id);
			if (mysql_num_rows($q20)<1 || mysql_result($q20,0,0)!=1) return false;
		}
		return true;
	}
}

?>