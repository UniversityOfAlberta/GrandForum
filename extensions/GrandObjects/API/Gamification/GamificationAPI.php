<?php

class GamificationAPI extends RESTAPI {
    
    function doGET(){
        $userId = $this->getParam('userId');
        if($userId == ""){
            $me = Person::newFromWgUser();
            $userId = $me->getId();
        }
        $person = Person::newFromId($userId);
        return Gamification::calculatePoints($person);
    }
    
    function doPOST(){
        return $this->doGet();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

?>
