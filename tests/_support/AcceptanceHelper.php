<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

//use vendor\sauce\sausage\src\Sauce\Sausage;

class AcceptanceHelper extends \Codeception\Module
{

    public function _initialize() {
        $wd = $this->getModule('WebDriverEHelper');
        $cap= array('tunnel-identifier'=>  getenv('sauceTunel'),
            'build'=>  getenv('buildName'),
            'tags'=>  getenv('buildTags'));
        $wd->_reconfigure(array('host'=>getenv('SELENIUM_HOST'),
            'capabilities'=>$cap));
        //"recordScreenshots": false
    }
    
}
