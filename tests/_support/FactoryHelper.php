<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use League\FactoryMuffin\Factory as FactoryMuffin;

class FactoryHelper extends \Codeception\Module
{
    protected $factory;
    
    public function _initialize() {
        $this->factory = new FactoryMuffin;
        $this->factory->setFakerLocale('en_EN');
        $this->_randomOsoba();
//        _randomOsoba();
    }
    
    public function getOsoba($num) {
        
        $this->factory->seed($num, 'Osoba');
    }
    
    public function _after(\Codeception\TestCase $test) {
        $this->factory->deleteSaved();
//        $this->factor
    }
    
    private function _randomOsoba() {
        $this->factory->define('Osoba',array(
    //            'id', 
        'ime'=>'firstName', 
        'prezime'=>'lastName', 
    //    'imeoca', 
    //    'prezimeoca', 
    //    'imemajke', 
    //    'prezimemajke', 
        'spol'=>'Z', 
    //    'brindexa', 
        'datum_rodjenja'=>null, 
        'mjesto_rodjenja'=>'numberBetween|1;17', //1-7
        'nacionalnost'=>'numberBetween|1;6', //1-6
        'drzavljanstvo'=>null, 
        'boracke_kategorije'=>'0', 
    //    'jmbg',
    //    'adresa', 
        'adresa_mjesto'=>null, 
    //    'telefon', 
        'kanton'=>'numberBetween|1;13',
        'treba_brisati'=>0, 
        'fk_akademsko_zvanje'=>'numberBetween|1;8', //1-8
        'fk_naucni_stepen'=>6, //1,2 ili 6
        'slika' => 'optional:imageUrl|400;400', 
    //    'djevojacko_prezime', 
        'maternji_jezik'=>'numberBetween|1;18', //1-18
        'vozacka_dozvola'=>0,
        'nacin_stanovanja'=>'numberBetween|1;9',//1-9
    )) ;


    }

}
