<?php

// Modul: core
// Klasa: Person
// Opis: osoba


require_once(Config::$backend_path."core/DB.php");

class Person {
	public $id, $name, $surname, $studentIdNr, $login;
	public $titlesPre, $titlesPost; // prefix and postfix titles
	// Da li ovo uopšte treba biti u Person, u sis/ExtendedPerson ili negdje pod hrm ?

	public static function fromId($id) {
		$q1 = DB::query("select o.ime, o.prezime, o.brindexa, a.login from osoba as o, auth as a where o.id=$id and a.id=$id");
		if (mysql_num_rows($q1) < 1) {
			throw new Exception("nepoznata osoba");
		}
		
		$p = new Person;
		$p->id = $id;
		$p->name = mysql_result($q1,0,0);
		$p->surname = mysql_result($q1,0,1);
		$p->studentIdNr = mysql_result($q1,0,2);
		$p->login = mysql_result($q1,0,3);
		// TODO dodati ostalo što neće ići u ExtendedPerson - odlučiti
		
		return $p;
	}

	public static function fromLogin($login) {
		$q1 = DB::query("select o.id, o.ime, o.prezime, o.brindexa from osoba as o, auth as a where o.id=a.id and a.login='$login'");
		if (mysql_num_rows($q1) < 1) {
			throw new Exception("nepoznata osoba");
		}
		
		$p = new Person;
		$p->id = mysql_result($q1,0,0);
		$p->name = mysql_result($q1,0,1);
		$p->surname = mysql_result($q1,0,2);
		$p->studentIdNr = mysql_result($q1,0,3);
		$p->login = $login;
		// TODO dodati ostalo što neće ići u ExtendedPerson - odlučiti
		
		return $p;
	}

	public static function search($query) {
		if (!preg_match("/\w/",$query)) { return array(); }

		$query = str_replace("(","",$query);
		$query = str_replace(")","",$query);
		$parts = explode(" ",$query);
		$sql = "";

		foreach($parts as $part) {
			if ($sql != "") $sql .= " and ";
			$sql .= "(o.ime like '%$part%' or o.prezime like '%$part%' or a.login like '%$part%' or o.brindexa like '%$part%')";
		}

		$q10 = myquery("select o.id, o.ime, o.prezime, o.brindexa, a.login from auth as a, osoba as o where a.id=o.id and $sql order by o.prezime, o.ime");
		$persons = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$p = new Person;
			$p->id = $r10[0];
			$p->name = $r10[1];
			$p->surname = $r10[2];
			$p->studentIdNr = $r10[3];
			$p->login = $r10[4];
			array_push($persons, $p);
		}

		return $persons;
	}
}

?>
