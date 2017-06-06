<?php

// Modul: core
// Klasa: Person
// Opis: osoba


class Person {
	public $id, $name, $surname, $studentIdNr, $login;
	public $titlesPre, $titlesPost; // prefix and postfix titles
	public $ExtendedPerson; // reference to ExtendedPerson class
	
	const MAX_QUERY_RESULTS = 10;

	public static function fromId($id) {
		$person = DB::query_assoc("select o.id id, o.ime name, o.prezime surname, o.brindexa studentIdNr, o.id ExtendedPerson from osoba as o, auth as a where o.id=$id");
		if (!$person) throw new Exception("Unknown person", "404");
		$person = Util::array_to_class($person, "Person", array("ExtendedPerson"));
		// User might not have a login
		$person->login = DB::get("SELECT login FROM auth WHERE id=$id");
		// Convert person Id info UnresolvedClass object
		return $person;
	}

	public static function fromLogin($login) {
		$login = DB::escape($login);
		$person = DB::query_assoc("select o.id id, o.ime name, o.prezime surname, o.brindexa studentIdNr, a.login login, o.id ExtendedPerson from osoba as o, auth as a where o.id=a.id and a.login='$login'");
		if (!$person) throw new Exception("Unknown person", "404");
		return Util::array_to_class($person, "Person", array("ExtendedPerson"));
	}

	public static function search($query) {
		// FIXME users without login can't be found
		$r = array();
		$r['query'] = $query;
		$r['results'] = array();
	
		if (!preg_match("/\w/",$query)) { return $r; }

		$query = str_replace("(","",$query);
		$query = str_replace(")","",$query);
		$parts = explode(" ",$query);
		$sql = "";

		foreach($parts as $part) {
			$part = DB::escape($part);
			if ($sql != "") $sql .= " and ";
			$sql .= "(o.ime like '%$part%' or o.prezime like '%$part%' or a.login like '%$part%' or o.brindexa like '%$part%')";
		}
		
		$persons = DB::query_table("SELECT o.id id, o.ime name, o.prezime surname, o.brindexa studentIdNr, a.login login, o.id ExtendedPerson from auth as a, osoba as o WHERE a.id=o.id AND $sql ORDER BY o.prezime, o.ime LIMIT " . Person::MAX_QUERY_RESULTS);
		foreach($persons as &$person) {
			$person = Util::array_to_class($person, "Person", array("ExtendedPerson"));
		}
		$r['results'] = $persons;
		return $r;
	}
	
	// Tituliraj
	function getTitles() {
		$qtitles = DB::query_assoc("select naucni_stepen, strucni_stepen from osoba where id=" . $this->id);
		if ($qtitles['naucni_stepen'])
			$this->titlesPre = DB::get("select titula from naucni_stepen where id=" . $qtitles['naucni_stepen']);
		if ($qtitles['strucni_stepen'])
			$this->titlesPost = DB::get("select titula from strucni_stepen where id=" . $qtitles['strucni_stepen']);
	
		$zvanje = DB::get("select z.titula from izbor as i, zvanje as z where i.osoba=" . $this->id . " and i.zvanje=z.id and (i.datum_isteka>=NOW() or i.datum_isteka='0000-00-00')");
		if ($zvanje) $this->titlesPre = $zvanje . " " . $this->titlesPre;
	}
}

?>
