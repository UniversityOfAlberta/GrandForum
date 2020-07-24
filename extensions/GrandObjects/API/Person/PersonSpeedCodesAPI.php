<?php

class PersonSpeedCodesAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        return json_encode($person->getSpeedCodes());
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
