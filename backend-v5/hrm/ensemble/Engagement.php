<?php

// Modul: hrm/ensemble
// Klasa: Engagement
// Opis: angažman nastavnika na predmetu


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."hrm/ensemble/Nomination.php");

class Engagement {
	public $courseUnitId, $academicYearId, $personId, $statusId, $status;
	public $person;
	
	public static function fromTeacherAndCourse($personId, $courseUnitId, $academicYearId) {
		$q10 = DB::query("select ast.id, ast.naziv from angazman as a, angazman_status as ast where a.osoba=$personId and a.predmet=$courseUnitId and a.akademska_godina=$academicYearId and a.angazman_status=ast.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nastavnik nije angazovan na predmetu");
		}
		
		$e = new Engagement;
		$e->courseUnitId = $courseUnitId;
		$e->academicYearId = $academicYearId;
		$e->personId = $personId;
		$e->statusId = mysql_result($q10,0,0);
		$e->status = mysql_result($q10,0,1);

		$e->person = 0;

		return $e;
	}
	
	// Gets list of Engagement objects for given course and a.y. (list of teachers engaged)
	public static function getTeachersOnCourse($courseUnitId, $academicYearId) {
		$q10 = DB::query("select a.osoba, ast.id, ast.naziv, o.ime, o.prezime, ns.titula, ss.titula from angazman as a, angazman_status as ast, naucni_stepen as ns, strucni_stepen as ss, osoba as o where a.predmet=$courseUnitId and a.akademska_godina=$academicYearId and a.angazman_status=ast.id and a.osoba=o.id and o.naucni_stepen=ns.id and o.strucni_stepen=ss.id order by ast.id"); 
		// FIXME ast.id bi trebao ići od viših statusa ka nižim (profesor ka asistentima) ali to treba eksplicitno nekako garantovati
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nastavnik nije angazovan na predmetu");
		}
		
		$teachers = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$e = new Engagement;
			$e->courseUnitId = $courseUnitId;
			$e->academicYearId = $academicYearId;
			$e->personId = $r10[0];
			$e->statusId = $r10[1];
			$e->status = $r10[2];

			$e->person = new Person;
			$e->person->id = $r10[0];
			$e->person->name = $r10[3];
			$e->person->surname = $r10[4];
			$e->person->studentIdNr = 0; // teachers don't tend to have student id

			// Određivanje titula - ovo treba na totalno drugi način riješiti
			try {
				$n = Nomination::getLatestForPerson($r10[0]);
				$e->person->titlesPre = $n->rankTitle;
			} catch(Exception $e) {
				// nema izbora, ne radimo ništa
			}
			$e->person->titlesPre .= $r10[5];
			$e->person->titlesPost .= $r10[6];

			array_push($teachers, $e);
		}

		return $teachers;
	}
}

?>
