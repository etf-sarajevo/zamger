<?php
// Here you can initialize variables that will be available to your tests

\Codeception\Util\Autoload::registerSuffix('Steps', __DIR__.DIRECTORY_SEPARATOR.'_steps');
//
//seleniumHost => getenv('SELENIUM_HOST'); 
//\Codeception\Configuration::$defaultSuiteSettings['modules']['enabled'] = [
//    'WebDriver'
//];
//\Codeception\Configuration::$defaultSuiteSettings['modules']['config']=[
//    'WebDriver' => [
//        'url' => 'http://localhost/index.php',
//        'host' => getenv('SELENIUM_HOST'),
//        'port' => 80,
//        'browser' => 'chrome',
//        'window_size' => '1024x768',
//        'wait' => 10,
//        'restart' =>false
//    ]
//];

//\Codeception\Configuration::

//env:
//    sauce:
//        modules:
//            enabled:
//                - WebDriver
//            config:
//                WebDriver:
//                    url: 'http://localhost/zamger/'
//                    port: 80
//                    browser: chrome
//                    window_size: 1024x768
//                    wait: 10
//                    restart: false
//                    capabilities:
//                        unexpectedAlertBehaviour: 'accept'
//                        platform: 'Linux'