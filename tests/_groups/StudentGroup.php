<?php


use \Codeception\Event\TestEvent;
/**
 * Group class is Codeception Extension which is allowed to handle to all internal events.
 * This class itself can be used to listen events for test execution of one particular group.
 * It may be especially useful to create fixtures data, prepare server, etc.
 *
 * INSTALLATION:
 *
 * To use this group extension, include it to "extensions" option of global Codeception config.
 */

class StudentGroup extends \Codeception\Platform\Group
{
    public static $group = 'student';

    public function _initialize() {
        parent::_initialize();
        
    }
    
    public function _before(TestEvent $e){
        $this->writeln("_before student group");
        $this->writeln($e->getTest()->getName());
//        $faker = $this->getModule('FakerHelper');
//        $db = $this->getModule('Db');
//        
//        $student = $faker->getOsoba();
//        $id = $db->haveInDatabase('osoba',$student);
////        $db->haveInDatabase('auth',array(
////            'id'=>$id,
////            'login'=>'student',
////            'password'=>'student',
////            'aktivan'=>1,
////        ));
    }

    public function _after(TestEvent $e){
        $this->writeln("_after");
    }
}