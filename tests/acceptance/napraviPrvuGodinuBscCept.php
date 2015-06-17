<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->wantTo('Napraviti 10 predmeta za prvu godinu');
$I->login('admin','admin');
$I->adminDodajPredmetaKursevaZaGodinuPrvu();
$I->logout();