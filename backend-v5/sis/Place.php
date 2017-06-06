<?php

// Modul: sis
// Klasa: Place
// Opis: naseljeno mjesto


class Place {
	public $id, $name, $country, $municipality;
	
	const MAX_QUERY_RESULTS = 10;

	public static function fromId($id) {
		$place_ar = DB::query_assoc("SELECT m.id id, m.naziv name, o.naziv municipality, d.naziv country, m.opcina_van_bih ovb FROM mjesto m, opcina o, drzava d WHERE m.opcina=o.id AND m.drzava=d.id AND m.id=$id");
		if (!$place_ar) throw new Exception("Unknown person", "404");
		
		if ($place_ar['country'] == "Bosna i Hercegovina")
			$place_ar['municipality'] = $place_ar['ovb'];
		unset($place_ar['ovb']);
		
		return Util::array_to_class($place_ar, "Place");
	}
}

?>
