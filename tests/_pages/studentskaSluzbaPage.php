<?php

class studentskaSluzbaPage
{
    // include url of current page
    public static $URL = '';
    
    public static $navAnketaLink = 'Anketa';
    public static $navPocetnaLink = 'Početna';
    public static $navIzvjestajiLink = 'Izvještaji';
    public static $navKreirajPlanStudijaLink = 'Kreiranje plana studija';
    public static $navObavjestiLink = 'Obavijesti';
    public static $navOsobeLink = 'Osobe';
    public static $navPlanStudijaLink = 'Plan studija';
    public static $navPredmetiLink = 'Predmeti';
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