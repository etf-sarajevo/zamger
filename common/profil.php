<?

// COMMON/PROFIL + opcije korisnika


require_once 'includes/classes/Form.php';

function common_profil() {
	
	global $person;
	global $user_studentska, $user_siteadmin, $conf_skr_naziv_institucije, $conf_promjena_sifre, $conf_skr_naziv_institucije_genitiv;
	
	//var_dump($person['ExtendedPerson']['previousEducation'][0]);
	
	ajax_box(); // Allow JS to create requests to zamger-api
	
	
	?>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
	<link href="static/css/select-2.css" rel="stylesheet" type="text/css">
	<link href="static/css/profile/profile.css" rel="stylesheet" type="text/css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="https://kit.fontawesome.com/cdf2a0a58b.js"></script>

	<script src="static/js/notify.js"></script>
	<script src="static/js/jquery-setup.js"> </script>
	<script src="static/js/profile/profile.js"> </script>
	<?php

	$emails   = $person['email'];
	$email_c  = 1;
	if(!count($emails)) $emails[] = ['x', '', '']; // Ukoliko nema ni jednog email-a unesenog, podesi defaultnu vrijednost
	
	/*
	 * 	If null, print empty string, otherwise change format from yyyy-mm-dd to dd.mm.yyyy
	 */
	
	$dateOfBirth = (empty($person['ExtendedPerson']['dateOfBirth'])) ? '' : date("d.m.Y", strtotime($person['ExtendedPerson']['dateOfBirth']));
	
	// Keywords - translate from API format to format expected by Form class
	$drzava = [];
	foreach (api_call("person/country/search", [ "query" => "" ] )["results"] as $result)
		$drzava[] = [ $result['id'], $result['name'] ];
	$opcina = [];
	foreach (api_call("person/municipality/search", [ "query" => "" ] )["results"] as $result)
		$opcina[] = [ $result['id'], $result['name'] ];
	$skola = [];
	foreach (api_call("person/school/search", [ "query" => "" ] )["results"] as $result) {
		$result['name'] = str_replace("MSŠ", "Mješovita srednja škola", $result['name']);
		$skola[] = [ $result['id'], $result['name'] ];
	}
	$skolska_godina = [];
	foreach (api_call("zamger/year", [] )["results"] as $result) {
		$year = substr($result['name'], strpos($result['name'], "/") + 1);
		if ($result['id'] != 0) $skolska_godina[] = [$result['id'], $year];
	}
	
	// These lists have fixed values
	$nacionalnost = [
		["1", "Bošnjak/Bošnjakinja"],
		["2", "Srbin/Srpkinja"],
		["3", "Hrvat/Hrvatica"],
		["4", "Rom/Romkinja"],
		["5", "Ostalo"],
		["6", "Nepoznato / Nije se izjasnio/la"],
		["9", "Bosanac/Bosanka"],
		["10", "BiH"],
		["11", "Musliman/Muslimanka"]
	];
	$izvoriFinansiranja = [ '1' => 'Roditelji', '2' => 'Primate plaću iz radnog odnosa', '3' => 'Primate stipendiju', '4' => 'Kredit', '5' => 'Ostalo' ];
	$statusAktivnosti   = [ '1' => 'Zaposlen', '2' => 'Nezaposlen', '3' => 'Neaktivan'];
	$statusZaposlenosti = [ '1' => 'Poslodavac / Samozaposlenik', '2' => 'Zaposlenik', '3' => 'Pomažući član porodice'];
	
	/*
	 * 	Require template for new place, municipalitiy, canton and country insert
	 */
	require_once 'includes/profile/add-place.php';
	
	// Unique identification number
	$jmb = isset($person['ExtendedPerson']['jmbg']) ? ((strlen((string)$person['ExtendedPerson']['jmbg']) == 12) ? '0'.$person['ExtendedPerson']['jmbg'] : $person['ExtendedPerson']['jmbg']) : '';
	?>

	<div class="container text-center">
		<div class="col-md-12 text-left border rounded-3">
			<div class="container-fluid bg-white m-0 p-3 ">
				<div class="row pb-3">
					<div class="col-md-6">
						<a class="navbar-brand color-logo" href="#"> <h2>Zahtjev za promjenu ličnih podataka u <br>Informacionom sistemu <?=$conf_skr_naziv_institucije_genitiv?></h2> </a>
					</div>
					<div class="col-md-6 text-right pt-2 mt-2">
						<a href="index.php" class="text-dark"> Naslovna / </a>
						<a href="index.php?sta=common/profil" class="color-logo"><b>Moj profil</b></a>
					</div>

					<div class="col-md-12">
							Popunjavanjem ovog Zahtjeva preuzimate odgovornost za ispravnost unesenih podataka. Ovdje uneseni podaci se mogu koristiti na dokumentima koje izdaje <?=$conf_skr_naziv_institucije?>. Studentska služba zadržava pravo da odbije zahtjev u slučaju da su podaci neispravni.<br>
						    <b>Napomena</b>: Pristupnu šifru možete promijeniti isključivo koristeći <?=$conf_promjena_sifre?>.
						<br> <span class="color-logo download-sv-20 ml-2"><a href="?sta=izvjestaj/sv20&ugovor=da">Preuzmite ŠV-20 obrazac</a></span>
					</div>
				</div>
				<div class="row mb-3 bg-light">
					<div class="col-md-12">
						<h4 class="pt-2">Osnovne informacije</h4>
					</div>
				</div>

				<form class="p-0" action="" method="post" id="update-profile">
					
					<?= Form::hidden('id', $person['id'], ['class' => 'form-control', 'id' => 'personId']) ?>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="name">Ime</label> <!-- Old -->
								<?= Form::text('name', $person['name'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'name', 'aria-describedby' => 'nameHelp', 'required' => 'required']) ?>
								<small id="nameHelp" class="form-text text-muted">Vaše ime</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="surname">Prezime</label> <!-- Old -->
								<?= Form::text('surname', $person['surname'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'surname', 'aria-describedby' => 'surnameHelp', 'required' => 'required']) ?>
								<small id="surnameHelp" class="form-text text-muted">Vaše prezime</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="sex">Spol</label> <!-- Old -->
								<?= Form::select('sex', ['M' => 'Muški', 'Z' => 'Ženski'], $person['ExtendedPerson']['sex'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'sex', 'aria-describedby' => 'sexHelp'], 'spol', '0') ?>
								<small id="sexHelp" class="form-text text-muted">Vaš spol</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="studentIdNr"> Broj indexa </label> <!-- Old -->
								<?= Form::text('studentIdNr', $person['studentIdNr'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'studentIdNr', 'aria-describedby' => 'studentIdNrHelp', (!$user_studentska or !$user_siteadmin) ? 'readonly' : '']) ?>
								<small id="studentIdNrHelp" class="form-text text-muted"> Ovaj podatak može uređivati samo administrator i/ili studentska služba </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="dateOfBirth">Datum rođenja</label> <!-- Old -->
								<?= Form::text('dateOfBirth', $dateOfBirth ?? '', ['class' => 'form-control form-control-sm datepicker', 'id' => 'dateOfBirth', 'aria-describedby' => 'dateOfBirthHelp', 'required' => 'required']) ?>
								<small id="dateOfBirthHelp" class="form-text text-muted">Datum rođenja (DD.MM.YYYY)</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="placeOfBirth">Mjesto rođenja</label>
								<!-- Zbog ljepšeg pregleda i korišenja sa Bootstrap-om, potrebno input wrappati unutar search-container-a -->
								<div class="search-container">
									<?= Form::text('placeOfBirth', $person['ExtendedPerson']['placeOfBirth']['name'] ?? '', ['class' => 'form-control placeSearch', 'id' => 'placeOfBirth', 'idVal' => $person['ExtendedPerson']['placeOfBirth']['id'] ?? '', 'municipality' => 'Municipality', 'country' => 'Country']) ?>
								</div>
								<small id="placeOfBirthHelp" class="form-text text-muted">
									Vaše mjesto rođenja.<br>
									Ukoliko ne možete pronaći vaše mjesto, molimo da isto unesete koristeći <b><span class="color-logo insert-place" idFor="placeOfBirth">obrazac za unos</span></b> !
								</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="Municipality">Općina rođenja</label> <!-- TODO !? -->
								<?= Form::text('Municipality',$person['ExtendedPerson']['placeOfBirth']['Municipality']['name'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'Municipality', 'aria-describedby' => 'MunicipalityHelp', 'readonly', 'idVal' => $person['ExtendedPerson']['placeOfBirth']['Municipality']['id'] ?? '']) ?>
									<small id="MunicipalityHelp" class="form-text text-muted">Općina rođenja - Ukoliko nije ispravno, promijenite Mjesto rođenja</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="Country">Država rođenja</label> <!-- TODO -->
								<?= Form::select('Country', $drzava, $person['ExtendedPerson']['placeOfBirth']['Country']['id'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'Country', 'aria-describedby' => 'CountryHelp', 'disabled' => 'true'], 'državu rođenja') ?>
								<small id="CountryHelp" class="form-text text-muted">Država rođenja - Ukoliko nije ispravno, promijenite Mjesto rođenja</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="nationality">Državljanstvo</label> <!-- TODO -->
								<?= Form::select('nationality', $drzava, $person['ExtendedPerson']['nationality'] ?? '', ['class' => 'form-control form-control-sm select-2', 'id' => 'nationality', 'aria-describedby' => 'nationalityHelp'], 'državljanstvo') ?>
								<small id="nationalityHelp" class="form-text text-muted">Odaberite državu čiji ste državljanin</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="ethnicity">Nacionalna pripadnost</label> <!-- Old -->
								<?= Form::select('ethnicity', $nacionalnost, $person['ExtendedPerson']['ethnicity'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'ethnicity', 'aria-describedby' => 'ethnicityHelp'], 'nacionalnost') ?>
								<small id="ethnicityHelp" class="form-text text-muted">Upisuju samo državljani BiH </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="jmbg">JMBG</label> <!-- Old -->
								<?= Form::number('jmbg', $jmb, ['class' => 'form-control form-control-sm', 'id' => 'jmbg', 'aria-describedby' => 'jmbgHelp', 'required' => 'required', (!$user_studentska or !$user_siteadmin) ? 'readonly' : '']) ?>
								<small id="jmbgHelp" class="form-text text-muted"> Vaš jedinstveni matični broj. Ovaj podatak može uređivati samo administrator i/ili studentska služba </small>
							</div>
						</div>
					</div>

					<hr>
					<div class="row mb-3 bg-light">
						<div class="col-md-12">
							<h4 class="pt-2">Prebivalište</h4>
						</div>
						<div class="col-md-12">
							<small id="prebivalisteHelp" class="form-text text-muted"> Prebivalište je adresa na koju ste prijavljeni u IDDEEA i koja je navedena na ličnim dokumentima i prijavi prebivališta. Molimo da provjerite prije unosa podataka. </small>
						</div>
					</div>

					<!-- Prebivalište -->
					<div class="row pt-2">
						<div class="col-md-6">
							<div class="form-group">
								<label for="residenceAddress">Adresa prebivališta</label>
								<?= Form::text('residenceAddress', $person['ExtendedPerson']['residenceAddress'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'residenceAddress']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="placeOfBirth">Mjesto prebivališta</label>
								<div class="search-container">
									<?= Form::text('residencePlace', $person['ExtendedPerson']['residencePlace']['name'] ?? '', ['class' => 'form-control placeSearch', 'id' => 'residencePlace', 'idVal' => $person['ExtendedPerson']['residencePlace']['id'] ?? '', 'municipality' => 'residenceMunicipality', 'country' => 'residenceCountry']) ?>
								</div>
								<small id="placeOfBirthHelp" class="form-text text-muted">
									Vaše mjesto prebivališta.<br>
									Ukoliko ne možete pronaći vaše mjesto, molimo da isto unesete koristeći <b><span class="color-logo insert-place" idFor="residencePlace">obrazac za unos</span></b> !
								</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="residenceMunicipality">Općina prebivališta</label>
								<?= Form::text('residenceMunicipality',$person['ExtendedPerson']['residencePlace']['Municipality']['name'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'residenceMunicipality', 'readonly', 'idVal' => $person['ExtendedPerson']['residencePlace']['Municipality']['id'] ?? '']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="residenceCountry">Država prebivališta</label>
								<?= Form::select('residenceCountry', $drzava, $person['ExtendedPerson']['residencePlace']['Country']['id'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'residenceCountry', 'disabled' => 'true'], 'državu prebivališta') ?>
							</div>
						</div>
					</div>
					
					<hr>
					<div class="row mb-3 bg-light">
						<div class="col-md-12">
							<h4 class="pt-2">Kontakt podaci</h4>
						</div>
					</div>

					<!-- Boravište, telefon, emails -->
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="addressStreetNo">Adresa boravišta</label>
								<?= Form::text('addressStreetNo', $person['ExtendedPerson']['addressStreetNo'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'addressStreetNo', 'aria-describedby' => 'addressStreetNoHelp']) ?>
								<small id="addressStreetNoHelp" class="form-text text-muted"> Boravište je mjesto stanovanja na kojem boravite. </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="addressPlace">Mjesto boravišta</label>
								<!-- Zbog ljepšeg pregleda i korišenja sa Bootstrap-om, potrebno input wrappati unutar search-container-a -->
								<div class="search-container">
									<?= Form::text('addressPlace', $person['ExtendedPerson']['addressPlace']['name'] ?? '', ['class' => 'form-control placeSearch', 'id' => 'addressPlace', 'idVal' => $person['ExtendedPerson']['addressPlace']['id'] ?? '',  'municipality' => 'addressMunicipality', 'country' => 'addressCountry']) ?>
								</div>
								<small id="placeOfBirthHelp" class="form-text text-muted">
									Vaše mjesto boravišta.<br>
									Ukoliko ne možete pronaći vaše mjesto, molimo da isto unesete koristeći <b><span class="color-logo insert-place" idFor="addressPlace">obrazac za unos</span></b> !
								</small>
							</div>
						</div>
					</div>
					<div class="row d-none">
						<div class="col-md-6">
							<div class="form-group">
								<label for="addressMunicipality">Općina boravišta</label>
								<?= Form::text('addressMunicipality',$person['ExtendedPerson']['addressPlace']['Municipality']['name'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'addressMunicipality', 'aria-describedby' => 'addressMunicipalityHelp', 'readonly', 'idVal' => $person['ExtendedPerson']['addressPlace']['Municipality']['id'] ?? '']) ?>
								<small id="addressMunicipalityHelp" class="form-text text-muted">Općina boravišta - popunjava se automatski u odnosu na mjesto</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="addressCountry">Država boravišta</label>
								<?= Form::select('addressCountry', $drzava, $person['ExtendedPerson']['addressPlace']['Country']['id'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'addressCountry', 'aria-describedby' => 'addressCountryHelp', 'disabled' => 'true'], 'državu boravišta') ?>
								<small id="addressCountryHelp" class="form-text text-muted">Država rođenja - popunjava se automatski u odnosu na mjesto</small>
							</div>
						</div>
					</div>

					<div class="row email-wrapper">
						<div class="col-md-6">
							<div class="form-group">
								<label for="phone">Telefon</label> <!-- Old -->
								<?= Form::text('phone', $person['ExtendedPerson']['phone'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'phone', 'aria-describedby' => 'phoneHelp', 'required' => 'required']) ?>
								<small id="phoneHelp" class="form-text text-muted">Format telefona: 00387 6X XXX XXX </small>
							</div>
						</div>
						<?php
						foreach ($emails as $email){
							?>
							<div class="<?= ($email_c < count($emails)) ? 'col-md-6' : ((count($emails) % 2 == 0) ? 'col-md-12' : 'col-md-6')  ?>">
								<div class="form-group">
									<label for="email">Email</label> <!-- Old -->
									<?= Form::email('email[]', $email['address'] ?? '', ['class' => 'form-control form-control-sm sm-emails', 'id' => 'email'.$email['id'], 'aria-describedby' => 'emailHelp', 'no' => $email_c++, ($email['account_address']) ? 'readonly' : '']) ?>
									<?= Form::hidden('email_id[]', $email['id'], ['class' => 'form-controll sm-emails-id']) ?>
									<?= Form::hidden('acc_addr[]', $email['account_address'], ['class' => 'form-controll sm-emails-accaddr']) ?>
									<small id="emailHelp" class="form-text text-muted">
										<?php
										if($email_c == 2) print 'Vaše privatni email ( ukoliko imate još email adresa, možete ih dodati <span class="color-logo append-email"><b>ovdje</b></span> )';
										if(!$email['account_address']) {
											?>
											- <span class="text-danger remove-email remove-email-db" id="<?= $email[0] ?>"><b>Obrišite ovaj email</b></span>
											<?php
										}
										?>
									</small>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<hr>
					<div class="row mb-3 bg-light">
						<div class="col-md-12">
							<h4 class="pt-2">Podaci za ŠV-20 obrazac</h4>
						</div>
						<div class="col-md-12">
							<small id="sv20Help" class="form-text text-muted"> Osobe koje nisu studenti ne moraju popunjavati ove podatke. </small>
						</div>
					</div>

					<!-- Informacije o roditeljima -->
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fathersName">Ime oca</label> <!-- Old -->
								<?= Form::text('fathersName', $person['ExtendedPerson']['fathersName'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'fathersName', 'aria-describedby' => 'fathersNameHelp', 'required' => 'required']) ?>
								<small id="fathersNameHelp" class="form-text text-muted"> Unesite ime Vašeg oca </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="fathersSurname">Prezime oca</label> <!-- Old -->
								<?= Form::text('fathersSurname', $person['ExtendedPerson']['fathersSurname'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'fathersSurname', 'aria-describedby' => 'fathersSurname', 'required' => 'required']) ?>
								<small id="fathersSurnameHelp" class="form-text text-muted"> Unesite prezime Vašeg oca </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="mothersName">Ime majke</label> <!-- Old -->
								<?= Form::text('mothersName', $person['ExtendedPerson']['mothersName'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'mothersName', 'aria-describedby' => 'mothersNameHelp', 'required' => 'required']) ?>
								<small id="mothersNameHelp" class="form-text text-muted"> Unesite ime Vaše majke </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="mothersSurname">Prezime majke</label> <!-- Old -->
								<?= Form::text('mothersSurname', $person['ExtendedPerson']['mothersSurname'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'mothersSurname', 'aria-describedby' => 'mothersSurnameHelp', 'required' => 'required']) ?>
								<small id="mothersSurnameHelp" class="form-text text-muted"> Unesite prezime Vaše majke </small>
							</div>
						</div>
					</div>

					<hr>

					<!-- Srednja škola -->
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="naziv">Naziv prethodno završenog obrazovanja</label> <!-- Old -->
								<?
								$schools = count($person['ExtendedPerson']['previousEducation']);
								if ($schools > 0) {
									$lastSchool = $person['ExtendedPerson']['previousEducation'][$schools - 1]['School']['id'];
									$yearCompleted = $person['ExtendedPerson']['previousEducation'][$schools - 1]['yearCompleted']['id'];
								} else
									$lastSchool = $yearCompleted = 0;
								print Form::select('skola', $skola, $lastSchool, ['class' => 'form-control form-control-sm select-2', 'id' => 'skola', 'aria-describedby' => 'prethodnoObrazHelp'], 'školu')
								?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="godina">Godina</label> <!-- New -->
								<?= Form::select('godina_zavrsetka', $skolska_godina, $yearCompleted ?? '', ['class' => 'form-control form-control-sm', 'id' => 'godina_zavrsetka', 'aria-describedby' => 'yearHelp'], 'godinu') ?>
							</div>
						</div>
					</div>
					<!--div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="opcina">Općina u kojoj ste završili</label>
								<?= Form::select('opcina', $opcina, '', ['class' => 'form-control form-control-sm select-2', 'id' => 'opcina', 'aria-describedby' => 'prethodnoObrazHelp'], 'općinu') ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="tipskole"> Tip škole </label>
								<?= Form::select('tipskole', ['GIMNAZIJA' => 'GIMNAZIJA', 'ELEKTROTEHNICKA' => 'ELEKTROTEHNICKA', 'TEHNICKA' => 'TEHNICKA', 'STRUCNA' => 'STRUCNA', 'MSS' => 'MSS'], 'GIMNAZIJA', ['class' => 'form-control form-control-sm', 'id' => 'tipskole', 'aria-describedby' => 'prethodnoObrazHelp']) ?>
							</div>
						</div>
					</div-->
					<div class="row">
						<!--div class="col-md-12">
							<div class="form-group">
								<label for="domaca">Da li je škola u BiH</label>
								<?= Form::select('domaca', ['1' => 'Da', '2' => 'Ne'], '1', ['class' => 'form-control form-control-sm', 'id' => 'domaca', 'aria-describedby' => 'prethodnoObrazHelp']) ?>
							</div>
						</div-->
						<div class="col-md-12">
							<small id="prethodnoObrazHelp" class="form-text text-muted">
								Naziv prethodno završenog obrazovanja -Upisuje se naziv prethodno završenog obrazovanja /srednje, više, visoke škole/, fakulteta, akademije koju ste završili prije upisa na ovu visokoškolsku ustanovu, tj. prije upisa na određeni studijski program. Ako ste prije studirali na nekoj visokoškolskoj ustanovi a niste diplomirali, upisujete naziv srednje škole koju ste prethodno završili.
								<br>
								Godina prethodno završenog obrazovanja - Upisujete godinu završnog ispita - mature ili godine kada ste dobili završno svjedočanstvo za srednju školu, odnosno godinu diplomiranja - završetka studija na određenom višem ili visokoškolskom studiju.
							</small>
						</div>
					</div>

					<hr>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="sourceOfFunding"> Izvori finansiranja studenta za vrijeme studija </label> <!-- New -->
								<?= Form::select('sourceOfFunding', $izvoriFinansiranja, $person['ExtendedPerson']['sourceOfFunding'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'sourceOfFunding', 'aria-describedby' => 'sourceOfFundingHelp'], 'izvor finansiranja') ?>
								<small id="sourceOfFundingHelp" class="form-text text-muted"> Ukoliko je iz više izvora, odaberite pretežiti izvor </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="activityStatusParent">Status u aktivnosti roditelja - izdržavatelja </label> <!-- New -->
								<?= Form::select('activityStatusParent', $statusAktivnosti, $person['ExtendedPerson']['activityStatusParent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'activityStatusParent', 'aria-describedby' => 'activityStatusHelp'], 'status aktivnosti roditelja') ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="activityStatusStudent">Status u aktivnosti studenta</label> <!-- New -->
								<?= Form::select('activityStatusStudent', $statusAktivnosti, $person['ExtendedPerson']['activityStatusStudent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'activityStatusStudent', 'aria-describedby' => 'activityStatusHelp'], 'status aktivnosti studenta') ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="activityStatusHelp" class="form-text text-muted">
								<b>Zaposlen</b> , ako roditelj - izdržavatelj ili student imaju neki posao kojim obezbjeđuju sredstva za život.
								<b>Nezaposlen</b> , ako roditelj - izdržavatelj ili student nemaju nikakav posao kojim obezbjeđuju sredstva za život, ali traže posao i spremni su da ponu da ga obavljaju ukoliko bi im posao bio ponuđen.
								<b>Neaktivan</b> , ako je roditelj - izdržavatelj ili student nesposoban za rad, domaćica, penzioner, student.
							</small>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="occupationParent">Zanimanje roditelja - izdržavatelja </label> <!-- New -->
								<?= Form::text('occupationParent', $person['ExtendedPerson']['occupationParent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'occupationParent', 'aria-describedby' => 'occupationHelp']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="occupationStudent">Zanimanje studenta</label> <!-- New -->
								<?= Form::text('occupationStudent', $person['ExtendedPerson']['occupationStudent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'occupationStudent', 'aria-describedby' => 'occupationHelp']) ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="occupationHelp" class="form-text text-muted">
								<b>Zanimanje</b> - vrsta posla koju osoba obavlja u preduzeu, prodavnici, tvornici, itd. Potrebno je upisati što precizniji opis zanimanja, odnosno vrste posla koji osoba obavlja u preduzeu, prodavnici, na poljoprivrednom gazdinstvu, i slino. Zanimanje ne mora biti u vezi sa stepenom obrazovanja ili specijalizacijom nego se veže za konkretan posao koji obavlja osoba, npr. pravnik koji pruža taksi usluge po zanimanju je taksista, a ne pravnik. U svrhu omoguavanja šifriranja odgovori moraju biti jasni i kompletni. Odgovor treba da je što detaljniji, npr.: individualni poljoprivrednik, rudar, blagajnik, profesor, elektrotehniar, laborant, slovoslaga i dr.
							</small>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="employmentStatusParent">Status u zaposlenosti roditelja - izdržavatelja </label> <!-- New -->
								<?= Form::select('employmentStatusParent', $statusZaposlenosti, $person['ExtendedPerson']['employmentStatusParent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'employmentStatusParent', 'aria-describedby' => 'employmentStatusHelp'], 'status zaposlenosti roditelja') ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="employmentStatusStudent">Status u zaposlenosti studenta</label> <!-- New -->
								<?= Form::select('employmentStatusStudent', $statusZaposlenosti, $person['ExtendedPerson']['employmentStatusStudent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'employmentStatusStudent', 'aria-describedby' => 'employmentStatusHelp'], 'status zaposlenosti studenta') ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="employmentStatusHelp" class="form-text text-muted">
								<b>Poslodavci/samozaposlenici</b> su poslodavci koji upravljaju poslovnim subjektom i zapošljavaju jednog ili više zaposlenika, kao i osobe koje rade za vlastiti raun i ne zapošljavaju zaposlenike, kao i vlasnici poljoprivrednog gazdinstva.
								<b>Zaposlenik</b> je osoba koja radi za poslodavca u državnom/privatnom sektoru i za taj rad prima naknadu /u novcu ili naturi/
								<b>Pomažući član porodice</b> je osoba koja radi bez plae u preduzeću, obrtu ili poljoprivrednom gazdinstvu kojeg vodi njen srodnik s kojim živi u istom domaćinstvu. Ova kategorija uključuje npr.: sina ili kćerku koji rade u roditeljskom biznisu ili roditeljskom poljoprivrednom gazdinstvu bez plaćanja.
							</small>
						</div>
					</div>

					<br>

					<div class="row">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-secondary btn-sm "><small>Ažurirajte informacije</small></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
	
}


?>
