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
//      
        $I->canSee("Studentska služba");
        $I->click("Studentska služba");
        $I->canSee("Predmeti");
        $I->click("Predmeti");
        
        $I->canSee("Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazno.");
        $I->canSeeElement('input[name=naziv]');
        $I->fillField('input[name=naziv]', $predmet);
//        $I->canSee("input[value=' Dodaj ']");
        $I->click("input[value=' Dodaj ']");
        
        $I->canSeeLink('Editovanje predmeta "'.$predmet.'"');
        $I->click('Editovanje predmeta "'.$predmet.'"');
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
//        $I->amOnUrl($linkTrenutni);
    }
    
    private function fillNovaPonudaKursa($studij,$semestar,$obavezan) {
        $I = $this;
        $I->canSee('Nova ponuda kursa za predmet');
        $I->seeElement('select[name=_lv_column_studij]');
        $I->selectOption('select[name=_lv_column_studij]', $studij);
        $I->fillField('input[name=semestar]', $semestar);
        $I->seeElement('input[name=obavezan]');
        if($obavezan)
        {
            $I->checkOption('input[name=obavezan]');
        }
        $I->click('input[type="submit"]');
        $I->canSee("Ponuda kursa uspješno kreirana");
    }
    
    private function fillNovaPonudaKursaArray(array $var){
        $I = $this;
        $I->canSee('Dodaj ponudu kursa');
        $I->click('Dodaj ponudu kursa');
//        array_walk($var, fillNovaPonudaKursa($studij,$semestar,$obavezan));
        foreach ($var as $value) {
            $I->fillNovaPonudaKursa($value['studij'], 
                    $value['semestar'], $value['obavezan']);
        }
    }
    
    public function adminDodajPredmetKursSvima($predmet,$sifra,$ects,
            $satiPredavanja,$satiVjezbi,$satiTutorijala,$studij,
            $semestar,$obavezan){
        $I = $this;
        $I->adminDodajPredmet($predmet, $sifra, $ects, 
            $satiPredavanja, $satiVjezbi, $satiTutorijala);
        $var = array(
            array(
                'studij'=>1,
                'semestar'=>$semestar,
                'obavezan'=>$obavezan
            ),
            array(
                'studij'=>2,
                'semestar'=>$semestar,
                'obavezan'=>$obavezan
            ),
            array(
                'studij'=>3,
                'semestar'=>$semestar,
                'obavezan'=>$obavezan
            ),
            array(
                'studij'=>4,
                'semestar'=>$semestar,
                'obavezan'=>$obavezan
            )
        );        
        $I->fillNovaPonudaKursaArray($var);
//        $I->click('Nazad');
    }

    public function adminDodajPredmetKurs($predmet,$sifra,$ects,
            $satiPredavanja,$satiVjezbi,$satiTutorijala,$studij,
            $semestar,$obavezan){
        $I = $this;
        $I->adminDodajPredmet($predmet, $sifra, $ects, 
                $satiPredavanja, $satiVjezbi, $satiTutorijala);
        $I->canSee('Dodaj ponudu kursa');
        $I->click('Dodaj ponudu kursa');
        $I->fillNovaPonudaKursa($I,$studij, $semestar, $obavezan);
        $I->click("Nazad");
    }
    
  
}