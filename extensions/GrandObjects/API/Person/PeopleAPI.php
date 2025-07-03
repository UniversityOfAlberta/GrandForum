<?php

class PeopleAPI extends RESTAPI {
    
    function doGET(){
        global $config;
        $simple = ($this->getParam('simple') != "");
        if($this->getParam('role') != ""){
            $university = "";
            $department = "";
            if($this->getParam('university') != ""){
                $university = $this->getParam('university');
            }
            if($this->getParam('department') != ""){
                $department = $this->getParam('department');
            }
            
            $exploded = explode(",", $this->getParam('role'));
            $finalPeople = array();
            foreach($exploded as $role){
                $role = trim($role);
                if($config->getValue('networkType') == "CFREF"){
                    if($role == "Former-".HQP){
                        $role = "Alumni";
                    }
                }
                $alumni = false;
                if($role == "Alumni"){
                    $role = "Former-".HQP;
                    $alumni = true;
                }
                if($role == 'all'){
                    // Get All people (including candidates)
                    $people = array_merge(Person::getAllPeople(), Person::getAllCandidates('all'));
                }
                else if($role == 'allexceptstaff'){
                    $people = array();
                    foreach(array_merge(Person::getAllPeople(), Person::getAllCandidates('all')) as $person){
                        if(!$person->isRoleAtLeast(STAFF)){
                            $people[] = $person;
                        }
                    }
                }
                else{
                    // Get the specific role
                    if(strstr($role, "Former-") !== false){
                        $people = Person::getAllPeopleDuring(str_replace("Former-", "", $role), "0000-00-00", date('Y-m-d'));
                    }
                    else{
                        $people = Person::getAllPeople($role);
                    }
                }
                foreach($people as $key => $person){
                    if(strstr($role, "Former-") !== false && $person->isRole(str_replace("Former-", "", $role))){
                        // Person is still the specified role, don't show on the 'former' table
                        unset($people[$key]);
                    }
                }
                if($alumni){
                    $allAlumni = Alumni::getAllAlumni();
                    foreach($allAlumni as $alum){
                        $person = $alum->getPerson();
                        if($person->isRoleDuring(HQP, "0000-00-00", date('Y-m-d'))){
                            $people[] = $person;
                        }
                    }
                }
                foreach($people as $person){
                    if($university == "" && $department == ""){
                        $finalPeople[$person->getReversedName()] = $person;
                    }
                    else {
                        $unis = $person->getCurrentUniversities();
                        foreach($unis as $uni){
                            if($uni['university'] == $university){
                                if($department == "" || $department == $uni['department']){
                                    $finalPeople[$person->getReversedName()] = $person;
                                }
                            }
                        }
                    }
                }
            }
            ksort($finalPeople);
            $finalPeople = new Collection(array_values($finalPeople));
            if($simple){
                return $finalPeople->toSimpleJSON();
            }
            return $finalPeople->toJSON();
        }
        else{
            $people = new Collection(Person::getAllPeople('all'));
            if($simple){
                return $people->toSimpleJSON();
            }
            return $people->toJSON();
        }
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
