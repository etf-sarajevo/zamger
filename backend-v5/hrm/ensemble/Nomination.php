<?php

// Modul: hrm/ensemble
// Klasa: Nomination (provjeriti terminologiju)
// Opis: izbori i imenovanja 


require_once(Config::$backend_path."core/DB.php");

class Nomination {
	public $id, $personId, $rankId, $rankName, $rankTitle, /* rank = zvanje, provjeriti */ $dateNamed, $dateExpired;
	
	
	public static function fromId($id) {
		// Trenutno nema polja ID u bazi :(
		// a ni indeksa
	}

	// Latest nomination for person
	public static function getLatestForPerson($personId) {
		$q10 = DB::query("select z.id, z.naziv, z.titula, UNIX_TIMESTAMP(i.datum_izbora), UNIX_TIMESTAMP(i.datum_isteka) from zvanje as z, izbor as i where i.zvanje=z.id and i.osoba=$personId order by i.datum_izbora desc limit 1");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nastavnik nikada nije izabran");
		}

		$n = new Nomination;
		$n->id = 0; // FIXME
		$n->personId = $personId;
		$n->rankId = mysql_result($q10,0,0);
		$n->rankName = mysql_result($q10,0,1);
		$n->rankTitle = mysql_result($q10,0,2);
		$n->dateNamed = mysql_result($q10,0,3);
		$n->dateExpired = mysql_result($q10,0,4);
		return $n;
	}
}

?>
