<?php

class SimilarPeopleAPI extends RESTAPI {
    
    function doGET(){
        global $config;
        $me = Person::newFromWgUser();
        if($this->getParam('id') != ""){
            $person = Person::newFromId($this->getParam('id'));
            if($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI) && !$config->getValue('hqpIsPublic'))){
                $this->throwError("This user does not exist");
            }
            $people = new Collection($person->getSimilarPeople());
            return $people->toSimpleJSON();
        }
    }
    
    function doPOST(){
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doGET();
    }
    
    function doDELETE(){
        return $this->doGET();
    }
}

?>
