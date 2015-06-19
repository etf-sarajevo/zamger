<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administraor");
$I->wantTo('Napraviti predmet koji svi slusaju');
$I->adminIskljuciPopUp();
$I->loginKaoAdmin();
$I->adminDodajPredmetKursSvima('Dummy Predmet', 'DP 0321', 2, 2, 
        5, 7, predmetPage::$ponudaKursaStudij['EEBsc'], "1", true);
$I->logout();
