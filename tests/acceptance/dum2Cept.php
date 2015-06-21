<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->wantTo('Nastavni plan random predmeta');
//$I->fixturePredmetiZaGodinuJedanBsc();
$I->loginKaoAdmin();
$I->amOnPage('/');
$I->click("Studentska služba");
$I->click("Kreiranje plana studija");
$I->adminNapraviNastavniPlanRandomPredmeta();
$I->logout();
