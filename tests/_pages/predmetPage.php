<?php

class predmetPage
{
    // include url of current page
    public static $URL = '';
    
    public static $detalji = array(
        'Sifra'=>'input[name=_lv_column_sifra]',
        'Naziv'=>'input[name=_lv_column_naziv]',
        'Institucija'=>'select[name=_lv_column_institucija]',
        'Kratki naziv'=>'input[name=_lv_column_kratki_naziv]',
        'Tippredmeta'=>'select[name=_lv_column_tippredmeta]',
        'Ects'=>'input[name=_lv_column_ects]',
        'Sati predavanja'=>'input[name=_lv_column_sati_predavanja]',
        'Sati tutorijala'=>'input[name=_lv_column_sati_tutorijala]',
        'Pošalji',
        'Poništi',
        'Nazad',
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