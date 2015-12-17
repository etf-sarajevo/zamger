<?php

class sveOsobePage
{
    // include url of current page
    public static $URL = '';
    
    public static $text = 'Studentska služba - Studenti i nastavnici';
    public static $prikaziSveOsobe = 'Prikaži sve osobe';
    
    public static $imeNoveOsobe = 'input[name=ime]';
    public static $prezimeNoveOsobe = 'input[name=prezime]';
    public static $dodajBtn = "Dodaj";
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