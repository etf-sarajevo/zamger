<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    public function loginAsAdmin()
    {
        $I = $this;
        $I->wantTo('login kao administrator');
        $I->amOnPage(\pocetnaPage::$URL);
        $I->see("bolognaware");
        $I->fillField(\pocetnaPage::$username, 'admin');
        $I->fillField(\pocetnaPage::$pass, 'admin');
        $I->click(\pocetnaPage::$button);
        $I->see(\pocetnaPage::$homeTextAdmin);
    }
    
    public function  loginAsStudent()
    {
        $I = $this;
        $I->wantTo('login kao student');
        $I->amOnPage(\pocetnaPage::$URL);
        $I->see("bolognaware");
        //$I->fillField(\pocetnaPage::$username, 'admin');
        //$I->fillField(\pocetnaPage::$pass, 'admin');
        //$I->click(\pocetnaPage::$button);
        //$I->see(\pocetnaPage::$homeTextStudent);
        //@todo login studenta
    }
    
    public function loginAsStudentskaSluzba()
    {}
    
    public function loginAsProfesor()
    {}
}