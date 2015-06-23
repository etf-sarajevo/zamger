<?php

use Codeception\Util\Locator as Locatorr;

class adminParametriStudijaPage
{
    // include url of current page
    public static $URL = '';
    
    public static $navAkademskaGodinaLink = 'Akademska godina';
    public static $navInstitucijaLink = 'Institucija';
    public static $navKantonLink = 'Kanton';
    public static $navKomponentaOcjeneLink = 'Komponenta ocjene';
    public static $navStudijLink = 'Studij';
    public static $navTipoviPredmetaLink = 'Tipovi predmeta';
    
    public static $akademskaGodinaNazivPoljeNova = 'input[name=_lv_column_naziv][value=""]';
//    public static $akademskaGodinaAktuelnaCheckbox = 
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