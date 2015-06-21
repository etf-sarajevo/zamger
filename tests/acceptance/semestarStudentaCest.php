<?php
use \AcceptanceTester;

class semestarStudentaCest
{
    private $predmetiSemestar1;
    private $predmetiSemestar2;
     /**
     *  @actor AcceptanceTester\MemberSteps
     *  group student
     *  depends nemaPhpErroraNaLoginPage
     */
    public function _before(AcceptanceTester $I){
//        $faker = $I->getFaker();
        $I->am('administrator');
        $I->amOnPage('/');
        $I->loginKaoAdmin();
        
        //napraviti stavke nastavnog plana
        $I->wantTo('dodati predmeta za 2 semestra');
        $I->amOnPage('/');
        $I->click(adminHomePage::$studentskaSluzbaLink);
        $I->click(studentskaSluzbaPage::$navKreirajPlanStudijaLink);
        $this->predmetiSemestar1 = $I->adminNapraviStavkuNastavniPlanRandomPredmeta(1,$smjer = 'RIBsc');
        $this->predmetiSemestar2 = $I->adminNapraviStavkuNastavniPlanRandomPredmeta(2,$smjer = 'RIBsc');
        
        //namjestanje parametara
        $I->wantTo('promjenitit parametre studija');
        $I->amOnPage('/');
        $I->click('Parametri studija');
        $I->click('Studij');
        //ostaviti samo RI sa 2 semestra
        $I->click("(//input[@name='_lv_action_delete'])[4]");
        $I->click("(//input[@name='_lv_action_delete'])[3]");
        $I->click("(//input[@name='_lv_action_delete'])[2]");
        $I->fillField("input[name=_lv_column_zavrsni_semestar]", "2");
        $I->click('input[type="submit"]');
        
        
    }

    public function _after(AcceptanceTester $I){
    }

    /**
     *  @actor AcceptanceTester\MemberSteps
     *  @group student
     *  depends nemaPhpErroraNaLoginPage
     */
    public function login(AcceptanceTester $I){
        $faker = $I->getFaker();
        $I->am('student');
        $I->wantTo('login na zamger');
        $I->lookForwardTo('vidim koje predmete imam');
        $I->wantToTest('da li _before radi');
    }
}