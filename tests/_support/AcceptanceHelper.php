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
        exec("mysql -u root zamger < zamger-dump.sql");
    }
    
}
