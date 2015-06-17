<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am('Administrator');
$I->wantTo('Dodati predmet i ponuditi ga kao kurs za RI Bsc');
$I->loginKaoAdmin();
$I->adminDodajPredmetIPonuduKursaRIBsc('Dummy RI2', 
        'DPRI02', '2.5', 
        '10', '2', 
        '2', false, '1');
$I->logout();
