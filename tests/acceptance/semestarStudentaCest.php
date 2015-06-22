<?php
use \AcceptanceTester;

class semestarStudentaCest
{
    private $predmetiSemestar1;
    private $predmetiSemestar2;
    
    private $predmetniAnsambl;
     /**
     *  @actor AcceptanceTester\MemberSteps
     *  group student
     *  depends nemaPhpErroraNaLoginPage
     */
    public function _before(AcceptanceTester $I){
//        $faker = $I->getFaker();
        $I->am('administrator');
        $I->adminIskljuciPopUp();
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
        //nova akademska godina
//        $I->click('Akademska godina');
//        $I->fillField("(//input[@name='_lv_column_naziv'])[2]", "2015/2016");
//        $I->checkOption("(//input[@name='_lv_column_aktuelna'])[2]");
//        $I->click("(//input[@value=' Pošalji '])[2]");
//        $I->uncheckOption("input[name=_lv_column_aktuelna]"); //obrisemo godinu
//        $I->click("inpit[name=_lv_action_delete]");
//        $I->click('input[type="submit"]');
        $I->click('Nova akademska godina');
        $I->canSee("Ovaj modul kreira novu akademsku godinu u bazi, a zatim za datu godinu kreira sve predmete koji su predviđeni aktuelnim planovima svih kreiranih studija.");
//        $I->see("2015/2016");
        $I->click('input[type="submit"]');
        $I->click('input[type="submit"]');
        $I->canSee("Podaci su ubačeni.");
//        $I->wantTo('dodati nastavnike na predmete');
        
        //
        $I->click(adminHomePage::$studentskaSluzbaLink);
        $I->click(studentskaSluzbaPage::$navPlanStudijaLink);
//        $I->click("Računarstvo i informatika (BSc) (2015/2016)");
//        foreach ($this->predmetiSemestar1 as $predmet) {
//            
//        }
    
        $I->adminDodajNastavnika('nastavnik','nastavnik');
        //angazuj na predmetu
        $I->selectOption("input[name=_lv_column_angazman_status]", "odgovorni nastavnik");
        $I->click("//input[@value=' Dodaj ']");
        //prava pristupa
        $I->click("(//input[@value=' Dodaj '])[2]");
    }

    public function _after(AcceptanceTester $I){
    }

    /**
     *  @actor AcceptanceTester\MemberSteps
     *  @group student
     *  depends nemaPhpErroraNaLoginPage
     */
    public function login(AcceptanceTester $I){
        $I->am('student');
        $I->wantTo('login na zamger');
        $I->lookForwardTo('vidim koje predmete imam');
        $I->wantToTest('da li _before radi');
    }
}