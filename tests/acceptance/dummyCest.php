<?php
use \AcceptanceTester;
use AcceptanceTester\MemberSteps;

class dummyCest
{    
    public function _before(AcceptanceTester $I)
    {
//        $I->loginKaoAdmin();
        $I->loginKaoAdmin();
        $I->am('Administrator');
    }
    
    public function _after(AcceptanceTester $I)
    {
        $I->logout();
    }

    // tests
    public function _adminDodajeNoviPredmet(AcceptanceTester $I,$scenario)
    {
        $predmet = 'dunmmn';
//        
//        $I = new AcceptanceTester\MemberSteps($scenario);        
//        $I->wantTo('dodati novi predmet');
//        $I->adminDodajNastavnika('nastavnik', 'nastavnik');
//        $I->am('administrator');
//        $I->amOnPage('/');
//        $I->canSee('Studentska služba');
//        $I->click('Studentska služba');
//        $I->canSee('Predmeti');
//        $I->click('Predmeti');
//        $I->canSee('Za prikaz svih predmeta na akademskoj godini, ostavite polje za pretragu prazn');
//        $I->canSeeElement('input[name=naziv]');
//        $I->fillField('input[name=naziv]', $predmet);
//        $I->click('Dodaj');
//        $I->canSee('Editovanje predmeta ');
//        $I->click('Editovanje predmeta "'.$predmet.'"');
//        $I->canSee($predmet);
//        $I->selectOption(\predmetPage::$izaberiNastavnika['Nastavnik'], 'nastavnik nastavnik');
//        $I->click(\predmetPage::$izaberiNastavnika['Dodaj']);
//        $I->canSee('Nastavniku dato pravo pristupa predmetu');
        
    }
    
    public function dodajPredmet(AcceptanceTester $I,$scenario){
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->wantTo('dodati novi predmet');
        $I->adminDodajPredmet('dummyPredmet', 'DP01', '1', 
                '5', '0', '0');
    }
    
    public function dodajPredmetIPonudiKursSvimBscSemestar1Obavezan(AcceptanceTester $I,$scenario){
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->wantTo('Dodati novi predmeti i ponuditi ga kao kurs');
        $I->adminDodajPredmetIPonuduKursaSvimBsc('dummyPredmet 2', 'DP02', '2', '5', 
                '0', '0', true, 1);
        $I->canSee("Računarstvo i informatika (BSc), 1. semestar");
        $I->canSee("Automatika i elektronika (BSc), 1. semestar");
        $I->canSee("Elektroenergetika (BSc), 1. semestar");
        $I->canSee("Telekomunikacije (BSc), 1. semestar");
//        $I->cantSee('izborni');
    }
    
    public function napraviPrvuGodinu(AcceptanceTester $I,$scenario){
        $I = new AcceptanceTester\MemberSteps($scenario);
        $I->wantTo('Napraviti 10 predmeta za prvu godinu');
//        $I->adminDodajPredmetIPonuduKursaSvimBsc
//        ($predmet, $sifra, $ects, $satiPredavanja, 
//        $satiVjezbi, $satiTutorijala, $obavezan, $semestar)
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
    }
}