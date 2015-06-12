<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

//use vendor\sauce\sausage\src\Sauce\Sausage;

class AcceptanceHelper extends \Codeception\Module
{
    protected $sauceApi;
    
//    public function &_sauceApi (){
//        if (!($this->sauceApi instanceof \Sauce\Sausage\SauceAPI)) {
//            $this->sauceApi = new \Sauce\Sausage\SauceAPI($this->config['username'], $this->config['accesskey']);
//        }
//        return $this->sauceApi;
//    }

    public function _initialize() {
        $wd = $this->getModule('WebDriver');
        $cap= array('tunnel-identifier'=>  getenv('sauceTunel'),
            'build'=>  getenv('buildName'),
            'tags'=>  getenv('buildTags'));
        $wd->_reconfigure(array('host'=>getenv('SELENIUM_HOST'),
            'capabilities'=>$cap));
        
    }
    
     // HOOK: before each suite
    public function _beforeSuite($settings = array()) {
    }

    // HOOK: after suite
    public function _afterSuite() {
    }    

    // HOOK: before each step
    public function _beforeStep(\Codeception\Step $step) {
    }

    // HOOK: after each step
    public function _afterStep(\Codeception\Step $step) {
    }

    // HOOK: before test
    public function _before(\Codeception\TestCase $test) {
//        $ime = $test->getName();
//        $wd = $this->getModule('WebDriver');
//        $cap= array('name'=>  $ime);
//        $wd->_reconfigure(array(
//            'capabilities'=>$cap));
    }

    // HOOK: after test
    public function _after(\Codeception\TestCase $test) {
    }

    // HOOK: on fail
    public function _failed(\Codeception\TestCase $test, $fail) {
        //$ime = $test->getName();
        $wd = $this->getModule('WebDriver');
        $wd->_reconfigure(array(
            'passed'=>false));
    }      
    
}
