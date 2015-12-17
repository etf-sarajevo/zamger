<?php

class adminHomePage
{
    // include url of current page @todo adminhomepage url dodati
    public static $URL = '';//?sta=admin/intro';
//    public static $cronLink = '?sta=admin/cron';
//    public static $administracijaPredmetaLink = '';
//    public static $kompaktovanjeBazeLink = '';
//    public static $pregledLogovaLink = '';
//    public static $novaAkademskaGodinaLink = '';
//    public static $alatiZaPrijemiLink='';
//    public static $parametriStudijaLink = '';
//    public static $studentskaStranicaLink = '';
//    
    
    
    public static $spisakPredmetaIGrupaLink='Spisak predmeta i grupa';
    public static $studentskaSluzbaLink = 'Studentska služba';
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