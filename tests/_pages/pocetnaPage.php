<?php

class pocetnaPage
{
    // include url of current page
    public static $URL = '';
    public static $username = 'input[name=login]';
    public static $pass = 'input[name=pass]';
    public static $button = 'input[type=submit]';
    
    public static $homeTextAdmin = 'Administracija sajta';
    

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: EditPage::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }


}
