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
}