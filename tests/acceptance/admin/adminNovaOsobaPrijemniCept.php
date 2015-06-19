<?php 
$I = new AcceptanceTester\MemberSteps($scenario);
$I->am("Administrator");
$I->loginKaoAdmin();
$I->wantTo("Dodati novog osobu tipa prijemni ispit");
$I->adminDodajPrijemniOsoba('prijemni_ispit', 'prijemni_ispit');
$I->logout();