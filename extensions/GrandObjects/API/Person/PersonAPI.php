<?php

class PersonAPI extends RESTAPI {
    
    function doGET(){
        global $config;
        $me = Person::newFromWgUser();
        if($this->getParam('current') != ""){
            if($me->isLoggedIn()){
                return $me->toJSON();
            }
            else{
                $this->throwError("You are not logged in");
            }
        }
        else if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) == 1){
            $person = Person::newFromId($this->getParam('id'));
            if($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI) && !$config->getValue('hqpIsPublic'))){
                $this->throwError("This user does not exist");
            }
            return $person->toJSON();
        }
        else if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) > 1){
            $json = array();
            foreach(explode(",", $this->getParam('id')) as $id){
                $person = Person::newFromId($id);
                if(!($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI) && !$config->getValue('hqpIsPublic')))){
                    $json[] = $person->toArray();
                }
            }
            return large_json_encode($json);
        }
    }
    
    function doPOST(){
        $person = new Person(array());
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->gender = $this->POST('gender');
        $person->birthDate = $this->POST('birthDate');
        $person->indigenousStatus = $this->POST('indigenousStatus');
        $person->minorityStatus = $this->POST('minorityStatus');
        $person->disabilityStatus = $this->POST('disabilityStatus');
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
        $person->birthDate = $this->POST('birthDate');
        $person->indigenousStatus = $this->POST('indigenousStatus');
        $person->minorityStatus = $this->POST('minorityStatus');
        $person->disabilityStatus = $this->POST('disabilityStatus');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        $person->candidate = $this->POST('candidate');
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
        return $person->toJSON();
    }
}

?>
