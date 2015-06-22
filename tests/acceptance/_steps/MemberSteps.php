<?php

//use Codeception\Util\Locator;

//use Codeception\Module\FakerHelper;

namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester {

    public function logout() {
        $I = $this;
        $I->canSeeLink("Odjava");
        $I->click("Odjava");
        $I->canSeeInCurrentUrl("?sta=logout");
    }

    public function login($username, $password) {
        $I = $this;
        $I->wantTo('login');
        $I->amOnPage(\loginPage::$URL);
        $I->canSee(\loginPage::$text);
        $I->fillField(\loginPage::$username, $username);
        $I->fillField(\loginPage::$pass, $password);
        $I->click(\loginPage::$button);
//        $I->registrujLogin($username,$password);
        #$I->see(\loginPage::$homeTextAdmin);
    }

    public function adminIskljuciPopUp() {
        $I = $this;
        $I->wantTo("pop up ");
        $I->loginKaoAdmin();
        $I->amOnPage('/');
        $I->click('Studentska služba');
        $I->click('Osobe');
        $I->click('Prikaži sve osobe');
        $I->click("//td/table/tbody/tr/td/table/tbody/tr/td[2]/a");
        $I->click('input[type="Submit"]');
        $I->click("Zamger opcije");
        $I->uncheckOption('input[name="savjet_dana"]');
        $I->click('input[type="submit"]');
        $I->logout();
    }

    public function loginKaoAdmin() {
        $I = $this;
        $I->wantTo('login kao administrator');
        $I->login('admin', 'admin');
        $I->see(\loginPage::$homeTextAdmin);
    }

    public function adminDodajOsobu($ime, $prezime) {
        $I = $this;
        $I->wantTo("Dodati novu osobu");
        $I->amOnPage("/");
        $I->seeLink("Studentska služba");
        $I->click("Studentska služba");
        $I->seeLink("Osobe");
        $I->click("Osobe");
        $I->canSee("Studenti i nastavnici");
        $I->seeElement("input[name=ime]");
        $I->fillField("input[name=ime]", $ime);
        $I->seeElement("input[name=prezime]");
        $I->fillField("input[name=prezime]", $prezime);
        $I->click("Dodaj");
        $I->see("Novi korisnik je dodan.");
        $I->canSeeLink($ime . " " . $prezime);
    }

    private function adminDodajOsobuTipa($ime, $prezime, $tip) {
        $I = $this;
        $I->adminDodajOsobu($ime, $prezime);
        $I->canSeeLink($ime . " " . $prezime);
        $I->click($ime . " " . $prezime);
        $I->canSee($ime . " " . $prezime);
        $I->seeElement("input[name=" . $tip . "]");
        $I->checkOption("input[name=" . $tip . "]");
//        $I->waitForElement("input[name=" . $tip . "]", 30);
//        $I->canSeeCheckboxIsChecked("input[name=" . $tip . "]");
        $I->click("Promijeni");
        $I->canSee("Data privilegija ");
    }

    public function adminDodajStudenta($ime, $prezime,$username = null,$password = null) {
        $I = $this;
        if(is_null($username)){
            $username = $ime;
        }
        if(is_null($password)){
            $password = $ime;
        }
        $I->adminDodajOsobuTipa($ime, $prezime, "student");
        $I->fillField('[name=login]', $username);
        $I->fillField('[name=password]', $password);
        $I->checkOption('[name=aktivan]');
        $I->click("//input[@value=' Dodaj novi ']");
    }

    public function adminDodajNastavnika($ime, $prezime,$username = null,$password = null) {
        $I = $this;
        if(is_null($username)){
            $username = $ime;
        }
        if(is_null($password)){
            $password = $ime;
        }
        $I->adminDodajOsobuTipa($ime, $prezime, "nastavnik");
        $I->fillField('[name=login]', $username);
        $I->fillField('[name=password]', $password);
        $I->checkOption('[name=aktivan]');
        $I->click("//input[@value=' Dodaj novi ']");
    }

    public function adminDodajPrijemniOsoba($ime, $prezime) {
        $I = $this;
        $I->adminDodajOsobuTipa($ime, $prezime, "prijemni");
        $I->fillField('[name=login]', $ime);
        $I->fillField('[name=password]', $ime);
        $I->checkOption('[name=aktivan]');
        $I->click("//input[@value=' Dodaj novi ']");
    }

    public function adminDodajStudentskaOsoba($ime, $prezime) {
        $I = $this;
        $I->adminDodajOsobuTipa($ime, $prezime, "studentska");
        $I->fillField('[name=login]', $ime);
        $I->fillField('[name=password]', $ime);
        $I->checkOption('[name=aktivan]');
        $I->click("//input[@value=' Dodaj novi ']");
    }

    public function adminDodajSiteadminOsoba($ime, $prezime) {
        $I = $this;
        $I->adminDodajOsobuTipa($ime, $prezime, "siteadmin");
        $I->fillField('[name=login]', $ime);
        $I->fillField('[name=password]', $ime);
        $I->checkOption('[name=aktivan]');
        $I->click("//input[@value=' Dodaj novi ']");
    }

    public function adminDodajPredmet($predmet, $sifra, $ects, $satiPredavanja, $satiVjezbi, $satiTutorijala) {
        $I = $this;
        $I->am('Administrator');
        $I->wantTo('Dodati novi predmet kao administrator');
        $I->amOnPage('/');
//      
        $I->canSee(\adminHomePage::$studentskaSluzbaLink);
        $I->click(\adminHomePage::$studentskaSluzbaLink);
        $I->canSee(\studentskaSluzbaPage::$navPredmetiLink);
        $I->click(\studentskaSluzbaPage::$navPredmetiLink);

        $I->canSee("Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.");
        $I->canSeeElement('input[name=naziv]');
        $I->fillField('input[name=naziv]', $predmet);
//        $I->canSee("input[value=' Dodaj ']");
        $I->click("input[value=' Dodaj ']");

        $I->canSeeLink('Editovanje predmeta "' . $predmet . '"');
        $I->click('Editovanje predmeta "' . $predmet . '"');
//        $linkTrenutni = $I->grab
//        $I->canSee('Izmijeni');
        $I->click("input[type=\"submit\"]");

        $I->canSee('Izmjena podataka o predmetu');
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sifra'], $sifra);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Ects'], $ects);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sati predavanja'], $satiPredavanja);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sati vjezbi'], $satiVjezbi);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sati tutorijala'], $satiTutorijala);
        $I->click("input[type=\"submit\"]");
        $I->canSee("Podaci o predmetu izmijenjeni");
        $I->click("Nazad");
    }

    private function fillNovaPonudaKursa($studij, $semestar, $obavezan = true) {
        $I = $this;
        $I->canSee('Nova ponuda kursa za predmet');
        $I->seeElement('select[name=_lv_column_studij]');
        $I->selectOption('select[name=_lv_column_studij]', $studij);
//        $I->fillField('input[name=semestar]', $semestar);
        $I->seeElement('input[name=obavezan]');

        if ($obavezan) {
            $I->checkOption('input[name=obavezan]');
        }
        $I->click('input[type="submit"]');
        $I->canSee("Ponuda kursa uspješno kreirana");
    }

