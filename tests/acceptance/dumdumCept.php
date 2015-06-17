<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->loginKaoAdmin();
$I->adminDodajPredmetKursSvima('predmet2', "!@#", 5, 
        5, 5, 
        5, 2, 2, true);
$I->logout();
