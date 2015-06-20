<?php 
$scenario->group('admin');
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->loginKaoAdmin();
$I->wantTo("Dodati novog studenta");
$I->adminDodajStudenta('student', 'student');
$I->logout();