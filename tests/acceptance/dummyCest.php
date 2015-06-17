<?php
use \AcceptanceTester;
use AcceptanceTester\MemberSteps;

class dummyCest
{    
    public function _before(AcceptanceTester $I)
    {
//        $I->loginKaoAdmin();
    }
    
    public function _after(AcceptanceTester $I)
    {
//        $I->logout();
    }

    // tests
    public function adminDodajeNoviPredmet(AcceptanceTester $I,$scenario)
    {
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->loginKaoAdmin();
        $I->wantTo('dodati novi predmet');
        $I->am('administrator');
        $I->amOnPage('/');
        $I->canSee('Studentska služba');
        $I->click('Studentska služba');
        $I->canSee('Predmeti');
        $I->click('Predmeti');
        $I->canSee('Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazn');
        $I->canSeeElement('input[name=naziv]');
        $I->fillField('input[name=naziv]', 'dummyPredmet2');
        $I->click('Dodaj');
        $I->canSee('Editovanje predmeta ');
        $I->click('Editovanje predmeta "dummyPredmet2"');
        $I->canSee('dummyPredmet');
        $I->selectOption(\predmetPage::$izaberiNastavnika['Nastavnik'], 'Admin Site');
        $I->click(\predmetPage::$izaberiNastavnika['Dodaj']);
        //?sta=studentska/intro
    }
}