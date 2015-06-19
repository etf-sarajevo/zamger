<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->loginKaoAdmin();
$I->wantTo("Dodati novog nastavnika");
$I->adminDodajNastavnika('nastavnik', 'nastavnik');
$I->logout();