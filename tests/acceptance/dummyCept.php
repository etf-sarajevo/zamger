
<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->adminDodajPredmetIPonuduKursaSvimBsc('Inžinjerska matematika 2', 'PG 06', '7.5', 
        '52', '0', '28', true, '1');
