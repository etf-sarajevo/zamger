<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see result');
$I->wantTo('login kao administrator');
$I->amOnPage('/');
$I->fillField('login', 'admin');
$I->fillField('pass', 'admin');
$I->click("Kreni");
$I->see('Administracija sajta');
?>