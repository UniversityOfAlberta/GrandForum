<?php

class PersonUniversitiesAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $universities = $person->getPersonUniversities();
        if($this->getParam('personUniversityId') != ""){
            // Single University
            foreach($universities as $university){
                if($university['id'] == $this->getParam('personUniversityId')){
                    return json_encode($university);
                }
            }
        }
        else{
            // All Universities
            $newUniversities = array();
            foreach($universities as $uni){
                if($uni['endDate'] == '0000-00-00 00:00:00'){
                    // Till the end of time
                    $newUniversities[EOT.' 00:00:00_'.$uni['startDate'].'_'.$uni['id']] = $uni;
                }
                else{
                    $newUniversities[$uni['endDate'].'_'.$uni['startDate'].'_'.$uni['id']] = $uni;
                }
            }
            ksort($newUniversities);
            $newUniversities = array_reverse($newUniversities);
            $universities = array_values($newUniversities);
            return json_encode($universities);
        }
    }
    
    function doPOST(){
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        
        $uniCheck = DBFunctions::execSQL("SELECT * FROM grand_universities
                                          WHERE university_name = BINARY '".DBFunctions::escape($this->POST('university'))."'");
        $posCheck = DBFunctions::execSQL("SELECT * FROM grand_positions
                                          WHERE position = BINARY '".DBFunctions::escape($this->POST('position'))."'");
        
        if(count($uniCheck) == 0){
            // Create new University
            DBFunctions::insert('grand_universities',
                                array('university_name' => $this->POST('university'),
                                      '`order`' => 10000,
                                      '`default`' => 0));
        }
        
        
        if(count($posCheck) == 0){
            // Create new Position
            DBFunctions::insert('grand_positions',
                                array('position' => $this->POST('position'),
                                      '`order`' => 10000,
                                      '`default`' => 0));
            
        }
       
        $universities = University::getAllUniversities();
        $positions = Person::getAllPositions();
        
        $university_id = "";
        $position_id = "";
        $faculty = $this->POST('faculty');
        $department = $this->POST('department');
        $start_date = $this->POST('startDate');
        $end_date = $this->POST('endDate');
        
        foreach($universities as $university){
            if($this->POST('university') == $university->getName()){
                $university_id = $university->getId();
            }
        }
        
        foreach($positions as $id => $position){
            if($this->POST('position') == $position){
                $position_id = $id;
            }
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::insert('grand_user_university',
                            array('user_id' => $person->getId(),
                                  'university_id' => $university_id,
                                  'faculty' => $faculty,
                                  'department' => $department,
                                  'position_id' => $position_id,
                                  'start_date' => $start_date,
                                  'end_date' => $end_date));

        $this->params['personUniversityId'] = DBFunctions::insertId();
        $person->universityDuring = array();
        Cache::delete("user_university_{$person->id}");
        MailingList::subscribeAll($person);
        return $this->doGET();
    }
    
    function doPUT(){
        $personUniversityId = $this->getParam('personUniversityId');
        $person = Person::newFromId($this->getParam('id'));
        
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        
        $uniCheck = DBFunctions::execSQL("SELECT * FROM grand_universities
                                          WHERE university_name = BINARY '".DBFunctions::escape($this->POST('university'))."'");
        $posCheck = DBFunctions::execSQL("SELECT * FROM grand_positions
                                          WHERE position = BINARY '".DBFunctions::escape($this->POST('position'))."'");

        if(count($uniCheck) == 0){
            // Create new University
            DBFunctions::insert('grand_universities',
                                array('university_name' => $this->POST('university'),
                                      '`order`' => 10000,
                                      '`default`' => 0));
        }
        
        if(count($posCheck) == 0){
            // Create new Position
            DBFunctions::insert('grand_positions',
                                array('position' => $this->POST('position'),
                                      '`order`' => 10000,
                                      '`default`' => 0));
            
        }
        
        $universities = University::getAllUniversities();
        $positions = Person::getAllPositions();
        
        $university_id = "";
        $position_id = "";
        $faculty = $this->POST('faculty');
        $department = $this->POST('department');
        $start_date = $this->POST('startDate');
        $end_date = $this->POST('endDate');
        
        foreach($universities as $university){
            if($this->POST('university') == $university->getName()){
                $university_id = $university->getId();
            }
        }
        
        foreach($positions as $id => $position){
            if($this->POST('position') == $position){
                $position_id = $id;
            }
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::update('grand_user_university',
                            array('user_id' => $person->getId(),
                                  'university_id' => $university_id,
                                  'faculty' => $faculty,
                                  'department' => $department,
                                  'position_id' => $position_id,
                                  'start_date' => $start_date,
                                  'end_date' => $end_date),
                            array('id' => EQ($personUniversityId)));
        $person->universityDuring = array();
        Cache::delete("user_university_{$person->id}");
        MailingList::subscribeAll($person);
        return $this->doGET();
    }
    
    function doDELETE(){
        $personUniversityId = $this->getParam('personUniversityId');
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::delete('grand_user_university',
                            array('id' => $personUniversityId));
        $person->universityDuring = array();
        Cache::delete("user_university_{$person->id}");
        MailingList::subscribeAll($person);
        return json_encode(array());
    }
}

?>
