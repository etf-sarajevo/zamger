<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

//use vendor\sauce\sausage\src\Sauce\Sausage;

class AcceptanceHelper extends \Codeception\Module
{
    public function _initialize() {
        $tags = array(
            getenv('buildTags'),
            getenv('TRAVIS_BRANCH'),
            getenv('TRAVIS_COMMIT'),
            getenv('TRAVIS_REPO_SLUG'),
            getenv('TRAVIS_JOB_NUMBER'),
        );
        $wd = $this->getModule('WebDriverEHelper');
        $cap= array('tunnel-identifier'=>  getenv('sauceTunel'),
            'build'=>  getenv('buildName'),
            'platform'=>  getenv('OS'),
            'wait'=>  getenv('WEB_DRIVER_WAIT'),
            'captureHtml'=>true,//captureHtml
            'tags'=>  $tags);
        $wd->_reconfigure(array('host'=>getenv('SELENIUM_HOST'),
            'capabilities'=>$cap));
        //"recordScreenshots": false
    }
    
    public function _before(\Codeception\TestCase $test) {
        parent::_before($test);
//        exec("mysql -u root zamger < zamger-dump.sql");
    }
    
    public function _beforeStep(\Codeception\Step $step) {
        parent::_beforeStep($step);
//        $step->getHumanizedAction();
        
//        $debug->debug('hvatanje isteka sesije');
        $wd = $this->getModule('WebDriverEHelper');
//        $sesijaIma = true;
        $wd->executeInSelenium(function(\WebDriver $webdriver) {
           try{                              
               $webdriver->findElement(\WebDriverBy::xpath("//*[text()='Vaša sesija je istekla. Molimo prijavite se ponovo.']"));
               $debug = new \Codeception\Util\Debug("Before step ".$step->getName());
               $debug->debug('Istekla sesija');
               $this->_login($this->username, $this->pass, $webdriver);
           }  
           catch (\WebDriverException $e){
               $sesijaIma = true;
//               $this->gdje = $webdriver->gra
           } 
        });
    }
    
    private $username;
    private $pass;
    private $gdje;
    public function registrujLogin($username,$password) {
        $this->username = $username;
        $this->pass = $password;
    }
    
    private function _login($username,$password,$webDriver) {
        $webDriver->wantTo('login');
        $webDriver->amOnPage(\loginPage::$URL);
        $webDriver->canSee(\loginPage::$text);
        $webDriver->fillField(\loginPage::$username, $username);
        $webDriver->fillField(\loginPage::$pass, $password);
        $webDriver->click(\loginPage::$button);
    }
}
