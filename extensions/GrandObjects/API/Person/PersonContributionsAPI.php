<?php

class PersonContributionsAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $array = array();
            $person = Person::newFromId($this->getParam('id'));
            if(!$me->isRoleAtLeast(MANAGER)){
                $this->throwError("You are not allowed to access this API");
            }
            $contributions = $person->getContributions();
            foreach($contributions as $contribution){
                $array[] = $contribution;
            }
            $array = new Collection(array_values($array));
            return $array->toJSON();
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
