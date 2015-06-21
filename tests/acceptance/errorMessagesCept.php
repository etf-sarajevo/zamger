<?php 
$scenario->group('bugs');
$I = new AcceptanceTester($scenario);
$I->wantTo('Da se ne prikazuju php debug upozorenja');
$I->amOnPage('/');
$I->dontSeeElement('.xdebug-error');
