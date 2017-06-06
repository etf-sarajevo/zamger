<?php

// Modul: sis
// Klasa: ExtendedPerson
// Opis: dodatni podaci o osobi za SIS


require_once(Config::$backend_path."sis/Place.php");

class ExtendedPerson {
	public $id, $fathersName, $fathersSurname, $mothersName, $mothersSurname, $sex, $dateOfBirth, $placeOfBirth, $ethnicity, $nationality;
	public $jmbg, $addressStreetNo, $addressPlace, $phone;
	
	const MAX_QUERY_RESULTS = 10;

	public static function fromId($id) {
		$experson = DB::query_assoc("select o.id id, o.imeoca fathersName, o.prezimeoca fathersSurname, o.imemajke mothersName, o.prezimemajke mothersSurname, o.spol sex, 
		o.datum_rodjenja dateOfBirth, o.mjesto_rodjenja placeOfBirth, o.nacionalnost ethnicity, o.drzavljanstvo nationality, o.jmbg jmbg, o.adresa addressStreetNo, o.adresa_mjesto addressPlace, o.telefon phone
		FROM osoba as o where o.id=$id");
		if (!$experson) throw new Exception("Unknown person", "404");
		
		// Fix sex
		if ($experson['sex'] == "Z") $experson['sex'] = "F";
		
		$exp = Util::array_to_class($experson, "ExtendedPerson");
				
		// Resolve classes
		if ($exp->placeOfBirth > 0)
			$exp->placeOfBirth = Place::fromId($exp->placeOfBirth);
		else
			$exp->placeOfBirth = new stdClass;
		if ($exp->addressPlace > 0)
			$exp->addressPlace = Place::fromId($exp->addressPlace);
		else
			$exp->addressPlace = new stdClass;
			
		return $exp;
	}
}

?>
