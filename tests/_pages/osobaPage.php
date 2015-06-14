<?php

class osobaPage
{
    // include url of current page
    public static $URL = '';
    public static $ime = 'Ime: ';
    public static $prezime = 'Prezime: ';
    public static $brojIndexa = 'Broj indexa (za studente): ';
    public static $JMBG = 'JMBG: ';
    public static $adresa = 'Adresa: ';
    public static $kanton = '';
    
    public static $izmjeniButton = 'input[type=submit,value= Izmijeni ]';
//    public static $button = 'input[type=submit]';
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