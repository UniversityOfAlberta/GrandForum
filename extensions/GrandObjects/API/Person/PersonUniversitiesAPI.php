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
                if($uni['endDate'] == ZOTT){
                    // Till the end of time
                    $newUniversities[EOT.'_'.$uni['startDate'].'_'.$uni['id']] = $uni;
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
                                  'start_date' => ZERO_DATE($start_date, zull),
                                  'end_date' => ZERO_DATE($end_date, zull)));
        $this->params['personUniversityId'] = DBFunctions::insertId();
        $person->universityDuring = array();
        Person::$allUniversityCache = array();
        DBCache::delete("user_university");
        DBCache::delete("user_university_{$person->id}");
        DBCache::delete("user_university_{$person->id}", true);
        DBCache::delete("user_universities_{$person->id}", true);
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
        
        DBFunctions::update('grand_user_university',
                            array('user_id' => $person->getId(),
                                  'university_id' => $university_id,
                                  'department' => $department,
                                  'research_area' => $researchArea,
                                  'position_id' => $position_id,
                                  '`primary`' => $primary,
                                  'start_date' => ZERO_DATE($start_date, zull),
                                  'end_date' => ZERO_DATE($end_date, zull)),
                            array('id' => EQ($personUniversityId)));

        $person->universityDuring = array();
        Person::$allUniversityCache = array();
        DBCache::delete("user_university");
        DBCache::delete("user_university_{$person->id}");
        DBCache::delete("user_university_{$person->id}", true);
        DBCache::delete("user_universities_{$person->id}", true);
        return $this->doGET();
    }
    
    function doDELETE(){
        $personUniversityId = $this->getParam('personUniversityId');
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
        Person::$allUniversityCache = array();
        DBCache::delete("user_university");
        DBCache::delete("user_university_{$person->id}");
        DBCache::delete("user_university_{$person->id}", true);
        DBCache::delete("user_universities_{$person->id}", true);
        return json_encode(array());
    }
}

?>
