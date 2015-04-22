<?php

class PeopleManagedAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $people = array();
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
            // TODO: Also need to include extra people
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
