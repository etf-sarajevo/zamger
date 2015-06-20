<?php 
$scenario->group('student');
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am('student');
$I->wantTo('login kao student');
$I->loginKaoStudent();
$I->canSee(studentHomePage::$porukeLinkText);
$I->logout();

