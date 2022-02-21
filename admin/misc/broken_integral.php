<?php

//----------------------------------------
// Detekcija predmeta sa nepravilnim integralnim ispitom
//----------------------------------------

// Tražimo integralne ispite koji nemaju dovoljno parcijalnih ili imaju nepostojeće parcijalne (što bi sve frontend
// trebalo da spriječi, sada ostaje da istražimo zašto nije)

function admin_misc_broken_integral() {
	$predmeti = db_query_varray("SELECT DISTINCT predmet FROM ponudakursa WHERE akademska_godina=16");
	foreach($predmeti as $predmet) {
		$ime = db_get("SELECT naziv FROM predmet WHERE id=$predmet");
		print "Predmet: $ime ($predmet)<br><br>";
		$ispiti = db_query_table("SELECT akp.id, akp.opcije FROM aktivnost_predmet akp, aktivnost_agp aagp WHERE aagp.predmet=$predmet AND aagp.akademska_godina=16 AND aagp.aktivnost_predmet=akp.id AND aagp.predmet=$predmet AND akp.aktivnost=8");
		foreach ($ispiti as $ispit) {
			if (strstr($ispit['opcije'], "Integral")) {
				print "Integralni: " . $ispit['id'] . " - " . $ispit['opcije'] . "<br>";
				$options = explode(",", $ispit['opcije']);
				$integralopt = "";
				foreach($options as $option)
					if (strstr($option, "Integral"))
						$integralopt = $option;
				$parts = explode(":", $integralopt);
				$partials = explode("+", $parts[1]);
				foreach($partials as $partial) {
					$found = false;
					foreach ($ispiti as $ispit2) {
						if ($ispit2['id'] == $partial) $found = true;
					}
					if (!$found) print "Not found $partial<br>";
				}
			}
		}
		print "<br><br>";
	}
}