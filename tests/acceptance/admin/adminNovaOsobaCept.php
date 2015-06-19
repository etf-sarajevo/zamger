<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->loginKaoAdmin();
$I->wantTo("Dodati novu osobu");
$I->adminDodajOsobu('osoba', 'osoba');
$I->logout();
