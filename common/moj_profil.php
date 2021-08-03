<?php

require_once 'common/classes/Form.php';

function common_moj_profil(){
	global $userid;
	
	$user     = db_query_assoc("select * from osoba where id=$userid");
	$user_a   = db_query_assoc("select * from osoba__dodatno where osoba = $userid");
	$email    = db_query_assoc("select * from email where osoba = $userid");
	
	// Šifarnici
	$nacionalnost = db_query("select id,naziv from nacionalnost order by naziv")->fetch_all();
	$drzava       = db_query("select id,naziv from drzava order by naziv")->fetch_all();
	$kanton       = db_query("select id,naziv from kanton order by naziv")->fetch_all();
	
	$izvoriFinansiranja = [ 'Roditelji' => 'Roditelji', 'Primate plaću iz radnog odnosa' => 'Primate plaću iz radnog odnosa', 'Primate stipendiju' => 'Primate stipendiju', 'Kredit' => 'Kredit', 'Ostalo' => 'Ostalo' ];
	$statusAktivnosti   = ['Zaposlen' => 'Zaposlen', 'Nezaposlen', 'Nezaposlen', 'Neaktivan' => 'Neaktivan'];
	$statusZaposlenosti = ['Poslodavac / Samozaposlenik' => 'Poslodavac / Samozaposlenik', 'Zaposlenik' => 'Zaposlenik', 'Pomažući član porodice' => 'Pomažući član porodice'];
	
	?>
	
	<!-- Include skriptu za AJAX request -->
	<script type="text/javascript" src="static/js/includes/profil.js"></script>
	
	<div class="container text-center">
		<div class="col-md-12 text-left border rounded-3">
			<div class="container-fluid bg-white m-0 p-3 ">
				<div class="row pb-3">
					<div class="col-md-6">
						<a class="navbar-brand color-logo" href="#"> <h2>Moj karton</h2> </a>
					</div>
					<div class="col-md-6 text-right pt-2 mt-2">
						<a href="index.php" class="text-dark"> Naslovna / </a>
						<a href="index.php?sta=common/moj_profil" class="color-logo"><b>Moj profil</b></a>
					</div>
					
					<div class="col-md-12">
						<div class="btn-group">
							Zahtjev za promjenu ličnih podataka u Informacionom sistemu ETFa.
							<b class="pl-2">Napomena</b>: Pristupnu šifru možete promijeniti isključivo koristeći <a href="#" class="pl-1 text-info">promjena šifre</a> <!-- TODO - Dodati link za promjenu šifre -->
						</div>
					</div>
				</div>

				<div class="row mb-3 bg-light">
					<div class="col-md-12">
						<h4 class="pt-2">Izmijenite svoje osnovne informacije</h4>
					</div>
				</div>

				<form class="p-0" action="" method="post" id="update-profile">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ime">Ime</label> <!-- Old -->
								<?= Form::text('ime', $user['ime'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'ime', 'aria-describedby' => 'imeHelp', 'required' => 'required']) ?>
								<small id="imeHelp" class="form-text text-muted">Vaše ime</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="prezime">Prezime</label> <!-- Old -->
								<?= Form::text('prezime', $user['prezime'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'prezime', 'aria-describedby' => 'prezimeHelp', 'required' => 'required']) ?>
								<small id="prezimeHelp" class="form-text text-muted">Vaše prezime</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="brindexa"> Broj indexa </label> <!-- Old -->
								<?= Form::number('brindexa', $user['brindexa'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'brindexa', 'aria-describedby' => 'brindexaHelp', 'readonly']) ?>
								<small id="brindexaHelp" class="form-text text-muted"> Ovaj podatak može uređivati samo administrator i/ili studentska služba </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="jmbg">JMBG</label> <!-- Old -->
								<?= Form::number('jmbg', $user['jmbg'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'jmbg', 'aria-describedby' => 'jmbgHelp', 'required' => 'required']) ?>
								<small id="jmbgHelp" class="form-text text-muted"> Vaš jedinstveni matični broj </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="spol">Spol</label> <!-- Old -->
								<?= Form::select('spol', ['M' => 'Muški', 'Z' => 'Ženski'], $user['prezime'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'spol', 'aria-describedby' => 'spolHelp', 'required' => 'required']) ?>
								<small id="spolHelp" class="form-text text-muted">Vaš spol</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="mjesto_rodjenja">Mjesto rođenja</label> <!-- Old -->
								<?= Form::select('mjesto_rodjenja', [], '', ['class' => 'form-control form-control-sm select-2-ajax', 'call_f' => 's2-place', 'id' => 'mjesto_rodjenja', 'aria-describedby' => 'mjesto_rodjenjaHelp', 'required' => 'required']) ?>
								<small id="mjesto_rodjenjaHelp" class="form-text text-muted">Vaše mjesto rođenja - Ukoliko je van BiH odaberite "Van BiH"</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="opcina_rodjenja">Općina rođenja</label> <!-- Old -->
								<?= Form::select('opcina_rodjenja', [], '', ['class' => 'form-control form-control-sm select-2-ajax', 'call_f' => 's2-munic', 'id' => 'opcina_rodjenja', 'aria-describedby' => 'opcina_rodjenjaHelp', 'required' => 'required']) ?>
								<small id="opcina_rodjenjaHelp" class="form-text text-muted">Općina rođenja - Ukoliko je van BiH odaberite "Van BiH"</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="drzava_rodjenja">Država rođenja</label> <!-- Old -->
								<?= Form::select('drzava_rodjenja', $drzava, '1', ['class' => 'form-control form-control-sm select-2', 'id' => 'drzava_rodjenja', 'aria-describedby' => 'drzava_rodjenjaHelp', 'required' => 'required']) ?>
								<small id="drzava_rodjenjaHelp" class="form-text text-muted">Odaberite državu rođenja</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="datum_rodjenja">Datum rođenja</label> <!-- Old -->
								<?= Form::text('datum_rodjenja', date("d.m.Y", strtotime($user['datum_rodjenja'])) ?? '', ['class' => 'form-control form-control-sm datepicker', 'id' => 'datum_rodjenja', 'aria-describedby' => 'datum_rodjenjaHelp', 'required' => 'required']) ?>
								<small id="datum_rodjenjaHelp" class="form-text text-muted">Datum rođenja (DD.MM.YYYY)</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="drzavljanstvo">Državljanstvo</label> <!-- Old -->
								<?= Form::select('drzavljanstvo', $drzava, '1', ['class' => 'form-control form-control-sm select-2', 'id' => 'drzavljanstvo', 'aria-describedby' => 'drzavljanstvoHelp', 'required' => 'required']) ?>
								<small id="drzavljanstvoHelp" class="form-text text-muted">Odaberite državu čiji ste državljanin</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="nacionalnost">Nacionalna pripadnost</label> <!-- Old -->
								<?= Form::select('nacionalnost', $nacionalnost, $user['nacionalnost'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'nacionalnost', 'aria-describedby' => 'nacionalnostHelp', 'required' => 'required']) ?>
								<small id="nacionalnostHelp" class="form-text text-muted">Upisuju samo državljani BiH </small>
							</div>
						</div>
					</div>

					<hr>

					<div class="row pt-2">
						<div class="col-md-6">
							<div class="form-group">
								<label for="drzava_preb">Država prebivališta</label>  <!-- New -->
								<?= Form::select('drzava_preb', $drzava, $user_a['drzava_preb'] ?? '1', ['class' => 'form-control form-control-sm select-2', 'id' => 'drzava_preb', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="kanton_preb">Kanton prebivalšta</label>  <!-- New -->
								<?= Form::select('kanton_preb', $kanton, $user_a['kanton_preb'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'kanton_preb']) ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="opcina_preb">Općina prebivališta</label>  <!-- New -->
								<?= Form::select('opcina_preb', [($user_a['opcina_preb'] ?? '') => ($user_a['opcina_preb'] ?? '')], $user_a['opcina_preb'] ?? '', ['class' => 'form-control form-control-sm select-2-ajax', 'call_f' => 's2-munic', 'id' => 'opcina_preb']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="adresa_preb">Adresa prebivališta</label>  <!-- New -->
								<?= Form::text('adresa_preb', $user_a['adresa_preb'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'adresa_preb']) ?>
							</div>
						</div>
						
						<div class="col-md-12">
							<small id="drzava_prebHelp" class="form-text text-muted">Prebivalište je mjesto u kojem je studentu stalno mjesto stanovanja. Studenti - državljani BiH upisuju za mjesto svog prebivališta mjesto prebivališta svojih roditelja - izdržavatelja. Studenti - strani državljani, izuzev onih koji stalno žive u BiH, upisuju naziv svoje države</small>
						</div>
					</div>
					
					<hr>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="imeoca">Ime oca</label> <!-- Old -->
								<?= Form::text('imeoca', $user['imeoca'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'imeoca', 'aria-describedby' => 'imeocaHelp', 'required' => 'required']) ?>
								<small id="imeocaHelp" class="form-text text-muted"> Unesite ime Vašeg oca </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="prezimeoca">Prezime oca</label> <!-- Old -->
								<?= Form::text('prezimeoca', $user['prezimeoca'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'prezimeoca', 'aria-describedby' => 'prezimeocaHelp', 'required' => 'required']) ?>
								<small id="prezimeocaHelp" class="form-text text-muted"> Unesite prezime Vašeg oca </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="imemajke">Ime majke</label> <!-- Old -->
								<?= Form::text('imemajke', $user['imemajke'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'imemajke', 'aria-describedby' => 'imemajkeHelp', 'required' => 'required']) ?>
								<small id="imemajkeHelp" class="form-text text-muted"> Unesite ime Vaše majke </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="prezimemajke">Prezime majke</label> <!-- Old -->
								<?= Form::text('prezimemajke', $user['prezimemajke'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'prezimemajke', 'aria-describedby' => 'prezimemajkeHelp', 'required' => 'required']) ?>
								<small id="prezimemajkeHelp" class="form-text text-muted"> Unesite prezime Vaše majke </small>
							</div>
						</div>
					</div>
					
					<hr>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="adresa">Adresa boravišta</label> <!-- Old -->
								<?= Form::text('adresa', $user['adresa'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'adresa', 'aria-describedby' => 'adresaHelp', 'required' => 'required']) ?>
								<small id="adresaHelp" class="form-text text-muted"> Boravište je mjesto stanovanja gdje student boravi za vrijeme studija. </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="adresa_mjesto">Mjesto boravišta</label> <!-- Old -->
								<?= Form::select('adresa_mjesto', [], $user['adresa_mjesto'] ?? '', ['class' => 'form-control form-control-sm select-2-ajax', 'call_f' => 's2-place', 'id' => 'adresa_mjesto', 'aria-describedby' => 'adresa_mjestoHelp', 'required' => 'required']) ?>
								<small id="adresa_mjestoHelp" class="form-text text-muted"> Boravište je mjesto stanovanja gdje student boravi za vrijeme studija. </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="telefon">Telefon</label> <!-- Old -->
								<?= Form::text('telefon', $user['telefon'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'telefon', 'aria-describedby' => 'telefonHelp', 'required' => 'required']) ?>
								<small id="telefonHelp" class="form-text text-muted"> 00387 6X XXX XXX </small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="email2">Email</label> <!-- Old -->
								<?= Form::email('email2', $email['adresa'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'email2', 'aria-describedby' => 'email2Help', 'required' => 'required']) ?>
								<small id="email2Help" class="form-text text-muted">Vaše privatni email</small>
							</div>
						</div>
					</div>

					<hr>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="naziv">Naziv prethodno završenog obrazovanja</label> <!-- Old -->
								<?= Form::text('naziv', '', ['class' => 'form-control form-control-sm', 'id' => 'naziv', 'aria-describedby' => 'prethodnoObrazHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="godina">Godina </label> <!-- New -->
								<?= Form::number('godina','', ['class' => 'form-control form-control-sm', 'id' => 'godina', 'aria-describedby' => 'prethodnoObrazHelp', 'required' => 'required', 'min' => '1960', 'max' => date('Y')]) ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="opcina">Općina u kojoj ste završili</label> <!-- Old -->
								<?= Form::text('opcina', '', ['class' => 'form-control form-control-sm', 'id' => 'opcina', 'aria-describedby' => 'prethodnoObrazHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="tipskole"> Tip škole </label> <!-- Old -->
								<?= Form::select('tipskole', ['GIMNAZIJA' => 'GIMNAZIJA', 'ELEKTROTEHNICKA' => 'ELEKTROTEHNICKA', 'TEHNICKA' => 'TEHNICKA', 'STRUCNA' => 'STRUCNA', 'MSS' => 'MSS'], 'GIMNAZIJA', ['class' => 'form-control form-control-sm', 'id' => 'tipskole', 'aria-describedby' => 'prethodnoObrazHelp', 'required' => 'required']) ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="domaca">Da li je škola u BiH</label> <!-- Old -->
								<?= Form::select('domaca', ['1' => 'Da', '2' => 'Ne'], '1', ['class' => 'form-control form-control-sm', 'id' => 'domaca', 'aria-describedby' => 'prethodnoObrazHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="prethodnoObrazHelp" class="form-text text-muted">
								Naziv prethodno završenog obrazovanja -Upisuje se naziv prethodno završenog obrazovanja /srednje, više, visoke škole/, fakulteta, akademije koju ste završili prije upisa na ovu visokoškolsku ustanovu, tj. prije upisa na određe ni studijski program. Ako ste prije studirali na nekoj visokoškolskoj ustanovi a niste diplomirali, upisujete naziv srednje škole koju ste prethodno završili.
								<br>
								Godina prethodno završenog obrazovanja - Upisujete godinu završnog ispita - mature ili godine kada ste dobili završno svjedoanstvo za srednju školu, odnosno godinu diplomiranja - završetka studija na određenom više ili visokoškolskom studiju.
							</small>
						</div>
					</div>

					<hr>
					
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="izvori_finan"> Izvori finansiranja studenta za vrijeme studija </label> <!-- New -->
								<?= Form::select('izvori_finan', $izvoriFinansiranja, $user_a['izvori_finan'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'izvori_finan', 'aria-describedby' => 'izvori_finanHelp', 'required' => 'required']) ?>
								<small id="izvori_finanHelp" class="form-text text-muted"> Ukoliko je iz više izvora, odaberite pretežiti izvor </small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="status_a_r">Status u aktivnosti roditelja - izdržavatelja </label> <!-- New -->
								<?= Form::select('status_a_r', $statusAktivnosti, $user_a['status_a_r'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'status_a_r', 'aria-describedby' => 'statusAktivnostHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="status_a_s">Status u aktivnosti studenta</label> <!-- New -->
								<?= Form::select('status_a_s', $statusAktivnosti, $user_a['status_a_s'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'status_a_s', 'aria-describedby' => 'statusAktivnostHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="statusAktivnostHelp" class="form-text text-muted">
								<b>Zaposlen</b> , ako roditelj - izdržavatelj ili student imaju neki posao kojim obezbjeđuju sredstva za život.
								<b>Nezaposlen</b> , ako roditelj - izdržavatelj ili student nemaju nikakav posao kojim obezbjeđuju sredstva za život, ali traže posao i spremni su da ponu da ga obavljaju ukoliko bi im posao bio ponuđen.
								<b>Neaktivan</b> , ako je roditelj - izdržavatelj ili student nesposoban za rad, domaćica, penzioner, student.
							</small>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="zanimanje_r">Zanimanje roditelja - izdržavatelja </label> <!-- New -->
								<?= Form::text('zanimanje_r', $user_a['zanimanje_r'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'zanimanje_r', 'aria-describedby' => 'zanimanjeHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="zanimanje_s">Status u aktivnosti studenta</label> <!-- New -->
								<?= Form::text('zanimanje_s', $user_a['zanimanje_s'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'zanimanje_s', 'aria-describedby' => 'zanimanjeHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="zanimanjeHelp" class="form-text text-muted">
								<b>Zanimanje</b> - vrsta posla koju osoba obavlja u preduzeu, prodavnici, tvornici, itd. Potrebno je upisati što precizniji opis zanimanja, odnosno vrste posla koji osoba obavlja u preduzeu, prodavnici, na poljoprivrednom gazdinstvu, i slino. Zanimanje ne mora biti u vezi sa stepenom obrazovanja ili specijalizacijom nego se veže za konkretan posao koji obavlja osoba, npr. pravnik koji pruža taksi usluge po zanimanju je taksista, a ne pravnik. U svrhu omoguavanja šifriranja odgovori moraju biti jasni i kompletni. Odgovor treba da je što detaljniji, npr.: individualni poljoprivrednik, rudar, blagajnik, profesor, elektrotehniar, laborant, slovoslaga i dr.
							</small>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="status_z_r">Status u zaposlenosti roditelja - izdržavatelja </label> <!-- New -->
								<?= Form::select('status_z_r', $statusZaposlenosti, $user_a['status_z_r'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'status_z_r', 'aria-describedby' => 'statusZapHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="status_z_s">Status u zaposlenosti studenta</label> <!-- New -->
								<?= Form::select('status_z_s', $statusZaposlenosti, $user_a['status_z_s'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'status_z_s', 'aria-describedby' => 'statusZapHelp', 'required' => 'required']) ?>
							</div>
						</div>
						<div class="col-md-12">
							<small id="statusZapHelp" class="form-text text-muted">
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
