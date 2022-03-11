<?

// COMMON/PROFIL + opcije korisnika



function common_profil() {
	
	global $person;
	global $user_studentska, $user_siteadmin, $conf_skr_naziv_institucije, $conf_promjena_sifre, $conf_skr_naziv_institucije_genitiv;
	$isAdmin = $user_studentska || $user_siteadmin;
	
	$title = '<div class="col-md-6">
						<a class="navbar-brand color-logo" href="#"> <h2>Zahtjev za promjenu ličnih podataka u <br>Informacionom sistemu ' . $conf_skr_naziv_institucije_genitiv . '</h2> </a>
					</div>
					<div class="col-md-6 text-right pt-2 mt-2">
						<a href="index.php" class="text-dark"> Naslovna / </a>
						<a href="index.php?sta=common/profil" class="color-logo"><b>Moj profil</b></a>
					</div>
					
					<div class="col-md-12">
						Popunjavanjem ovog Zahtjeva preuzimate odgovornost za ispravnost unesenih podataka. Ovdje uneseni podaci se mogu koristiti na dokumentima koje izdaje ' . $conf_skr_naziv_institucije . '. Studentska služba zadržava pravo da odbije zahtjev u slučaju da su podaci neispravni.<br>
						<b>Napomena</b>: Pristupnu šifru možete promijeniti isključivo koristeći ' . $conf_promjena_sifre . '.
						<br> <span class="color-logo download-sv-20 ml-2"><a href="?sta=izvjestaj/sv20&ugovor=da">Preuzmite ŠV-20 obrazac</a></span>
					</div>';
	
	require_once "includes/profile/profile.php";
	
	includes_profile($title, $person, $isAdmin);
}


?>
