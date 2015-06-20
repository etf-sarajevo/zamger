<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->wantTo('perform actions and see result');
$I->loginKaoAdmin();
$te = $I->getOsoba();
$I->haveInDatabase('osoba', $te);
$I->click(adminHomePage::$studentskaSluzbaLink);
$I->click(studentskaSluzbaPage::$navOsobeLink);
$I->click(sveOsobePage::$prikaziSveOsobe);
$I->fillField(sveOsobePage::$imeNoveOsobe, $I->getFaker()->firstName);
$I->fillField(sveOsobePage::$prezimeNoveOsobe, $I->getFaker()->lastName);
$I->click(sveOsobePage::$dodajBtn);
$I->logout();
