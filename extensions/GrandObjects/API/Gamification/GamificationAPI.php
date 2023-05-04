<?php

class GamificationAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        $userId = $this->getParam('userId');
        if($userId == ""){
            $userId = $me->getId();
        }
        if(!$me->isLoggedIn() || ($me->getId() != $userId && !$me->isRoleAtLeast(STAFF))){
            $this->throwError("You are not allowed to access this api");
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
