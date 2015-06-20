<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I


class FakerHelper extends \Codeception\Module
{
    private $faker;
    private $datumFormat = 'Y-m-d';
    private $spol = array('M','Z');
    private $odnosEctsPredavanja =12;
    
    private function randomSpol() {
        $k = array_rand($this->spol);
        return $this->spol[$k];
    }
    
    public function getFaker() {
        if(!$this->faker){
            $faker = \Faker\Factory::create();
            $faker->addProvider(new \Faker\Provider\Address($faker));
            $faker->addProvider(new \Faker\Provider\Base($faker));
            $faker->addProvider(new \Faker\Provider\Person($faker));
            $faker->addProvider(new \Faker\Provider\DateTime($faker));
            $faker->addProvider(new \Faker\Provider\Lorem($faker));
            $faker->addProvider(new \Faker\Provider\File($faker));
            $faker->addProvider(new \Faker\Provider\Internet($faker));
            
            $this->faker = $faker;
        }
        
        return $this->faker;
    }
    
    public function getUsernameAndPass() {
        return array(
            'login'=>  $this->getFaker()->userName,
            'password'=>$this->getFaker()->password,
        );
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
            'spol'=>  $this->randomSpol(), 
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
            'djevojacko_prezime'=>  $this->getFaker()->optional()->lastName, 
            'maternji_jezik'=>$this->getFaker()->numberBetween(1,18),//'numberBetween|1;18', //1-18
            'vozacka_dozvola'=>$this->getFaker()->numberBetween(1,13),//0, //1-13
            'nacin_stanovanja'=>$this->getFaker()->numberBetween(1,9),//'numberBetween|1;9',//1-9

        );
        return $osoba;
    }
    
    private function kratkiNaziv($naziv){
        $words = explode(" ", $naziv);
        $kratki = "";
        foreach ($words as $w) {
            $kratki.=$words[0];
        }
        return $kratki;
    }
    
    public function getPredmet() {
        /*$I->haveInDatabase('predmet',array('id'=>'1', 'sifra'=>'PG01', 'naziv'=>'Inžinjerska matematika 1', 
         * 'institucija'=>'1', 
        'kratki_naziv'=>'IM1', 'tippredmeta'=>'1', 'ects'=>'6.5', 'sati_predavanja'=>'49',
        'sati_vjezbi'=>'0', 'sati_tutorijala'=>'26',));*/
        $naziv = $this->getFaker()->sentence($faker->numberBetween(1, 3));
        $kratki = $this->kratkiNaziv($naziv);
        $ects = $this->getFaker()->numberBetween(4, 30);
        $sati=  $this->odnosEctsPredavanja*$ects;
        $sati_predavanja = $this->getFaker()->numberBetween(0,$sati);
        $sati_vjezbi = $this->getFaker()->numberBetween(0,$sati-$sati_predavanja);
        $sati_tutorijala = $sati - $sati_predavanja - $sati_vjezbi;
        $predmet = array(
            'sifra',
            'naziv'=>  $naziv,
            'institucija'=>'1',
            'kratki_naziv'=> $kratki,
            'tippredmeta'=>'1',
            'ects'=> $ects ,
            'sati_predavanja'=>  $sati_predavanja,
            'sati_vjezbi'=>$sati_vjezbi,
            'sati_tutorijala'=>$sati_tutorijala,
        );
        return $predmet;
    }
    
    public function getSemestarPredmeta(){
        $maxEcts = 30;
        $faker = $this->getFaker();
        $br = $faker->numerify('###');
        $sifraSlova = strtoupper($faker->unique()->text(5));
        $niz = array();
//        $nizObavezno = array();
        do{
            $ects = $faker->numberBetween(0, $maxEcts);
            $maxEcts-=$ects;
//            $obavezan = $faker->boolean(80);
            $ukupno = $this->odnosEctsPredavanja*$ects;
            $predavanja = $faker->numberBetween(0, $ukupno);
            $vjezbe = $faker->numberBetween(0, $ukupno-$predavanja);
            $tut = $ukupno - $predavanja - $vjezbe;
            $naziv = $faker->sentence($faker->numberBetween(1, 3)); //words($faker->numberBetween(1, 3), true);
            $kratki = $this->kratkiNaziv($naziv);
            $sifra = $sifraSlova." ".$kratki." ".$br;
            
            $predmet = array(
                'sifra'=>$sifra,
                'naziv'=>$naziv,
                'institucija'=>'1',
                'kratki_naziv'=> $kratki,
                'tippredmeta'=>'1',
                'ects'=> $ects ,
                'sati_predavanja'=>  $predavanja,
                'sati_vjezbi'=>$vjezbe,
                'sati_tutorijala'=>$tut,
            );
            $niz[] = $predmet;
//            $nizObavezno[] = $obavezan;
        }while($maxEcts>0);
        
        return $niz;
    }
}
