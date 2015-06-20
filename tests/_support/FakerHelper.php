<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I


class FakerHelper extends \Codeception\Module
{
    private $faker;
    private $datumFormat = 'Y-m-d';
    
    
    public function getFaker() {
        if(!$this->faker){
            $faker = \Faker\Factory::create();
            $faker->addProvider(new \Faker\Provider\Address($faker));
            $faker->addProvider(new \Faker\Provider\Base($faker));
            $faker->addProvider(new \Faker\Provider\Person($faker));
            $faker->addProvider(new \Faker\Provider\DateTime($faker));
            $faker->addProvider(new \Faker\Provider\Lorem($faker));
            $faker->addProvider(new \Faker\Provider\File($faker));
            
            $this->faker = $faker;
        }
        
        return $this->faker;
    }
    
    
    public function getOsoba() {
        $osoba = array(
        //            'id', 
            'ime'=>  $this->getFaker()->firstName, 
            'prezime'=>$this->getFaker()->lastName,//'lastName', 
        //    'imeoca', 
        //    'prezimeoca', 
        //    'imemajke', 
        //    'prezimemajke', 
            'spol'=>'Z', 
        //    'brindexa', 
            'datum_rodjenja'=>  $this->getFaker()->dateTimeThisDecade->format($this->datumFormat),//null, 
            'mjesto_rodjenja'=>$this->getFaker()->numberBetween(1,7),//'numberBetween|1;17', //1-7
            'nacionalnost'=>$this->getFaker()->numberBetween(1,6),//'numberBetween|1;6', //1-6
            'drzavljanstvo'=>$this->getFaker()->numberBetween(1,25),//null, 1-25
            'boracke_kategorije'=>'0', 
        //    'jmbg',
            'adresa'=>$this->getFaker()->optional()->address, 
            'adresa_mjesto'=>$this->getFaker()->numberBetween(1,7),//null, //1-7
        //    'telefon', 
            'kanton'=>$this->getFaker()->numberBetween(1,13),//'numberBetween|1;13',
            'treba_brisati'=>0, 
            'fk_akademsko_zvanje'=>$this->getFaker()->numberBetween(1,8),//'numberBetween|1;8', //1-8
            'fk_naucni_stepen'=>6, //1,2 ili 6
            'slika' => $this->getFaker()->optional()->imageUrl(),//'optional:imageUrl|400;400', 
        //    'djevojacko_prezime', 
            'maternji_jezik'=>$this->getFaker()->numberBetween(1,18),//'numberBetween|1;18', //1-18
            'vozacka_dozvola'=>$this->getFaker()->numberBetween(1,13),//0, //1-13
            'nacin_stanovanja'=>$this->getFaker()->numberBetween(1,9),//'numberBetween|1;9',//1-9

        );
        return $osoba;
    }
}
