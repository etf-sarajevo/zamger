<?php
//class AcceptanceHelper extends \Codeception\Module
class studentHomePage extends basePage
{
    // include url of current page
    public static $URL = '';
    public static $welcomeTextMale ='Dobro došao,';
    public static $welcomeTextFemale ='Dobro došla,';
    
    //sidebar
    public static $zahtjevZaOvjerenoUvjerenjeText = 'Zahtjev za ovjereno uvjerenje';
    public static $zahtjevZaOvjerenoUvjerenjeLink = '?sta=student/potvrda';
    
    //http://localhost/zamger/index.php?sta=student/potvrda
    
    public static $uvjerenjeOPolozenimPredmetimaText = 'Uvjerenje o položenim predmetima';
    
    public static $pregledOstvarenogRezultataText = 'Pregled ostvarenog rezultata';
    
    public static $ugovorOUcenjuText = 'Ugovor o učenju';
    
    public static $prijavaIspitaText = 'Prijava ispita';
    
    public static $promjenaOdsjekaText='';
    
    public static $zahtjevZaKolizijuText = 'Zahtjev za koliziju';
    
    public static $prosjeciPoGodinamaText = 'Prosjeci po godinama';
    
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