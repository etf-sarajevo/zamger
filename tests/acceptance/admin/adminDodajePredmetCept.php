<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->loginKaoAdmin();
$I->wantTo('dodati novi predmet');
$I->adminDodajPredmet('dummyPredmet', 'DP01', '1', 
        '5', '0', '0');
$I->logout();