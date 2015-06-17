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
    public function _adminDodajeNoviPredmet(AcceptanceTester $I,$scenario)
    {
        $predmet = 'dunmmn';
        
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->loginKaoAdmin();
        $I->wantTo('dodati novi predmet');
        $I->adminDodajNastavnika('nastavnik', 'nastavnik');
        $I->am('administrator');
        $I->amOnPage('/');
        $I->canSee('Studentska služba');
        $I->click('Studentska služba');
        $I->canSee('Predmeti');
        $I->click('Predmeti');
        $I->canSee('Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazn');
        $I->canSeeElement('input[name=naziv]');
        $I->fillField('input[name=naziv]', $predmet);
        $I->click('Dodaj');
        $I->canSee('Editovanje predmeta ');
        $I->click('Editovanje predmeta "'.$predmet.'"');
        $I->canSee($predmet);
        $I->selectOption(\predmetPage::$izaberiNastavnika['Nastavnik'], 'nastavnik nastavnik');
        $I->click(\predmetPage::$izaberiNastavnika['Dodaj']);
        $I->canSee('Nastavniku dato pravo pristupa predmetu');
        
    }
    
    public function dodajPredmet(AcceptanceTester $I,$scenario){
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->adminDodajPredmet('dummyPredmet', 'DP01', '1', 
                '5', '0', '0');
    }
    
    public function dodajPredmetIPonudiKursSvimBscSemestar1Obavezan(AcceptanceTester $I,$scenario){
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->adminDodajPredmetIPonuduKursaSvimBsc('dummyPredmet 2', 'DP02', '2', '5', 
                '0', '0', true, 1);
        $I->canSee("Računarstvo i informatika (BSc), 1. semestar");
        $I->canSee("Automatika i elektronika (BSc), 1. semestar");
        $I->canSee("Elektroenergetika (BSc), 1. semestar");
        $I->canSee("Telekomunikacije (BSc), 1. semestar");
//        $I->cantSee('izborni');
    }
}