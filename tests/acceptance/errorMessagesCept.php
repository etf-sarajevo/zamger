<?php 
$scenario->group('bugs');
$I = new AcceptanceTester($scenario);
$I->wantTo('Da se ne prikazuju php debug upozorenja');
$I->amOnPage('/');
$I->dontSeeElement('.xdebug-error');
//$I->grabFromCurrentUrl();
//$I->amOnUrl($url);
//$I->executeInSelenium(function(\WebDriver $webdriver) {
//    $webdriver->findElement(WebDriverBy::xpath("//*[text()='Vaša sesija je istekla. Molimo prijavite se ponovo.']"));
//});
