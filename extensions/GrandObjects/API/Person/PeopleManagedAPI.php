<?php

class PeopleManagedAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $people = array();
            if($me->isRoleAtLeast(ADMIN)){
                $people[$me->getReversedName()] = $me;
            }
            foreach($me->getRelations(SUPERVISES, true) as $rel){
                // Get the list of Supervises
                $hqp = $rel->getUser2();
                $people[$hqp->getReversedName()] = $hqp;
            }
            foreach($me->getRelations(WORKS_WITH, true) as $rel){
                // Get list of Works With
                $user = $rel->getUser2();
                $people[$user->getReversedName()] = $user;
            }
            foreach($me->leadership() as $proj){
                // Get list of people on current lead projects
                $members = $proj->getAllPeopleDuring('all', "0000-00-00 00:00:00", "2100-01-01 00:00:00");
                foreach($members as $member){
                    $people[$member->getReversedName()] = $member;
                }
            }
            foreach($me->getManagedPeople() as $person){
                $people[$person->getReversedName()] = $person;
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
        return false;
    }

}

?>
