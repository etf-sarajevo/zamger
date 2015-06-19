<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->wantTo('Napraviti 10 predmeta za prvu godinu  i napraviti plan studija');
$I->fixturePredmetiZaGodinuJedanBsc();
$I->loginKaoAdmin();
$I->click("Studentska služba");
$I->click("Kreiranje plana studija");
$I->adminDodajStvakeNastavnogPlanaPrvaGodina();
$I->logout();