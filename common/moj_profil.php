<?php

require_once 'common/classes/Form.php';

function common_moj_profil(){
    global $userid, $person;

    $emails   = db_query("select * from email where osoba = $userid")->fetch_all(); // Ovo će se brisati poslije
    $email_c  = 1;
    if(!count($emails)) $emails[] = ['x', '', '']; // Ukoliko nema ni jednog email-a unesenog, podesi defaultnu vrijednost

    // Keywords
    $nacionalnost = db_query("select id,naziv from nacionalnost order by naziv")->fetch_all();       // TODO - API
    $drzava       = db_query("select id,naziv from drzava order by naziv")->fetch_all();             // TODO - API
    $kanton       = db_query("select id,naziv from kanton order by naziv")->fetch_all();             // TODO - API
    $mjesto       = db_query("select id,naziv from mjesto order by naziv")->fetch_all();             // TODO - API
    $opcina       = db_query("select id,naziv from opcina order by naziv")->fetch_all();             // TODO - API


    // This one can stay, since it would not change value !
    $izvoriFinansiranja = [ '1' => 'Roditelji', '2' => 'Primate plaću iz radnog odnosa', '3' => 'Primate stipendiju', '4' => 'Kredit', '5' => 'Ostalo' ];
    $statusAktivnosti   = [ '1' => 'Zaposlen', '2' => 'Nezaposlen', '3' => 'Neaktivan'];
    $statusZaposlenosti = [ '1' => 'Poslodavac / Samozaposlenik', '2' => 'Zaposlenik', '3' => 'Pomažući član porodice'];

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
                            <b class="pl-2">Napomena</b>: Pristupnu šifru možete promijeniti isključivo koristeći <a href="promjena-sifre.php" class="pl-1 text-info">promjena šifre</a>. <!-- TODO - Dodati link za promjenu šifre -->
                            <span class="color-logo download-sv-20 ml-2">Preuzmite ŠV-20 obrazac</span>
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
                                <?= Form::text('ime', $person['name'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'ime', 'aria-describedby' => 'imeHelp', 'required' => 'required']) ?>
                                <small id="imeHelp" class="form-text text-muted">Vaše ime</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prezime">Prezime</label> <!-- Old -->
                                <?= Form::text('prezime', $person['surname'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'prezime', 'aria-describedby' => 'prezimeHelp', 'required' => 'required']) ?>
                                <small id="prezimeHelp" class="form-text text-muted">Vaše prezime</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="studentIdNr"> Broj indexa </label> <!-- Old -->
                                <?= Form::text('studentIdNr', $person['studentIdNr'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'studentIdNr', 'aria-describedby' => 'studentIdNrHelp', 'readonly']) ?>
                                <small id="studentIdNrHelp" class="form-text text-muted"> Ovaj podatak može uređivati samo administrator i/ili studentska služba </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jmbg">JMBG</label> <!-- Old -->
                                <?= Form::number('jmbg', $person['ExtendedPerson']['jmbg'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'jmbg', 'aria-describedby' => 'jmbgHelp', 'required' => 'required']) ?>
                                <small id="jmbgHelp" class="form-text text-muted"> Vaš jedinstveni matični broj </small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sex">Spol</label> <!-- Old -->
                                <?= Form::select('sex', ['M' => 'Muški', 'Z' => 'Ženski'], $person['ExtendedPerson']['sex'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'sex', 'aria-describedby' => 'sexHelp', 'required' => 'required']) ?>
                                <small id="sexHelp" class="form-text text-muted">Vaš spol</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="placeOfBirth">Mjesto rođenja</label> <!-- Old -->
                                <?= Form::select('placeOfBirth', $mjesto, $person['ExtendedPerson']['placeOfBirth']['id'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'placeOfBirth', 'aria-describedby' => 'placeOfBirthHelp']) ?>
                                <small id="placeOfBirthHelp" class="form-text text-muted">Vaše mjesto rođenja - Ukoliko je van BiH odaberite "Van BiH"</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Municipality">Općina rođenja</label> <!-- TODO !? -->
                                <?= Form::select('Municipality', $opcina, $person['ExtendedPerson']['placeOfBirth']['Municipality']['id'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'Municipality', 'aria-describedby' => 'MunicipalityHelp']) ?>
                                <small id="MunicipalityHelp" class="form-text text-muted">Općina rođenja - Ukoliko je van BiH odaberite "Van BiH"</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Country">Država rođenja</label> <!-- TODO -->
                                <?= Form::select('Country', $drzava, $person['ExtendedPerson']['placeOfBirth']['Country']['id'] ?? '', ['class' => 'form-control form-control-sm select-2', 'id' => 'Country', 'aria-describedby' => 'CountryHelp', 'required' => 'required']) ?>
                                <small id="CountryHelp" class="form-text text-muted">Odaberite državu rođenja</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateOfBirth">Datum rođenja</label> <!-- Old -->
                                <?= Form::text('dateOfBirth', date("d.m.Y", strtotime($person['ExtendedPerson']['dateOfBirth'])) ?? '', ['class' => 'form-control form-control-sm datepicker', 'id' => 'dateOfBirth', 'aria-describedby' => 'dateOfBirthHelp', 'required' => 'required']) ?>
                                <small id="dateOfBirthHelp" class="form-text text-muted">Datum rođenja (DD.MM.YYYY)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drzavljanstvo">Državljanstvo</label> <!-- TODO -->
                                <?= Form::select('drzavljanstvo', $drzava, '1', ['class' => 'form-control form-control-sm select-2', 'id' => 'drzavljanstvo', 'aria-describedby' => 'drzavljanstvoHelp', 'required' => 'required']) ?>
                                <small id="drzavljanstvoHelp" class="form-text text-muted">Odaberite državu čiji ste državljanin</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nationality">Nacionalna pripadnost</label> <!-- Old -->
                                <?= Form::select('nationality', $nacionalnost, $person['ExtendedPerson']['nationality'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'nationality', 'aria-describedby' => 'nationalityHelp', 'required' => 'required']) ?>
                                <small id="nationalityHelp" class="form-text text-muted">Upisuju samo državljani BiH </small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row pt-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drzava_preb">Država prebivališta</label>  <!-- TODO -->
                                <?= Form::select('drzava_preb', $drzava, $person['drzava_preb'] ?? '1', ['class' => 'form-control form-control-sm select-2', 'id' => 'drzava_preb', 'required' => 'required']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kanton_preb">Kanton prebivalšta</label>  <!-- TODO -->
                                <?= Form::select('kanton_preb', $kanton, $person['kanton_preb'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'kanton_preb']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="opcina_preb">Općina prebivališta</label>  <!-- TODO -->
                                <?= Form::select('opcina_preb', [($person['opcina_preb'] ?? '') => ($person['opcina_preb'] ?? '')], $person['opcina_preb'] ?? '', ['class' => 'form-control form-control-sm select-2-ajax', 'call_f' => 's2-munic', 'id' => 'opcina_preb']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="residenceAddress">Adresa prebivališta</label>  <!-- TODO -->
                                <?= Form::text('residenceAddress', $person['ExtendedPerson']['residenceAddress'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'residenceAddress']) ?>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="residenceAddress">Adresa boravišta</label> <!-- Old -->
                                <?= Form::text('residenceAddress', $person['ExtendedPerson']['residenceAddress'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'residenceAddress', 'aria-describedby' => 'residenceAddressHelp', 'required' => 'required']) ?>
                                <small id="residenceAddressHelp" class="form-text text-muted"> Boravište je mjesto stanovanja gdje student boravi za vrijeme studija. </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="residencePlace">Mjesto boravišta</label> <!-- Old -->
                                <?= Form::select('residencePlace', $mjesto, $person['ExtendedPerson']['residencePlace'] ?? '', ['class' => 'form-control form-control-sm select-2-ajax', 'call_f' => 's2-place', 'id' => 'residencePlace', 'aria-describedby' => 'residencePlaceHelp']) ?>
                                <small id="residencePlaceHelp" class="form-text text-muted"> Boravište je mjesto stanovanja gdje student boravi za vrijeme studija. </small>
                            </div>
                        </div>
                    </div>
                    <div class="row email-wrapper">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Telefon</label> <!-- Old -->
                                <?= Form::text('phone', $person['ExtendedPerson']['phone'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'phone', 'aria-describedby' => 'phoneHelp', 'required' => 'required']) ?>
                                <small id="phoneHelp" class="form-text text-muted"> 00387 6X XXX XXX </small>
                            </div>
                        </div>
                        <?php
                        foreach ($emails as $email){
                            ?>
                            <div class="<?= ($email_c % 2 == 0) ? 'col-md-12' : 'col-md-6' ?>">
                                <div class="form-group">
                                    <label for="email">Email</label> <!-- Old -->
                                    <?= Form::email('email[]', $email[2] ?? '', ['class' => 'form-control form-control-sm sm-emails', 'id' => 'email', 'aria-describedby' => 'emailHelp', 'required' => 'required', 'no' => $email_c++]) ?>
                                    <?= Form::hidden('email_id[]', $email[0], ['class' => 'form-controll sm-emails-id']) ?>
                                    <small id="emailHelp" class="form-text text-muted">Vaše privatni email ( ukoliko imate još email adresa, možete ih dodati <span class="color-logo append-email"><b>ovdje</b></span> ) - <span class="text-danger remove-email remove-email-db" id="<?= $email[0] ?>"><b>Obrišite ovaj email</b></span></small>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="naziv">Naziv prethodno završenog obrazovanja</label> <!-- Old -->
                                <?= Form::text('naziv', '', ['class' => 'form-control form-control-sm', 'id' => 'naziv', 'aria-describedby' => 'prethodnoObrazHelp']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="godina">Godina </label> <!-- New -->
                                <?= Form::number('godina','', ['class' => 'form-control form-control-sm', 'id' => 'godina', 'aria-describedby' => 'prethodnoObrazHelp', 'min' => '1960', 'max' => date('Y')]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="opcina">Općina u kojoj ste završili</label> <!-- Old -->
                                <?= Form::text('opcina', '', ['class' => 'form-control form-control-sm', 'id' => 'opcina', 'aria-describedby' => 'prethodnoObrazHelp']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipskole"> Tip škole </label> <!-- Old -->
                                <?= Form::select('tipskole', ['GIMNAZIJA' => 'GIMNAZIJA', 'ELEKTROTEHNICKA' => 'ELEKTROTEHNICKA', 'TEHNICKA' => 'TEHNICKA', 'STRUCNA' => 'STRUCNA', 'MSS' => 'MSS'], 'GIMNAZIJA', ['class' => 'form-control form-control-sm', 'id' => 'tipskole', 'aria-describedby' => 'prethodnoObrazHelp']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="domaca">Da li je škola u BiH</label> <!-- Old -->
                                <?= Form::select('domaca', ['1' => 'Da', '2' => 'Ne'], '1', ['class' => 'form-control form-control-sm', 'id' => 'domaca', 'aria-describedby' => 'prethodnoObrazHelp']) ?>
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
                                <label for="sourceOfFunding"> Izvori finansiranja studenta za vrijeme studija </label> <!-- New -->
                                <?= Form::select('sourceOfFunding', $izvoriFinansiranja, $person['ExtendedPerson']['sourceOfFunding'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'sourceOfFunding', 'aria-describedby' => 'sourceOfFundingHelp', 'required' => 'required']) ?>
                                <small id="sourceOfFundingHelp" class="form-text text-muted"> Ukoliko je iz više izvora, odaberite pretežiti izvor </small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activityStatusParent">Status u aktivnosti roditelja - izdržavatelja </label> <!-- New -->
                                <?= Form::select('activityStatusParent', $statusAktivnosti, $person['ExtendedPerson']['activityStatusParent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'activityStatusParent', 'aria-describedby' => 'activityStatusHelp', 'required' => 'required']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activityStatusStudent">Status u aktivnosti studenta</label> <!-- New -->
                                <?= Form::select('activityStatusStudent', $statusAktivnosti, $person['ExtendedPerson']['activityStatusStudent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'activityStatusStudent', 'aria-describedby' => 'activityStatusHelp', 'required' => 'required']) ?>
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
                                <?= Form::text('occupationParent', $person['ExtendedPerson']['occupationParent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'occupationParent', 'aria-describedby' => 'occupationHelp', 'required' => 'required']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="occupationStudent">Status u aktivnosti studenta</label> <!-- New -->
                                <?= Form::text('occupationStudent', $person['ExtendedPerson']['occupationStudent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'occupationStudent', 'aria-describedby' => 'occupationHelp', 'required' => 'required']) ?>
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
                                <?= Form::select('employmentStatusParent', $statusZaposlenosti, $person['ExtendedPerson']['employmentStatusParent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'employmentStatusParent', 'aria-describedby' => 'employmentStatusHelp', 'required' => 'required']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employmentStatusStudent">Status u zaposlenosti studenta</label> <!-- New -->
                                <?= Form::select('employmentStatusStudent', $statusZaposlenosti, $person['ExtendedPerson']['employmentStatusStudent'] ?? '', ['class' => 'form-control form-control-sm', 'id' => 'employmentStatusStudent', 'aria-describedby' => 'employmentStatusHelp', 'required' => 'required']) ?>
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
