<?php 

use Codeception\Util\Locator;
use Codeception\Util\Debug;


$I = new AcceptanceTester\MemberSteps($scenario);
//$I->haveInDatabase('predmet', 
//        array('id' => '1', 
//            'naziv' => 'miles@davis.com'));
//$I->loginKaoAdmin();
//$I->click("Studentska služba");
//$I->click("Kreiranje plana studija");
//$I->see(stavkaNastavnogPlanaPage::$text);
//$I->selectOption(stavkaNastavnogPlanaPage::$studij, stavkaNastavnogPlanaPage::$studijOptions['AiEBsc']);
//$I->fillField(stavkaNastavnogPlanaPage::$semestar, 6);
//
//$I->checkOption(stavkaNastavnogPlanaPage::$jeObavezan);
//$opt = "//option[contains(text(),normalize-space('mn'))]";
////$select = null;
//$I->executeInSelenium(function(RemoteWebDriver $webDriver) {
//    $select = $webDriver->findElement(WebDriverBy::id(stavkaNastavnogPlanaPage::$predmetID));
//    $select->click();
//    $option = $select->findElement(WebDriverBy::xpath("//option[contains(text(),normalize-space('mn'))]"));
//    $option->click();
//    $coo = $option->getCoordinates();
//    $webDriver->getMouse()->doubleClick($coo);
//    
//});
////$I->click($opt);
//
//
////$I->selectOption(stavkaNastavnogPlanaPage::$predmet, $opt);
//
//$I->click('input[id=submitbutton]');
//$I->logout();
