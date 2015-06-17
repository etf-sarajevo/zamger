<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->loginKaoAdmin();
$I->click("Studentska služba");
$I->click("Kreiranje plana studija");
$I->see("Dodavanje stavke nastavnog plana");
$I->selectOption("select[id=studij]", "4.Telekomunikacije (BSc)");
$I->fillField("input[name=semestar]", 7);
$k = 1;
do {
    $I->selectOption("select[id=predmet]", $k);
    $temp = $I->grabValueFrom();
            //"select[id=predmet]");
    $I->canSeeElementInDOM($selector);
//    $I->gra
    $tempString = explode(".", $temp);
    if($tempString[1]=="dummyPredmet"){    
        break;        
    }
    $k++;
}while(true);

$I->click('input[id=submitbutton]');
$I->logout();
