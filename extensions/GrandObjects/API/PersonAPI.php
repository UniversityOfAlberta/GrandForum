<?php

class PersonAPI extends RESTAPI {

    var $id;

    function processParams($params){
        foreach($params as $key => $param){
            if($key == 1){
                $this->id = $param;
            }
        }
    }

    function isLoginRequired(){
        return true;
    }
    
    function doGET(){
        $person = Person::newFromId($this->id);
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        return $person->toJSON();
    }
    
    function doPOST(){
        $person = Person::newFromId($this->id);
        header('Content-Type: application/json');
        $person->create();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->id);
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        header('Content-Type: application/json');
        $person->update();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->id);
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        header('Content-Type: application/json');
        $person->delete();
    }
	
}

?>