//    private function fillNovaPonudaKursaArray(array $var) {
//        $I = $this;
//        $I->canSee('Dodaj ponudu kursa');
//        $I->click('Dodaj ponudu kursa');
//        foreach ($var as $value) {
//            $I->fillNovaPonudaKursa($value['studij'], $value['semestar'], $value['obavezan']);
//        }
//    }

    public function adminDodajPredmetKursSvima($predmet, $sifra, $ects, $satiPredavanja, $satiVjezbi, $satiTutorijala, $semestar, $obavezan = true) {
        $I = $this;
        $I->adminDodajPredmet($predmet, $sifra, $ects, $satiPredavanja, $satiVjezbi, $satiTutorijala);
        $var = array(
            array(
                'studij' => \predmetPage::$ponudaKursaStudij['RIBsc'],
                'semestar' => $semestar,
                'obavezan' => $obavezan
            ),
            array(
                'studij' => \predmetPage::$ponudaKursaStudij['EEBsc'],
                'semestar' => $semestar,
                'obavezan' => $obavezan
            ),
            array(
                'studij' => \predmetPage::$ponudaKursaStudij['TKBsc'],
                'semestar' => $semestar,
                'obavezan' => $obavezan
            ),
            array(
                'studij' => \predmetPage::$ponudaKursaStudij['AiEBsc'],
                'semestar' => $semestar,
                'obavezan' => $obavezan
            )
        );
        $I->canSee('Dodaj ponudu kursa');
        $I->click('Dodaj ponudu kursa');
        foreach ($var as $value) {
            $I->fillNovaPonudaKursa($value['studij'], $value['semestar']); //, $value['obavezan']
        }
//        $I->fillNovaPonudaKursaArray($var);
//        $I->click('Nazad');
    }

    public function adminDodajPredmetKurs($predmet, $sifra, $ects, $satiPredavanja, $satiVjezbi, $satiTutorijala, $studij, $semestar, $obavezan=true) {
        $I = $this;
        $I->adminDodajPredmet($predmet, $sifra, $ects, $satiPredavanja, $satiVjezbi, $satiTutorijala);
        $I->canSee('Dodaj ponudu kursa');
        $I->click('Dodaj ponudu kursa');
        $I->fillNovaPonudaKursa($I, $studij, $semestar, $obavezan);
        $I->click("Nazad");
    }

    public function adminDodajPrvuGodinuBsc() {
        $I = $this;
        $I->adminDodajPredmetKursSvima('Inžinjerska matematika 1', 'PG01', '6.5', 49, 0, 26, 1, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Inžinjerska matematika 2', 'PG06', '7.5', 52, 0, 28, 2, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Inženjerska fizika 1', 'PG03', '5', 39, 0, 21, 1, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Inženjerska fizika 2', 'PG08', '5', 39, 0, 21, 2, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Osnove elektrotehnike', 'PG02', '7.5', 48, 4, 28, 1, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Električni krugovi 1', 'PG07', '6.5', 45, 10, 20, 2, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Osnove računarstva', 'PG05', '6', 44, 26, 0, 1, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Tehnike programiranja', 'PG09', '6', 44, 26, 0, 2, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Linearna algebra i geometrija', 'PG04', '5', 39, 0, 21, 1, true);
        $I->amOnPage('/');

        $I->adminDodajPredmetKursSvima('Elektronički elementi i sklopovi', 'PG10', '5', 39, 0, 21, 2, true);
        $I->amOnPage('/');
    }

    public function adminDodajStavkuNastavnogPlana($predmet,$obavezan=true,$semestar=1,$smjer='TKBsc') {
        $I = $this;
        $I->selectOption(\stavkaNastavnogPlanaPage::$studij, \stavkaNastavnogPlanaPage::$studijOptions[$smjer]);
//        $I->selectOption(\stavkaNastavnogPlanaPage::$studij, stavkaNastavnogPlanaPage::$studijOptions[$smjer]);
        $I->fillField(\stavkaNastavnogPlanaPage::$semestar, $semestar);
        $I->selectOption(\stavkaNastavnogPlanaPage::$predmet, $predmet);
        if($obavezan){
            $I->checkOption(\stavkaNastavnogPlanaPage::$jeObavezan);            
        }
        $I->click(\stavkaNastavnogPlanaPage::$potvrdi);
        $I->canSee(\stavkaNastavnogPlanaPage::$uspjesnoText);
        $I->click(\stavkaNastavnogPlanaPage::$uspjesnoUrlNazad);
        $I->fixturePredmetUKurs($predmet, $obavezan, $semestar);//zakucano na RI
    }

    public function adminDodajStvakeNastavnogPlanaPrvaGodina() {
        $I = $this;
        $I->adminDodajStavkuNastavnogPlana('1.Inžinjerska matematika 1', true, 1, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('2.Inžinjerska matematika 2', true, 2, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('3.Inženjerska fizika 1', true, 1, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('4.Inženjerska fizika 2', true, 2, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('5.Osnove elektrotehnike', true, 1, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('6.Električni krugovi 1', true, 2, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('7.Osnove računarstva', true, 1, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('8.Tehnike programiranja', true, 2, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('9.Linearna algebra i geometrija', true, 1, 'RIBsc');
        $I->adminDodajStavkuNastavnogPlana('10.Elektronički elementi i sklopovi', true, 2, 'RIBsc');
    }
    
    public function fixturePredmetiZaGodinuJedanBsc() {
        $I = $this;
        //$I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles@davis.com'));
        
        $I->haveInDatabase('predmet',array('id'=>'1', 'sifra'=>'PG01', 'naziv'=>'Inžinjerska matematika 1', 'institucija'=>'1', 
            'kratki_naziv'=>'IM1', 'tippredmeta'=>'1', 'ects'=>'6.5', 'sati_predavanja'=>'49',
            'sati_vjezbi'=>'0', 'sati_tutorijala'=>'26',));
        $I->haveInDatabase('predmet',array('id'=>'2', 'sifra'=>'PG06', 'naziv'=>'Inžinjerska matematika 2', 'institucija'=>'1', 
            'kratki_naziv'=>'IM2', 'tippredmeta'=>'1', 'ects'=>'7.5', 'sati_predavanja'=>'52',
            'sati_vjezbi'=>'0', 'sati_tutorijala'=>'28',));
        $I->haveInDatabase('predmet',array('id'=>'3', 'sifra'=>'PG03', 'naziv'=>'Inženjerska fizika 1', 'institucija'=>'1', 
            'kratki_naziv'=>'IF1', 'tippredmeta'=>'1', 'ects'=>'5', 'sati_predavanja'=>'39',
            'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'4', 'sifra'=>'PG08', 'naziv'=>'Inženjerska fizika 2', 'institucija'=>'1', 
            'kratki_naziv'=>'IF2', 'tippredmeta'=>'1', 'ects'=>'5', 'sati_predavanja'=>'39',
            'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'5', 'sifra'=>'PG02', 'naziv'=>'Osnove elektrotehnike', 'institucija'=>'1', 
            'kratki_naziv'=>'OE', 'tippredmeta'=>'1', 'ects'=>'7.5', 'sati_predavanja'=>'48',
            'sati_vjezbi'=>'4', 'sati_tutorijala'=>'28',));
        $I->haveInDatabase('predmet',array('id'=>'6', 'sifra'=>'PG07', 'naziv'=>'Električni krugovi 1', 'institucija'=>'1', 
            'kratki_naziv'=>'EK1', 'tippredmeta'=>'1', 'ects'=>'6.5', 'sati_predavanja'=>'45',
            'sati_vjezbi'=>'10', 'sati_tutorijala'=>'20',));
        $I->haveInDatabase('predmet',array('id'=>'7', 'sifra'=>'PG05', 'naziv'=>'Osnove računarstva', 'institucija'=>'1', 
            'kratki_naziv'=>'OR', 'tippredmeta'=>'1', 'ects'=>'6', 'sati_predavanja'=>'44',
            'sati_vjezbi'=>'26', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'8', 'sifra'=>'PG09', 'naziv'=>'Tehnike programiranja', 'institucija'=>'1', 
            'kratki_naziv'=>'TP', 'tippredmeta'=>'1', 'ects'=>'6', 'sati_predavanja'=>'44',
            'sati_vjezbi'=>'26', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'9', 'sifra'=>'PG04', 'naziv'=>'Linearna algebra i geometrija', 'institucija'=>'1', 
            'kratki_naziv'=>'LAG', 'tippredmeta'=>'1', 'ects'=>'5', 'sati_predavanja'=>'39',
            'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'10', 'sifra'=>'PG10', 'naziv'=>'Elektronički elementi i sklopovi', 'institucija'=>'1', 
            'kratki_naziv'=>'EES', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'39',
            'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));

    }

    
    public function fixturePredmetiZaRiBsc(){
        $I = $this;
        $I->haveInDatabase('predmet',array('id'=>'1', 'sifra'=>'PG01', 'naziv'=>'Inžinjerska matematika 1', 'institucija'=>'1', 
        'kratki_naziv'=>'IM1', 'tippredmeta'=>'1', 'ects'=>'6.5', 'sati_predavanja'=>'49',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'26',));
        $I->haveInDatabase('predmet',array('id'=>'2', 'sifra'=>'PG06', 'naziv'=>'Inžinjerska matematika 2', 'institucija'=>'1', 
        'kratki_naziv'=>'IM2', 'tippredmeta'=>'1', 'ects'=>'7.5', 'sati_predavanja'=>'52',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'28',));
        $I->haveInDatabase('predmet',array('id'=>'3', 'sifra'=>'PG03', 'naziv'=>'Inženjerska fizika 1', 'institucija'=>'1', 
        'kratki_naziv'=>'IF1', 'tippredmeta'=>'1', 'ects'=>'5', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'4', 'sifra'=>'PG08', 'naziv'=>'Inženjerska fizika 2', 'institucija'=>'1', 
        'kratki_naziv'=>'IF2', 'tippredmeta'=>'1', 'ects'=>'5', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'5', 'sifra'=>'PG02', 'naziv'=>'Osnove elektrotehnike', 'institucija'=>'1', 
        'kratki_naziv'=>'OE', 'tippredmeta'=>'1', 'ects'=>'7.5', 'sati_predavanja'=>'48',
        'sati_vjezbi'=>'4', 'sati_tutorijala'=>'28',));
        $I->haveInDatabase('predmet',array('id'=>'6', 'sifra'=>'PG07', 'naziv'=>'Električni krugovi 1', 'institucija'=>'1', 
        'kratki_naziv'=>'EK1', 'tippredmeta'=>'1', 'ects'=>'6.5', 'sati_predavanja'=>'45',
        'sati_vjezbi'=>'10', 'sati_tutorijala'=>'20',));
        $I->haveInDatabase('predmet',array('id'=>'7', 'sifra'=>'PG05', 'naziv'=>'Osnove računarstva', 'institucija'=>'1', 
        'kratki_naziv'=>'OR', 'tippredmeta'=>'1', 'ects'=>'6', 'sati_predavanja'=>'44',
        'sati_vjezbi'=>'26', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'8', 'sifra'=>'PG09', 'naziv'=>'Tehnike programiranja', 'institucija'=>'1', 
        'kratki_naziv'=>'TP', 'tippredmeta'=>'1', 'ects'=>'6', 'sati_predavanja'=>'44',
        'sati_vjezbi'=>'26', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'9', 'sifra'=>'PG04', 'naziv'=>'Linearna algebra i geometrija', 'institucija'=>'1', 
        'kratki_naziv'=>'LAG', 'tippredmeta'=>'1', 'ects'=>'5', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'10', 'sifra'=>'PG10', 'naziv'=>'Elektronički elementi i sklopovi', 'institucija'=>'1', 
        'kratki_naziv'=>'EES', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'11', 'sifra'=>'ETF RIO DM 2360', 'naziv'=>'Diskretna matematika', 'institucija'=>'1', 
        'kratki_naziv'=>'DM', 'tippredmeta'=>'1', 'ects'=>'5.5', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'21',));
        $I->haveInDatabase('predmet',array('id'=>'12', 'sifra'=>'ETF RIO OS 2360', 'naziv'=>'Operativni sistemi', 'institucija'=>'1', 
        'kratki_naziv'=>'OS', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'28',
        'sati_vjezbi'=>'22', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'13', 'sifra'=>'ETF RIO ASP 2360', 'naziv'=>'Algoritmi i strukture podataka', 'institucija'=>'1', 
        'kratki_naziv'=>'ASP', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'38',
        'sati_vjezbi'=>'22', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'14', 'sifra'=>'ETF RIO RPR 2360', 'naziv'=>'Razvoj programskih rješenja', 'institucija'=>'1', 
        'kratki_naziv'=>'RPR', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'40',
        'sati_vjezbi'=>'20', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'15', 'sifra'=>'ETF RIO LD 2360', 'naziv'=>'Logički dizajn', 'institucija'=>'1', 
        'kratki_naziv'=>'LD', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'40',
        'sati_vjezbi'=>'20', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'16', 'sifra'=>'ETF RII SP 2345', 'naziv'=>'Sistemsko programiranje', 'institucija'=>'1', 
        'kratki_naziv'=>'SP', 'tippredmeta'=>'1', 'ects'=>'4.5', 'sati_predavanja'=>'30',
        'sati_vjezbi'=>'15', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'17', 'sifra'=>'ETF RII VS 2345', 'naziv'=>'Vjerovatnoća i statistika', 'institucija'=>'1', 
        'kratki_naziv'=>'VIS', 'tippredmeta'=>'1', 'ects'=>'4.5', 'sati_predavanja'=>'30',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'15',));
        $I->haveInDatabase('predmet',array('id'=>'18', 'sifra'=>'ETF RIO RA 2460', 'naziv'=>'Računarske arhitekture', 'institucija'=>'1', 
        'kratki_naziv'=>'RA', 'tippredmeta'=>'1', 'ects'=>'5.5', 'sati_predavanja'=>'40',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'15',));
        $I->haveInDatabase('predmet',array('id'=>'19', 'sifra'=>'ETF RIO OOAD 2460', 'naziv'=>'Objektno orijentisana analiza i dizajn', 'institucija'=>'1', 
        'kratki_naziv'=>'OOAD', 'tippredmeta'=>'1', 'ects'=>'5.5', 'sati_predavanja'=>'40',
        'sati_vjezbi'=>'15', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'20', 'sifra'=>'ETF RIO OBP 2460', 'naziv'=>'Osnove baza podataka', 'institucija'=>'1', 
        'kratki_naziv'=>'OBP', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'20',));
        $I->haveInDatabase('predmet',array('id'=>'21', 'sifra'=>'ETF RIO OIS 2460', 'naziv'=>'Osnove informacionih sistema', 'institucija'=>'1', 
        'kratki_naziv'=>'OIS', 'tippredmeta'=>'1', 'ects'=>'5.0', 'sati_predavanja'=>'39',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'20',));
        $I->haveInDatabase('predmet',array('id'=>'22', 'sifra'=>'ETF RII IE 2445', 'naziv'=>'Internet ekonomija', 'institucija'=>'1', 
        'kratki_naziv'=>'IE', 'tippredmeta'=>'1', 'ects'=>'4.5', 'sati_predavanja'=>'30',
        'sati_vjezbi'=>'15', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'23', 'sifra'=>'ETF RII CCI 2445', 'naziv'=>'CAD - CAM inženjering', 'institucija'=>'1', 
        'kratki_naziv'=>'CCI', 'tippredmeta'=>'1', 'ects'=>'4.5', 'sati_predavanja'=>'30',
        'sati_vjezbi'=>'15', 'sati_tutorijala'=>'0',));
        $I->haveInDatabase('predmet',array('id'=>'24', 'sifra'=>'ETF RII OT 2445', 'naziv'=>'Osnove telekomunikacija', 'institucija'=>'1', 
        'kratki_naziv'=>'OT', 'tippredmeta'=>'1', 'ects'=>'4.5', 'sati_predavanja'=>'30',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'15',));
        $I->haveInDatabase('predmet',array('id'=>'25', 'sifra'=>'ETF RII PAI 2445', 'naziv'=>'Praktikum iz automatike i informatike', 'institucija'=>'1', 
        'kratki_naziv'=>'PAI', 'tippredmeta'=>'1', 'ects'=>'4.5', 'sati_predavanja'=>'10',
        'sati_vjezbi'=>'30', 'sati_tutorijala'=>'0',));

    }
    
    public function fixtureSemestarRandomPredmeta() {
        $I = $this;
        $random = $I->getSemestarPredmeta();
        $id = NULL;
        foreach ($random as $predmet) {
            $id = $I->haveInDatabase('predmet', $predmet);
            $predmet['naziv']=$id.$predmet['naziv'];
            \Codeception\Util\Debug::debug($id." :".$predmet['naziv']);
            $I->haveInDatabase('ponudakursa', array(
                'predmet'=>$id,
                'studij'=>'1',
                'semestar'=>'1',
                'obavezan'=>'1',
                'akademska_godina'=>'1',
            ));
        }
        return $random;
    }
    
    public function adminNapraviStavkuNastavniPlanRandomPredmeta($semestar = 1,$smjer = 'TKBsc',$predmeti = null){
        $I = $this;
        if($predmeti == null){
            $predmeti = $I->fixtureSemestarRandomPredmeta();
            $I->reloadPage();
        }
//        $tre = $predmeti[0];
////        $tra = $tre['predmet'];
        foreach ($predmeti as $val) {
//            $ime = $tre['id'].$tra['naziv'];
            $obavezan = 1;//$I->getFaker()->boolean(80);
            $I->adminDodajStavkuNastavnogPlana($val['naziv'],$obavezan , $semestar, $smjer);
            
        }
        return $predmeti;
    }
    
    private function fixturePredmetUKurs($naziv,$obavezan,$semestar,$smjer = 1) {
        $I = $this;
        //$mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));
        $id = $I->grabFromDatabase('predmet', 'id', array(
            'naziv'=>$naziv,
        ));
        \Codeception\Util\Debug::debug('Debug: fixturePredmetUKurs');
        \Codeception\Util\Debug::debug("Debug, naziv: ".$naziv." ,id: ".$id);
        $I->haveInDatabase('ponudakursa', array(
            'predmet'=>$id,
            'studij'=>$smjer,
            'semestar'=>$semestar,
            'obavezan'=>$obavezan,
            'akademska_godina'=>'1',
        ));
    }
    
    public function adminNapraviNastavniPlanRandomPredmeta() {
        //prva godina zajedno //na stranici za dodavanje stavke
        $I = $this;
//        $I->reloadPage();
        $predmetiPrva1 = $I->fixtureSemestarRandomPredmeta(); 
        $predmetiPrva2 = $I->fixtureSemestarRandomPredmeta();
        
        $predmeti = null;
        
        for($i=0;$i<=(4*4);$i++){
            $predmeti[] = $I->fixtureSemestarRandomPredmeta();
        }
        $I->reloadPage();
        $I->amOnPage("/");
        $I->click(\adminHomePage::$studentskaSluzbaLink);
        $I->click(\studentskaSluzbaPage::$navKreirajPlanStudijaLink);
        //prva godina
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(1, 'RIBsc', $predmetiPrva1);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(1, 'TKBsc', $predmetiPrva1);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(1, 'EEBsc', $predmetiPrva1);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(1, 'AiEBsc', $predmetiPrva1);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(2, 'RIBsc', $predmetiPrva2);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(2, 'TKBsc', $predmetiPrva2);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(2, 'EEBsc', $predmetiPrva2);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(2, 'AiEBsc', $predmetiPrva2);
        
////        //ri
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(3, 'RIBsc', $predmeti[0]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(4, 'RIBsc', $predmeti[1]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(5, 'RIBsc', $predmeti[2]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(6, 'RIBsc', $predmeti[3]);
////        
////        //tk
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(3, 'TKBsc', $predmeti[4]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(4, 'TKBsc', $predmeti[5]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(5, 'TKBsc', $predmeti[6]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(6, 'TKBsc', $predmeti[7]);
////                //ee
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(3, 'EEBsc', $predmeti[8]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(4, 'EEBsc', $predmeti[9]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(5, 'EEBsc', $predmeti[10]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(6, 'EEBsc', $predmeti[11]);
////        //Aie
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(3, 'AiEBsc', $predmeti[12]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(4, 'AiEBsc', $predmeti[13]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(5, 'AiEBsc', $predmeti[14]);
        $I->adminNapraviStavkuNastavniPlanRandomPredmeta(6, 'AiEBsc', $predmeti[15]);
        
    }
    
    private function createStudent() {
        $I = $this;
        $osoba = $I->getOsoba();
        $osobaId = $I->haveInDatabase('osoba', $osoba);
        $UserPass = $I->getUsernameAndPass();
        $I->haveInDatabase('auth', array(
            'id'=>$osobaId,
            'login'=>$UserPass['login'],
            'password'=>$UserPass['password'],
            'aktivan'=>1,
        ));
        exec("mysql -u root zamger;insert into privilegije values (".$osobaId.",student);");
        
        $this->studentLogin = $UserPass['login'];
        $this->studentPassword = $UserPass['password'];
    }
    
    private $studentLogin;
    private $studentPassword;
    
    public function loginKaoStudent() {
        if(!$this->studentLogin||!$this->studentPassword){
            $this->loginKaoAdmin();
            $this->adminDodajStudenta('student', 'student');
            $this->logout();
        }
        $this->login('student', 'student');
    }
    
    private $nastavnikLogin;
    private $nastavnikPassword;
    
    public function loginKaoNastavnik() {
        if(!$this->nastavnikLogin||!$this->nastavnikPassword){
            $this->loginKaoAdmin();
            $this->adminDodajNastavnika('nastavnik', 'nastavnik');
            $this->logout();
        }
        $this->login('nastavnik', 'nastavnik');
    }
    
    private $studentskaLogin;
    private $studentskaPassword;
    
    public function loginKaoStudentska() {
        if(!$this->studentskaLogin||!$this->studentskaPassword){
            $this->loginKaoAdmin();
            $this->adminDodajStudentskaOsoba('studentska', 'studentska');
            $this->logout();
        }
        $this->login('studentska', 'studentska');
    }
}
