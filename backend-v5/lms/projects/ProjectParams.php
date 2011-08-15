<?php

// Modul: lms/projects
// Klasa: ProjectParams
// Opis: parametri projekata na predmetu

require_once(Config::$backend_path."core/DB.php");

class ProjectParams {
	public $id;
	public $courseUnitId, $academicYearId, $minTeams, $maxTeams, $minTeamMembers, $maxTeamMembers, $locked;
	
	// Gets just one poll that is active for all courses in current academic year 
	// (usually there aren't more such polls)
	public static function fromCourse($courseUnitId, $academicYearId) {
		$q10 = DB::query("select min_timova, max_timova, min_clanova_tima, max_clanova_tima, zakljucani_projekti from predmet_projektni_parametri where predmet=$courseUnitId and akademska_godina=$academicYearId");
		if (mysql_num_rows($q10) < 1) {
			throw new Exception("no params set for project");
		}

		$pp = new ProjectParams;
		$pp->courseUnitId = $courseUnitId;
		$pp->academicYearId = $academicYearId;
		$pp->minTeams = mysql_result($q10,0,0);
		$pp->maxTeams = mysql_result($q10,0,1);
		$pp->minTeamMembers = mysql_result($q10,0,2);
		$pp->maxTeamMembers = mysql_result($q10,0,3);
		if (mysql_result($q10,0,4) == 1) $pp->locked = true; else $pp->locked = false;
		
		return $pp;
	}
}

?>