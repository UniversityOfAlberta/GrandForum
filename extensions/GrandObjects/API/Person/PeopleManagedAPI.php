<?php

class PeopleManagedAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $people = array($me->getReversedName() => $me);
            if($me->isRoleAtLeast(STAFF)){
                $people = array_merge(Person::getAllPeople(), Person::getAllCandidates('all'));
                foreach($people as $person){
                    $people[$person->getReversedName()] = $person;
                }
            }
            else{
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
                    $members = $proj->getAllPeopleDuring(null, "0000-00-00 00:00:00", "2100-01-01 00:00:00");
                    foreach($members as $member){
                        $people[$member->getReversedName()] = $member;
                    }
                }
                foreach($me->getThemeProjects() as $proj){
                    // Get list of people on current theme lead projects
                    $members = $proj->getAllPeopleDuring(null, "0000-00-00 00:00:00", "2100-01-01 00:00:00");
                    foreach($members as $member){
                        $people[$member->getReversedName()] = $member;
                    }
                }
                foreach($me->getRequestedMembers() as $person){
                    $people[$person->getReversedName()] = $person;
                }
                // Handle Project Assistants and Project Support
                foreach($me->getProjects() as $project){
                    if($me->isRole(PA, $project) || $me->isRole(PS, $project)){
                        foreach($project->getAllPeople() as $person){
                            $people[$person->getReversedName()] = $person;
                        }
                    }
                }
                foreach($me->getManagedPeople() as $person){
                    $people[$person->getReversedName()] = $person;
                }
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
