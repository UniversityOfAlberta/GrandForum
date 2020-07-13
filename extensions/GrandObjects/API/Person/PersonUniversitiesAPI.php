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
                    $newUniversities['9999-99-99 99:99:99_'.$uni['startDate'].'_'.$uni['id']] = $uni;
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
        
        $department = $this->POST('department');
        $researchArea = $this->POST('researchArea');
        $primary = $this->POST('primary');
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
        DBFunctions::insert('grand_user_university',
                            array('user_id' => $person->getId(),
                                  'university_id' => $university_id,
                                  'department' => $department,
                                  'research_area' => $researchArea,
                                  'position_id' => $position_id,
                                  '`primary`' => $primary,
                                  'start_date' => $start_date,
                                  'end_date' => $end_date));
        $this->params['personUniversityId'] = DBFunctions::insertId();
        $person->universityDuring = array();
        Cache::delete("user_university_{$person->id}");
        Cache::delete("user_university_{$person->id}", true);

        // Send out Notifications to the supervisors
        $supervisors = $person->getSupervisors();
        foreach($supervisors as $supervisor){
            Notification::addNotification($me, $supervisor, "Basic Info Added", "{$person->getNameForForms()} has been added to '{$this->POST('department')}' as a '{$this->POST('position')}'", "{$person->getUrl()}", !($supervisor->isMe()));
        }
        
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

        $department = $this->POST('department');
        $researchArea = $this->POST('researchArea');
        $primary = $this->POST('primary');
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
        
        // Check if the data is actually changing
        $hasChanged = false;
        $data = DBFunctions::select(array('grand_user_university'),
                                    array('*'),
                                    array('id' => EQ($personUniversityId)));
        if(count($data) > 0){
            $row = $data[0];
            if($university_id != $row['university_id'] ||
               $department != $row['department'] ||
               $researchArea != $row['research_area'] ||
               $position_id != $row['position_id'] ||
               $primary != $row['primary'] ||
               substr($start_date, 0, 10) != str_replace("0000-00-00", "", substr($row['start_date'], 0, 10)) ||
               substr($end_date, 10) != str_replace("0000-00-00", "", substr($row['end_date'], 0, 10))){
                $hasChanged = true;  
            }
        }
        
        DBFunctions::update('grand_user_university',
                            array('user_id' => $person->getId(),
                                  'university_id' => $university_id,
                                  'department' => $department,
                                  'research_area' => $researchArea,
                                  'position_id' => $position_id,
                                  '`primary`' => $primary,
                                  'start_date' => $start_date,
                                  'end_date' => $end_date),
                            array('id' => EQ($personUniversityId)));

        $person->universityDuring = array();
        Cache::delete("user_university_{$person->id}");
        Cache::delete("user_university_{$person->id}", true);
        
        if($hasChanged){
            // Send out Notifications to the supervisors
            $supervisors = $person->getSupervisors();
            foreach($supervisors as $supervisor){
                Notification::addNotification($me, $supervisor, "Basic Info Changed", "{$person->getNameForForms()} has had their Basic Info changed", "{$person->getUrl()}", !($supervisor->isMe()));
            }
        }
        
        return $this->doGET();
    }
    
    function doDELETE(){
        $personUniversityId = DBFunctions::escape($this->getParam('personUniversityId'));
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        DBFunctions::select(array('grand_relations'),
                            array('id'),
                            array('university' => EQ($personUniversityId),
                                  'user1' => NEQ($me->getId())));
        if(DBFunctions::getNRows() > 0){
            $this->throwError("This University cannot be deleted, there are still relations linked to it (possibly by someone else)");
        }
        DBFunctions::delete('grand_user_university',
                            array('id' => $personUniversityId));
        $person->universityDuring = array();
        Cache::delete("user_university_{$person->id}");
        Cache::delete("user_university_{$person->id}", true);
        
        // Send out Notifications to the supervisors
        if(count($data) > 0){
            $row = $data[0];
            $supervisors = $person->getSupervisors();
            foreach($supervisors as $supervisor){
                Notification::addNotification($me, $supervisor, "Basic Info Deleted", "{$person->getNameForForms()} is no longer in '{$row['department']}' as a '{$row['position']}'", "{$person->getUrl()}", !($supervisor->isMe()));
            }
        }
        return json_encode(array());
    }

}

?>
