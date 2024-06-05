<?php

class PeopleAPI extends RESTAPI {
    
    function doGET(){
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
                if($role == 'all'){
                    // Get All people
                    $people = array_merge(Person::getAllPeople());
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
                foreach($people as $person){
                    if(strstr($role, "Former-") !== false && $person->isRole(str_replace("Former-", "", $role))){
                        // Person is still the specified role, don't show on the 'former' table
                        continue;
                    }
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
            if($simple){
                $peopleIds = Person::getAllPeople('all', true);
                $people = array();
                $data = DBFunctions::select(array('mw_user'),
                                            array('user_id', 
                                                  'user_name',
                                                  'user_email',
                                                  'user_real_name',
                                                  'first_name',
                                                  'last_name',
                                                  'middle_name'),
                                            array('user_id' => IN($peopleIds)));
                foreach($data as $row){
                    $person = new LimitedPerson(array());
                    $person->id = $row['user_id'];
                    $person->name = $row['user_name'];
                    $person->realname = $row['user_real_name'];
                    $person->firstName = $row['first_name'];
                    $person->lastName = $row['last_name'];
                    $person->middleName = $row['middle_name'];
                    $person->email = $row['user_email'];
                    $people[strtolower($row['user_name'])] = $person;
                }
                ksort($people);
                $json = array();
                foreach($people as $person){
                    $json[] = $person->toSimpleArray();
                }
                return json_encode($json);
            }
            else{
                $people = new Collection(Person::getAllPeople('all'));
                return $people->toJSON();
            }
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
