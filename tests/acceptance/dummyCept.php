<?php 
$I = new AcceptanceTester($scenario);
//$I = new AcceptanceTester\fixturesStudentSteps($scenario);
$I->wantTo('test vezu sa bazom');
////$osobe = array();
////        $maxIdTrenutni = $I->grabFromDatabase('osoba');
////        for($i=1;$i<=$broj;$i++){
//            $osoba = array(
//                'ime'=>'test',
//                'prezime'=>'test',
////                'spol'=>'',
//                'fk_akademsko_zvanje'=>5,
//                'fk_naucni_stepen'=>6,
//            );
////            array_push($osobe, $osoba);
////        } 
$I->haveInDatabase('osoba',array(
    'id'=>'99',
    'ime'=>'ime',
    'prezime'=>'prezime',
));
