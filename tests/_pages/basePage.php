<?php

class basePage
{
    // include url of current page
    public static $URL = '';//@todo basepage url dodati
    public static $uputstvaLinkText = 'Uputstva';
    public static $prijaviteBugLinkText = 'Prijavite bug ';
    
    public static $porukeLinkText ='Poruke';
    public static $profilLinkText ='Profil';
    public static $profilLinkText ='Odjava';
//    public static $homeLink = '';
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