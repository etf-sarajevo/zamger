<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class WebDriverEHelper extends \Codeception\Module\WebDriver
{
//    public function _after(\Codeception\TestCase $test)
//    {
//        if ($this->config['restart'] && isset($this->webDriver)) {
//            $this->webDriver->quit();
//            // \RemoteWebDriver consists of four parts, executor, mouse, keyboard and touch, quit only set executor to null,
//            // but \RemoteWebDriver doesn't provide public access to check on executor
//            // so we need to unset $this->webDriver here to shut it down completely
////            $this->webDriver = null;
//        }
//        if ($this->config['clear_cookies'] && isset($this->webDriver)) {
//            $this->webDriver->manage()->deleteAllCookies();
//        }
//    }
//    public function _after(\Codeception\TestCase $test) {
//        if ($this->config['restart'] && isset($this->webDriver)) {
//            $this->webDriver->quit();
//            // \RemoteWebDriver consists of four parts, executor, mouse, keyboard and touch, quit only set executor to null,
//            // but \RemoteWebDriver doesn't provide public access to check on executor
//            // so we need to unset $this->webDriver here to shut it down completely
//            $this->webDriver = null;
//        }
//        if ($this->config['clear_cookies'] && isset($this->webDriver)) {
//            $this->webDriver->manage()->deleteAllCookies();
//        }
//    }
//    public function _failed(\Codeception\TestCase $test, $fail)
//    {
////        $filename = str_replace(['::', '\\', '/'],['.', '', ''], \Codeception\TestCase::getTestSignature($test)) . '.fail';
////        $this->_saveScreenshot(codecept_output_dir().$filename.'.png');
////        file_put_contents(codecept_output_dir().$filename.'.html', $this->webDriver->getPageSource());
////        $this->debug("Screenshot and HTML snapshot were saved into '_output' dir");
//        $this->debug('_failed');
//    }
    
//    public function _saveScreenshot($filename)
//    {
//        $this->webDriver->takeScreenshot($filename);
//    }
}
