<?php

class HQPHandler extends PersonHandler {
        
    function HQPHandler($id){
        $this->PersonHandler($id);
    }
    
    static function init(){
        $personHandler = new HQPHandler('hqp');
    }
    
    function getArray(){
        $me = Person::newFromWgUser();
        $people = $me->getHQP(true);
        return $people;
    }
    
    function getArray2(){
        return Person::getAllPeopleDuring(HQP, SOT, EOT);
    }
}

?>
