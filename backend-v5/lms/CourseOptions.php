<?php

// Modul: lms
// Klasa: CourseOptions
// Opis: lms moduli aktivirani na datom predmetu

// Ovo treba biti znatno bolja klasa sa detaljnijim LMS opcijama za kurs


require_once(Config::$backend_path."core/DB.php");

class CourseOptions {
	public $id;
	public $name, $guiName, $newWindow;
	
	public static function fromId($id) {
		// TODO
	}
	
	public static function isModuleActiveForCourse($module, $courseUnitId, $academicYearId) {
		$q10 = DB::query("select count(*) from studentski_modul_predmet as smp, studentski_modul as sm where smp.predmet=$courseUnitId and smp.akademska_godina=$academicYearId and smp.aktivan=1 and smp.studentski_modul=sm.id and sm.modul='$module'");
		if (mysql_result($q10,0,0)>0) return true;
		return false;
	}
}

?>
