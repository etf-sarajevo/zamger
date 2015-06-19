<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administraor");
$I->wantTo('Napraviti predmet koji svi slusaju');
$I->adminIskljuciPopUp();
$I->loginKaoAdmin();
$I->adminDodajPredmetKursSvima('Dummy predmet 0321', 'DP 0321', '2', '2', '2', '2', 
        '1', false);
$I->logout();
