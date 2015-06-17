<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->loginKaoAdmin();
$I->adminDodajPredmetKursSvima('predmet23', "!r@#", 5, 
        5, 5, 
        5, 2, 2, true);
$I->logout();
