<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->wantTo('Napraviti 10 predmeta za prvu godinu');
$I->loginKaoAdmin();
$I->adminDodaj10PredmetaKursevaZaGodinu1();
$I->logout();