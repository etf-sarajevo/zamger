<?php

class stavkaNastavnogPlanaPage
{
    // include url of current page
    public static $URL = '?sta=studentska/kreiranje_plana';
    public static $text = "Dodavanje stavke nastavnog plana";
    public static $studij = 'select[name=studij]';
    public static $studijOptions = array(
        'TKBsc'=>'4.Telekomunikacije (BSc)',
        'RIBsc'=>'1.Računarstvo i informatika (BSc)',
        'AiEBsc'=>'2.Automatika i elektronika (BSc)',
        'EEBsc'=>'3.Elektroenergetika (BSc)',
    );
    public static $semestar = 'input[name=semestar]';
    public static $predmet = 'select[name=predmet]';
    public static $predmetID = 'predmet';
    
    public static $jeObavezan = 'input[name=obavezan]';
    public static $potvrdi = 'input[name=create]';
    
    public static $uspjesnoText = 'Uspješno ste kreirali stavku plana studija.';
    public static $uspjesnoUrlNazad = 'Nazad na kreiranje nove stavke plana';


//    public static function predmetOpcije($predmet) {
//        return '1.dumdum';
//    }
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