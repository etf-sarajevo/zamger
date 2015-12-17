<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->loginKaoAdmin();
$I->amOnPage('/');
$I->logout();