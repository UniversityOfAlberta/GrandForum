<?php

class PeopleManagedAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $people = array();
            if($me->isRoleAtLeast(ADMIN)){
                $people[$me->getReversedName()] = $me;
            }
            foreach($me->getRelations('all', true) as $type){
                foreach($type as $rel){
                    // Get the list of Related Users
                    $user = $rel->getUser2();
                    $people[$user->getReversedName().$user->getId()] = $user;
                }
            }
            foreach($me->getManagedPeople() as $person){
                $people[$person->getReversedName().$person->getId()] = $person;
            }
            ksort($people);
            $people = new Collection(array_values($people));
            return $people->toJSON();
        }
        else{
            $this->throwError("Could not retrieve list of managed users (Not logged in)");
        }
        return false;
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            DBFunctions::insert('grand_managed_people',
                                array('user_id' => $me->getId(),
                                      'managed_id' => $this->POST('id')));
            return json_encode(array('user_id' => $me->getId(),
                                     'managed_id' => $this->POST('id')));
        }
        else{
            $this->throwError("Not Logged In");
        }
        return false;
    }
    
    function doPUT(){
        return false;
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            DBFunctions::delete('grand_managed_people',
                                array('user_id' => $me->getId(),
                                      'managed_id' => $this->POST('id')));
            return false;
        }
        else{
            $this->throwError("Not Logged In");
        }
        return false;
    }

}

?>
