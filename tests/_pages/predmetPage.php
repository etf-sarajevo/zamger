<?php

class predmetPage
{
    // include url of current page
    public static $URL = '';
    
    public static $izmjenaPodatakaOPredmetu = array(
        'Sifra'=>'input[name=_lv_column_sifra]',
        'Naziv'=>'input[name=_lv_column_naziv]',
        'Institucija'=>'select[name=_lv_column_institucija]',
        'Kratki naziv'=>'input[name=_lv_column_kratki_naziv]',
        'Tippredmeta'=>'select[name=_lv_column_tippredmeta]',
        'Ects'=>'input[name=_lv_column_ects]',
        'Sati predavanja'=>'input[name=_lv_column_sati_predavanja]',
        'Sati tutorijala'=>'input[name=_lv_column_sati_tutorijala]',
        'Sati vjezbi'=>'input[name=_lv_column_sati_vjezbi]',
    );
    
    public static $izmjenaPodatakaOPredmetuInstitucije = array(
        'Nepoznato'=>'Nepoznato',
        'ETF'=>'Elektrotehnički fakultet Sarajevo',
        'AiE'=>'Odsjek za automatiku i elektroniku',
        'EE'=>'Odsjek za elektroenergetiku',
        'RI'=>'Odsjek za računarstvo i informatiku',
        'TK'=>'Odsjek za telekomunikacije',
    );
    
    public static $ponudaKursaStudij = array(
        'EEBsc'=>'Elektroenergetika (BSc)',
        'RIBsc'=>'Računarstvo i informatika (BSc)',
        'TKBsc'=>'Telekomunikacije (BSc)',
        'AiEBsc'=>'Automatika i elektronika (BSc)',
    );
    
    public static $izaberiNastavnika = array(
        'Nastavnik'=>'select[name=nastavnik]',
        'Dodaj'=>'Dodaj',
    );
            //'select[name=nastavnik]';
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