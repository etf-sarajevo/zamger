<?php

//require_once 'vendor/fzaninotto/faker/src/autoload.php';

namespace AcceptanceTester;

//$faker = Faker\Factory::create();

class fixturesStudentSteps extends \AcceptanceTester
{    
    public function randomNOsoba($broj = 36) {
//        $I = $this;
//        $osobe = array();
////        $maxIdTrenutni = $I->grabFromDatabase('osoba');
//        for($i=1;$i<=$broj;$i++){
//            $osoba = array(
//                'ime'=>'test',
//                'prezime'=>'test',
////                'spol'=>'',
//                'fk_akademsko_zvanje'=>5,
//                'fk_naucni_stepen'=>6,
//            );
//            array_push($osobe, $osoba);
//        } 
//        $I->haveInDatabase('osoba',$osobe);
    }
    public function randomNStudenata($broj = 30)
    {
        $I = $this;
        $studenti = array();
        for($i=1;$i<=$broj;$i++){
            $student = array(
                'ime'=>'',
                'prezime'=>'',
                'spol'=>'',
            );
        }        
    }    
    public function randomNNastavnika($broj = 6)
    {
        $I = $this;
    }    
    public function randomNPredmeta($broj = 6)
    {
        $I = $this;
    }    
    public function upisiStudentaNaPredmet()
    {
        $I = $this;
    }    
    public function upisiNastavnikaNaPredmet()
    {
        $I = $this;
    }
}