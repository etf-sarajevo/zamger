<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    public function logout()
    {
        $I = $this;
        $I->canSeeLink("Odjava");
        $I->click("Odjava");
        $I->canSeeInCurrentUrl("?sta=logout");
    }
    
    public function login($username,$password) {
        $I = $this;
        $I->wantTo('login');
        $I->amOnPage(\loginPage::$URL);
        $I->canSee(\loginPage::$text);
        $I->fillField(\loginPage::$username, $username);
        $I->fillField(\loginPage::$pass, $password);
        $I->click(\loginPage::$button);
        #$I->see(\loginPage::$homeTextAdmin);
    }
    
    public function loginKaoAdmin()
    {
        $I = $this;
        $I->wantTo('login kao administrator');
        $I->login('admin', 'admin');
        $I->see(\loginPage::$homeTextAdmin);
    }
    
    public function  loginKaoStudent()
    {
//        $I = $this;
//        $I->wantTo('login kao student');
//        $I->amOnPage(\loginPage::$URL);
//        $I->see("bolognaware");
        //$I->fillField(\pocetnaPage::$username, 'admin');
        //$I->fillField(\pocetnaPage::$pass, 'admin');
        //$I->click(\pocetnaPage::$button);
        //$I->see(\pocetnaPage::$homeTextStudent);
        //@todo login studenta
    }
    
    public function loginKaoStudentskaSluzba()
    {}
    
    public function loginKaoProfesor()
    {}
    
    public function adminDodajOsobu($ime,$prezime)
    {
        $I=$this;
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
        $I->canSeeLink($ime." ".$prezime);
    }
    public function adminDodajOsobuTipa($ime,$prezime,$tip)
    {
        $I = $this;
        $I->adminDodajOsobu($ime,$prezime);
        $I->canSeeLink($ime." ".$prezime);
        $I->click($ime." ".$prezime);
        $I->canSee($ime." ".$prezime);
        $I->seeElement("input[name=".$tip."]");
        $I->checkOption("input[name=".$tip."]");
        $I->waitForElement("input[name=".$tip."]",30);
        $I->canSeeCheckboxIsChecked("input[name=".$tip."]");        
        $I->click("Promijeni"); 
        $I->canSee("Data privilegija ");
    }
    public function adminDodajStudenta($ime,$prezime)
    {
        $I = $this;
        $I->adminDodajOsobuTipa($ime,$prezime,"student");
        $I->fillField('[name=login]', $ime);
        $I->fillField('[name=password]', $ime);
        $I->checkOption('[name=aktivan]');
        $I->click("//input[@value=' Dodaj novi ']");
        
    }
    
    public function adminDodajNastavnika($ime,$prezime)
    {
        $I = $this;
        $I->adminDodajOsobuTipa($ime,$prezime,"nastavnik");
    }
    
    public function adminDodajPrijemniOsoba($ime,$prezime)
    {
        $I = $this;
        $I->adminDodajOsobuTipa($ime,$prezime,"prijemni");
    }
    
    public function adminDodajStudentskaOsoba($ime,$prezime)
    {
        $I = $this;
        $I->adminDodajOsobuTipa($ime,$prezime,"studentska");
    }
    
    public function adminDodajSiteadminOsoba($ime,$prezime)
    {
        $I = $this;
        $I->adminDodajOsobuTipa($ime,$prezime,"siteadmin");
    }
    
    public function adminDodajPredmet($predmet,$sifra,$ects,$satiPredavanja,$satiVjezbi,$satiTutorijala)
    {
        $I = $this;
        $I->am('Administrator');
        $I->wantTo('Dodati novi predmet kao administrator');
        $I->amOnPage('/');
//        $I->debugOutput("Predmet: ".$predmet.", sifra: ".$sifra
//                .", ects: ".$ects.", satiPredavanja: ".$satiPredavanja
//                .", satiVjezbi".$satiVjezbi.", satiTutorijala".$satiTutorijala);
        $I->canSee("Studentska služba");
        $I->click("Studentska služba");
        $I->canSee("Predmeti");
        $I->click("Predmeti");
        
        $I->canSee("Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.");
        $I->canSeeElement('input[name=naziv]');
        $I->fillField('input[name=naziv]', $predmet);
        $I->canSee("input[@value=' Dodaj ']");
        $I->click("input[@value=' Dodaj ']");
        
        $I->canSeeLink('Editovanje predmeta "'.$predmet.'"');
        $I->click('Editovanje predmeta "'.$predmet.'"');
        
        $I->canSee('Izmijeni');
        $I->click("input[type=\"submit\"]");
        
        $I->canSee('Izmjena podataka o predmetu');
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sifra'], $sifra);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Ects'], $ects);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sati predavanja'], $satiPredavanja);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sati vjezbi'], $satiVjezbi);
        $I->fillField(\predmetPage::$izmjenaPodatakaOPredmetu['Sati tutorijala'], $satiTutorijala);
        $I->click("input[type=\"submit\"]");
        $I->canSee("Podaci o predmetu izmijenjeni");
        $I->canSee("Nazad");
        $I->click("Nazad");
    }
    private function _adminDodajPonuduKursaSmjeru($obavezan,$semestar,$smjer){
        $I->canSeeInCurrentUrl('akcija=dodaj_pk');///zamger/index.php?sta=studentska/predmeti&predmet=4&ag=1&akcija=dodaj_pk
        $I->selectOption('name=_lv_column_studij', $smjer);
        $I->fillField('input[name=semestar]', $semestar);
        if($obavezan){
            $I->checkOption('input[name=obavezan]');
        }
        $I->click('input[type="submit"]');
        $I->canSee("Ponuda kursa uspješno kreirana");
        $I->click("Nazad");
    }

    public function adminDodajPredmetIPonuduKursaSmjeru($predmet,$sifra,
            $ects,$satiPredavanja,$satiVjezbi,$satiTutorijala,$obavezan,$semestar,$smjer){
        $I = $this;
        $I->amOnPage('/');
        $I->adminDodajPredmet($predmet,$sifra,$ects,$satiPredavanja,$satiVjezbi,$satiTutorijala);
        $I->click('Dodaj ponudu kursa');
        $I->_adminDodajPonuduKursaSmjeru($obavezan,$semestar,$smjer);
    }
    
    public function adminDodajPredmetIPonuduKursaRIBsc($predmet,$sifra,
            $ects,$satiPredavanja,$satiVjezbi,$satiTutorijala,$obavezan,$semestar){
        
        $I=$this;
        $I->amOnPage('/');
        $I->adminDodajPredmetIPonuduKursaSmjeru($predmet, $sifra, $ects, $satiPredavanja, 
                $satiVjezbi, $satiTutorijala, $obavezan, $semestar, 'Računarstvo i informatika (BSc)');
    }
    
    public function adminDodajPredmetIPonuduKursaTKBsc($predmet,$sifra,
            $ects,$satiPredavanja,$satiVjezbi,$satiTutorijala,$obavezan,$semestar){
        
        $I=$this;
        $I->amOnPage('/');
        $I->adminDodajPredmetIPonuduKursaSmjeru($predmet, $sifra, $ects, $satiPredavanja, 
                $satiVjezbi, $satiTutorijala, $obavezan, $semestar, 'Telekomunikacije (BSc)');
    }
    
    public function adminDodajPredmetIPonuduKursaAiEBsc($predmet,$sifra,
            $ects,$satiPredavanja,$satiVjezbi,$satiTutorijala,$obavezan,$semestar){
        
        $I=$this;
        $I->amOnPage('/');
        $I->adminDodajPredmetIPonuduKursaSmjeru($predmet, $sifra, $ects, $satiPredavanja, 
                $satiVjezbi, $satiTutorijala, $obavezan, $semestar, 'Automatika i elektronika (BSc)');
    }
    
    public function adminDodajPredmetIPonuduKursaEEBsc($predmet,$sifra,
            $ects,$satiPredavanja,$satiVjezbi,$satiTutorijala,$obavezan,$semestar){
        
        $I=$this;
        $I->amOnPage('/');
        $I->adminDodajPredmetIPonuduKursaSmjeru($predmet, $sifra, $ects, $satiPredavanja, 
                $satiVjezbi, $satiTutorijala, $obavezan, $semestar, 'Elektroenergetika (BSc)');
    }
    
    public function adminDodajPredmetIPonuduKursaSvimBsc($predmet,$sifra,
            $ects,$satiPredavanja,$satiVjezbi,$satiTutorijala,$obavezan,$semestar){
        
        $I=$this;
        $I->amOnPage('/');
        $I->adminDodajPredmet($predmet, $sifra, $ects, $satiPredavanja, 
                $satiVjezbi, $satiTutorijala);
        $I->_adminDodajPonuduKursaSmjeru($obavezan, $semestar, 'Računarstvo i informatika (BSc)');
        $I->_adminDodajPonuduKursaSmjeru($obavezan, $semestar, 'Telekomunikacije (BSc)');
        $I->_adminDodajPonuduKursaSmjeru($obavezan, $semestar, 'Elektroenergetika (BSc)');
        $I->_adminDodajPonuduKursaSmjeru($obavezan, $semestar, 'Automatika i elektronika (BSc)');
    }
    
    public function adminDodaj10PredmetaKursevaZaGodinu1() {
        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Inžinjerska matematika 1', 'PG01', '6.5', 49, 
            0, 26, true, 1);
        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Inžinjerska matematika 2', 'PG06', '7.5', 52, 
            0, 28, true, 2);

        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Inženjerska fizika 1', 'PG03', '5.0', 39, 
            0, 21, true, 1);
        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Inženjerska fizika 2', 'PG08', '5.0', 39, 
            0, 21, true, 2);

        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Osnove elektrotehnike', 'PG02', '7.5', 48, 
            4, 28, true, 1);
        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Električni krugovi 1', 'PG07', '6.5', 45, 
            10, 20, true, 2);

        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Osnove računarstva', 'PG05', '6', 44, 
            26, 0, true, 1);
        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Tehnike programiranja', 'PG09', '6', 44, 
            26, 0, true, 2);

        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Linearna algebra i geometrija', 'PG04', '5', 39, 
            0, 21, true, 1);
        $I->adminDodajPredmetIPonuduKursaSvimBsc
            ('Elektronički elementi i sklopovi', 'PG10', '5', 39, 
            0, 21, true, 2);

    }
}