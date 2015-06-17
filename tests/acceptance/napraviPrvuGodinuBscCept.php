<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->wantTo('Napraviti 10 predmeta za prvu godinu');
$I->loginKaoAdmin();
$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Inžinjerska matematika 1', 'PG01', '6.5', 49, 
    0, 26, true, 1);
$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Inžinjerska matematika 2', 'PG06', '7.5', 52, 
    0, 28, true, 2);

$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Inženjerska fizika 1', 'PG03', '5.0', 39, 
    0, 21, true, 1);
$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Inženjerska fizika 2', 'PG08', '5.0', 39, 
    0, 21, true, 2);

$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Osnove elektrotehnike', 'PG02', '7.5', 48, 
    4, 28, true, 1);
$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Električni krugovi 1', 'PG07', '6.5', 45, 
    10, 20, true, 2);

$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Osnove računarstva', 'PG05', '6', 44, 
    26, 0, true, 1);
$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Tehnike programiranja', 'PG09', '6', 44, 
    26, 0, true, 2);

$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Linearna algebra i geometrija', 'PG04', '5', 39, 
    0, 21, true, 1);
$I->adminDodajPredmetIPonuduKursaSvimBsc
    ('Elektronički elementi i sklopovi', 'PG10', '5', 39, 
    0, 21, true, 2);
