<?php 
$predmetiSemestar1 = null;
$predmetiSemestar2n = null;
    
$predmetniAnsambl = null;
$studenti = null;

//function _dajPredmetniAnsambl($naziv) {
//    foreach ($this->predmetniAnsambl as $stavka) {
//        if($stavka['naziv']==$naziv){
//            return $stavka;
//        }                
//    }
//    return null;
//}

$scenario->group('student');
$I->am('administrator');
//        $I->adminIskljuciPopUp();
$I->amOnPage('/');
$I->loginKaoAdmin();
//napraviti stavke nastavnog plana
$I->wantTo('dodati predmeta za 2 semestra');
$I->amOnPage('/');
$I->click(adminHomePage::$studentskaSluzbaLink);
$I->click(studentskaSluzbaPage::$navKreirajPlanStudijaLink);
$predmetiSemestar1 = $I->adminNapraviStavkuNastavniPlanRandomPredmeta(1,$smjer = 'RIBsc');
$predmetiSemestar2 = $I->adminNapraviStavkuNastavniPlanRandomPredmeta(2,$smjer = 'RIBsc');

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
$I->click("Računarstvo i informatika (BSc) (2014/2015)");
//        foreach ($this->predmetiSemestar1 as $predmet) {
//            
//        }

//        $I->adminDodajNastavnika('nastavnik','nastavnik');
//        //angazuj na predmetu
//        $I->selectOption("input[name=_lv_column_angazman_status]", "odgovorni nastavnik");
//        $I->click("//input[@value=' Dodaj ']");
//        //prava pristupa
//        $I->click("(//input[@value=' Dodaj '])[2]");

foreach ($predmetiSemestar1 as $predmet) {
    $faker = $I->getFaker();
    $usernamePassword = $I->getUsernameAndPass();
    $imePrezime = $I->getImePrezime(); 
    $I->adminDodajNastavnika($imePrezime['ime'],$imePrezime['prezime'],$usernamePassword['login'],$usernamePassword['password']);
    $nastavnik = array(
        'login' => $usernamePassword['login'],
        'password'=>$usernamePassword['password'],
        'ime' => $imePrezime['ime'],
        'prezime' => $imePrezime['prezime'],
    );

    $ansa = array(
        'naziv'=>$predmet['naziv'],
        'nastavnik' => $nastavnik,
    );
    $predmetniAnsambl[] = $ansa;
    $I->selectOption("select[name=_lv_column_angazman_status]", "odgovorni nastavnik");
    $I->selectOption("select[name=predmet]", $predmet['naziv']." (ETF)");
    $I->click("//input[@value=' Dodaj ']");
    //prava pristupa:
    $I->selectOption("(//select[@name='predmet'])[2]", $predmet['naziv']." (ETF)");
    $I->click("(//input[@value=' Dodaj '])[2]");
    Codeception\Util\Debug::debug(
            "Debug: predmet: ".$predmet['naziv']." ,nastavnik ime:".$nastavnik['ime']." ,prezime: ".$nastavnik['prezime']);

//            $I->adminNadjiPredmet($predmet['naziv']);
//            $I->selectOption("select[name=nivo_pristupa]", 'Nastavnik');
//            $I->click("//input[@value=' Postavi ']");
}

//10 studenata na svim predmetima
for($i=1;$i<=6;$i++){
    $faker = $I->getFaker();
    $usernamePassword = $I->getUsernameAndPass();
    $imePrezime = $I->getImePrezime();
    $I->adminDodajStudenta($imePrezime['ime'],$imePrezime['prezime'],$usernamePassword['login'],$usernamePassword['password']);
    $studenti[] = array(
        'ime'=>$imePrezime['ime'],
        'prezime'=>$imePrezime['prezime'],
        'login'=>$usernamePassword['login'],
        'password'=>$usernamePassword['password'],
        'index'=>$i,
    );
    $I->click('Upiši studenta na Prvu godinu studija, 1. semestar.');
    $I->click('input[name=novi_studij]');
    $I->click('input[name=nacin_studiranja]');
    $I->fillField("input[name=novi_brindexa]", $i);
    $I->click('input[type="submit"]');
    $I->fillField("(//input[@name='novi_brindexa'])[2]", $i);
    $I->click('input[type="submit"]');
    $I->canSee("proglašen za studenta");
    $I->canSee("broj indeksa postavljen na ".$i);
    Codeception\Util\Debug::debug(
            "Debug, student ime: ".$imePrezime['ime'].", prezime: ".$imePrezime['prezime'].", index: ".$i);
}
$I->logout();
$I->am('student');
$I->wantTo('login na zamger');
$I->lookForwardTo('vidim koje predmete imam');
$I->wantToTest('da li _before radi');
$student = $studenti[0];
//                'login' => $usernamePassword['login'],
//                'password'=>$usernamePassword['password'],
$I->login($student['login'],$student['password']);
$I->canSee($student['ime']." ".$student['prezime']);
foreach ($predmetiSemestar1 as $predmet) {
    $I->canSee($predmet['naziv']);
    $I->click($predmet['naziv']);
//    $ansa = $this->_dajPredmetniAnsambl($predmet['naziv']);
//    $nastavnik = $ansa['nastavnik'];
//    $I->canSee($nastavnik['ime']." ".$nastavnik['prezime']);
}
$I->logout();
