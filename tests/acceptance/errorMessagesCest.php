<?php
use \AcceptanceTester;

class errorMessagesCest
{
    private $loginStudent;
    private $passStudent;


    public function _before(AcceptanceTester $I){
        
    }

    public function _after(AcceptanceTester $I){
    }

    
    /**
     *  @actor AcceptanceTester\MemberSteps
     *  @group bugs
     */
    public function nemaPhpErroraNaLoginPage(AcceptanceTester $I,$scenario){
        $I->wantTo('Da se ne prikazuju php upozorenja na login page');
        $I->amOnPage('/');
        $I->dontSeeElement('.xdebug-error');
    }
    /**
     *  @actor AcceptanceTester\MemberSteps
     *  @group bugs
     *  @depends nemaPhpErroraNaLoginPage
     */
    public function nemaPhpErroraNaAdminHomePage(AcceptanceTester $I,$scenario) {        
        $I->am('admin');
        $I->wantTo('Da vidim da nema php upozorenja na admin home page');
        $I->amOnPage('/');
        $I->loginKaoAdmin();
        $I->dontSeeElement('.xdebug-error');
    }
    
    /**
     *  @actor AcceptanceTester\MemberSteps
     *  @group bugs
     *  @depends nemaPhpErroraNaLoginPage
     */
    public function nemaPhpErroraNaStudentHomePage(AcceptanceTester $I,$scenario) {        
        $I->am('student');
        $I->wantTo('Da vidim da nema php upozorenja na student home page');
        $I->amOnPage('/');
        $I->loginKaoStudent();
        $I->dontSeeElement('.xdebug-error');
    }
    
    
    

}