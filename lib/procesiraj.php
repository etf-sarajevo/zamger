<?

function spasiPodatkeHR($userid) {
	$djevojacko= my_escape($_REQUEST['djevojacko']);
	$vozacka= intval($_REQUEST['vozacka']);
	$mjezik= intval($_REQUEST['mjezik']);
	$mobitel= intval($_REQUEST['mobitel']);
	$nacin_stanovanja= intval($_REQUEST['nacin_stanovanja']);		
	myquery("update osoba set djevojacko_prezime='$djevojacko', maternji_jezik=$mjezik , vozacka_dozvola=$vozacka , mobilni_telefon='$mobitel', nacin_stanovanja=$nacin_stanovanja where id=$userid");
}

function evidentirajUsavrsavanje($userid) {
	$naziv= my_escape($_REQUEST['naziv_usavrsavanja']);
	$datum= strtotime(my_escape($_REQUEST['datum_usavrsavanja']));
	$institucija= my_escape($_REQUEST['naziv_institucije']);
	$kvalifikacija= my_escape($_REQUEST['kvalifikacija']);
	myquery("INSERT INTO hr_usavrsavanje (fk_osoba ,datum ,naziv_usavrsavanja ,obrazovna_institucija ,kvalifikacija)VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$institucija',  '$kvalifikacija')");
}


function evidentirajNaucniRad($userid) {
	$naziv= my_escape($_REQUEST['naziv_rada']);
	$datum= strtotime(my_escape($_REQUEST['datum_rada']));
	$naziv_casopisa= my_escape($_REQUEST['naziv_casopisa']);
	$naziv_izdavaca= my_escape($_REQUEST['naziv_izdavaca']);
	myquery("INSERT INTO hr_naucni_radovi (fk_osoba ,datum ,naziv_rada ,naziv_casopisa ,naziv_izdavaca)VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$naziv_casopisa',  '$naziv_izdavaca')");
}

function evidentirajMentorstvo($userid) {
	$datum= strtotime(my_escape($_REQUEST['datum_mentorstva']));
	$ime_kandidata= my_escape($_REQUEST['ime_kandidata']);
	$naziv_teme= my_escape($_REQUEST['naziv_teme']);
	$mfakultet= intval($_REQUEST['mfakultet']);
	$mmentorstvo= intval($_REQUEST['mmentorstvo']);
	myquery("INSERT INTO hr_mentorstvo (fk_osoba ,datum ,ime_kandidata ,naziv_teme ,fk_fakultet,fk_vrsta_mentora)VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$ime_kandidata',  '$naziv_teme',  $mfakultet,$mmentorstvo)");
}

function evidentirajPublikaciju($userid) {
	$datum= strtotime(my_escape($_REQUEST['datum_publikacije']));
	$naziv= my_escape($_REQUEST['naziv_publikacije']);
	$casopis= my_escape($_REQUEST['naziv_ci']);
	$fk_tip_publikacije= intval($_REQUEST['vrsta_publikacije']);
	myquery("INSERT INTO  hr_publikacija (fk_osoba,datum ,naziv ,casopis ,fk_tip_publikacije) VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$casopis',  $fk_tip_publikacije)");
}


function evidentirajNagradu($userid) {
	$datum= strtotime(my_escape($_REQUEST['datum_nagrade']));
	$naziv= my_escape($_REQUEST['naziv_nagrade']);
	$opis= my_escape($_REQUEST['opis_nagrade']);
	myquery("INSERT INTO `hr_nagrade_priznanja` (`fk_osoba`, `datum`, `naziv`, `opis`) VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$opis')");
}

function evidentirajJezik($userid) {
	$jezik= intval($_REQUEST['jezik']);
	$razumjevanje= intval($_REQUEST['razumjevanje']);
	$govor= intval($_REQUEST['govor']);
	$pisanje= intval($_REQUEST['pisanje']);
	myquery("INSERT INTO `hr_kompetencije` (`fk_osoba`, `jezik`, `razumjevanje`, `govor`, pisanje) VALUES ('$userid', $jezik, $razumjevanje,$govor, $pisanje )");
}

?>