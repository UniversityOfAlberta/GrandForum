<?php

class PersonAPI extends RESTAPI {
    
    function doGET(){
	$me = Person::newFromWgUser();
	if(!$me->isLoggedIn()){
	    $this->throwError("You are not logged in");
	}
        if($this->getParam('id') != ""){
            $person = Person::newFromId($this->getParam('id'));
            if($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI))){
                $this->throwError("This user does not exist");
            }
            return $person->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You are not logged in");
        }
        $person = new Person(array());
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->gender = $this->POST('gender');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        if($person->exists()){
            $this->throwError("A user by the name of <i>{$person->getName()}</i> already exists");
        }
        $status = $person->create();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be created");
        }
        $person = Person::newFromName($person->getName());
        return $person->toJSON();
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You are not logged in");

        }
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $person->name = $this->POST('name');
        $person->realname = $this->POST('realName');
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->gender = $this->POST('gender');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        $status = $person->update();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be updated");
        }
        $person = Person::newFromId($this->getParam('id'));
        return $person->toJSON();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $status = $person->delete();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be deleted");
        }
    }
}

?>
