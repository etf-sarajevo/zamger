<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->loginKaoAdmin();
$I->wantTo("Dodati novog osobu tipa studentska");
$I->adminDodajStudentskaOsoba('studentska', 'studentska');
$I->logout();