<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->loginKaoAdmin();
$I->wantTo("Dodati novog osobu tipa siteadmin");
$I->adminDodajSiteadminOsoba('siteadmin', 'siteadmin');
$I->logout();