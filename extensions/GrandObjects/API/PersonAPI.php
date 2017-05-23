<?php

class PersonAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $person = Person::newFromId($this->getParam('id'));
            if($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI))){
                $this->throwError("This user does not exist");
            }
            return $person->toJSON();
        }
    }
    
    function doPOST(){
        $person = new Person(array());
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->ldap = $this->POST('ldap');
        $person->gender = $this->POST('gender');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        if($person->exists()){
            $this->throwError("A user by the name of <i>{$person->getName()}</i> already exists");
        }
        $status = $person->create();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be created");
        }
        $person = Person::newFromName($person->getName());
        return $person->toJSON();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $person->name = $this->POST('name');
        $person->realname = $this->POST('realName');
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->ldap = $this->POST('ldap');
        $person->gender = $this->POST('gender');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        $status = $person->update();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be updated");
        }
        $person = Person::newFromId($this->getParam('id'));
        return $person->toJSON();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $status = $person->delete();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be deleted");
        }
    }
}

class PeopleAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('role') != ""){
            $university = "";
            if($this->getParam('university') != ""){
                $university = $this->getParam('university');
            }
            $exploded = explode(",", $this->getParam('role'));
            $finalPeople = array();
            foreach($exploded as $role){
                $role = trim($role);
                $people = Person::getAllPeople($role);
                foreach($people as $person){
                    if($university == ""){
                        $finalPeople[$person->getReversedName()] = $person;
                    }
                    else {
                        foreach($person->getUniversitiesDuring(date("Y-01-01 00:00:00"), date("Y-12-31 23:59:59")) as $uni){
                            if($uni['university'] == $university){
                                $finalPeople[$person->getReversedName()] = $person;
                                break;
                            }
                        }
                    }
                }
            }
            ksort($finalPeople);
            $finalPeople = new Collection(array_values($finalPeople));
            return $finalPeople->toJSON();
        }
        else{
            $people = new Collection(Person::getAllPeople('all'));
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

class PersonProjectsAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $projects = $person->getPersonProjects();
        if($this->getParam('personProjectId') != ""){
            // Single Project
            foreach($projects as $project){
                if($project['id'] == $this->getParam('personProjectId')){
                    return json_encode($project);
                }
            }
        }
        else{
            // All Projects
            return json_encode($projects);
        }
    }
    
    function doPOST(){
        $person = Person::newFromId($this->getParam('id'));
        $project = Project::newFromName($this->POST('name'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        if($project == null || $project->getName() == ""){
            $this->throwError("This Project does not exist");
        }
        if(!in_array($this->POST('name'), $allowedProjects) || 
           !in_array($project->getName(), $allowedProjects)){
            $this->throwError("You are not allowed to add this person to that project");
        }
        MailingList::unsubscribeAll($person);
        $status = DBFunctions::insert('grand_project_members',
                                      array('user_id'    => $person->getId(),
                                            'project_id' => $project->getId(),
                                            'start_date' => $this->POST('startDate'),
                                            'end_date'   => $this->POST('endDate'),
                                            'comment'    => $this->POST('comment')));

        $data = DBFunctions::select(array('grand_project_members'),
                                    array('id'),
                                    array('project_id' => $project->getId(),
                                          'user_id' => $person->getId()),
                                    array('id' => 'DESC'),
                                    array(1));
        if(count($data) > 0){
            $this->params['personProjectId'] = $data[0]['id'];
        }
        $person->projects = null;
        MailingList::subscribeAll($person);
        return $this->doGET();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        $project = Project::newFromName($this->POST('name'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        if($project == null || $project->getName() == ""){
            $this->throwError("This Project does not exist");
        }
        if(!in_array($this->POST('name'), $allowedProjects) || 
           !in_array($project->getName(), $allowedProjects)){
            $this->throwError("You are not allowed to add this person to that project");
        }
        MailingList::unsubscribeAll($person);
        $status = DBFunctions::update('grand_project_members',
                                      array('project_id' => $project->getId(),
                                            'start_date' => $this->POST('startDate'),
                                            'end_date'   => $this->POST('endDate'),
                                            'comment'    => $this->POST('comment')),
                                      array('id' => $this->getParam('personProjectId')));
        $person->projects = null;
        MailingList::subscribeAll($person);
        if(!$status){
            $this->throwError("The project <i>{$project->getName()}</i> could not be updated");
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        $data = DBFunctions::select(array('grand_project_members'),
                                    array('project_id'),
                                    array('id' => EQ($this->getParam('personProjectId'))));
        if(count($data) > 0){
            $project = Project::newFromId($data[0]['project_id']);
            if(!in_array($project->getName(), $allowedProjects)){
                $this->throwError("You are not allowed to remove this person from that project");
            }
        }
        else{
            $this->throwError("This Project does not exist");
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::delete('grand_project_members',
                            array('id' => $this->getParam('personProjectId')));
        $person->projects = null;
        MailingList::subscribeAll($person);
        return false;
    }
}

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
        $uniCheck = DBFunctions::select(array('grand_universities'),
                                        array('*'),
                                        array('university_name' => $this->POST('university')));
        $posCheck = DBFunctions::select(array('grand_positions'),
                                        array('*'),
                                        array('position' => $this->POST('position')));
        
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
                                  'department' => $department,
                                  'research_area' => $researchArea,
                                  'position_id' => $position_id,
                                  'start_date' => $start_date,
                                  'end_date' => $end_date));
                                  
        $this->params['personUniversityId'] = DBFunctions::insertId();
        $person->universityDuring = array();
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
        
        $uniCheck = DBFunctions::select(array('grand_universities'),
                                        array('*'),
                                        array('university_name' => $this->POST('university')));
        $posCheck = DBFunctions::select(array('grand_positions'),
                                        array('*'),
                                        array('position' => $this->POST('position')));

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
                                  'department' => $department,
                                  'research_area' => $researchArea,
                                  'position_id' => $position_id,
                                  'start_date' => $start_date,
                                  'end_date' => $end_date),
                            array('id' => EQ($personUniversityId)));
        $person->universityDuring = array();
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
        MailingList::subscribeAll($person);
        return false;
    }
}

class PersonRelationsAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $relations = $person->getRelations('all', true);
        if($this->getParam('relId') != ""){
            // Single Relation
            foreach($relations as $type){
                foreach($type as $id => $relation){
                    if($id == $this->getParam('relId')){
                        return json_encode($relation);
                    }
                }
            }
        }
        else{
            // All Relations
            return json_encode(flatten($relations));
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        
        $relation = new Relationship(array());
        $relation->user1 = $this->POST('user1');
        $relation->user2 = $this->POST('user2');
        $relation->type = $this->POST('type');
        $relation->startDate = $this->POST('startDate');
        $relation->endDate = $this->POST('endDate');
        $relation->comment = $this->POST('comment');
        $relation->create();
        return $this->doGET();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        
        $relation = Relationship::newFromId($this->getParam('relId'));
        if($relation->getId() == null){
            $this->throwError("This Relationship does not exist");
        }
        $relation->user1 = $this->POST('user1');
        $relation->user2 = $this->POST('user2');
        $relation->type = $this->POST('type');
        $relation->startDate = $this->POST('startDate');
        $relation->endDate = $this->POST('endDate');
        $relation->comment = $this->POST('comment');
        $relation->update();
        return $this->doGET();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        $relation = Relationship::newFromId($this->getParam('relId'));
        if($relation->getId() == null){
            $this->throwError("This Relationship does not exist");
        }
        $relation->delete();
        return false;
    }
}

class PersonRolesAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $json = array();
        $roles = $person->getRoles(true);
        foreach($roles as $role){
            $json[] = array('roleId' => $role->getId(),
                            'personId' => $person->getId(),
                            'startDate' => $role->getStartDate(),
                            'endDate' => $role->getEndDate());
        }
        return json_encode($json);
    }
    
    function doPOST(){
        return doGET();
    }
    
    function doPUT(){
        return doGET();
    }
    
    function doDELETE(){
        return doGET();
    }
}

class PersonProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam(0) == "person"){
            // Get Products
            $me = Person::newFromWgUser();
            $person = Person::newFromId($this->getParam('id'));
            $json = array();
            $onlyPublic = true;
            $showAll = false;
            if($this->getParam(3) == "private"){
                $onlyPublic = false;
            }
            if($this->getParam(3) == "all" && $me->isRoleAtLeast(ADMIN) && $me->getId() == $person->getId()){
                $showAll = true;
                $onlyPublic = false;
            }
            if($showAll){
                $products = Product::getAllPapers("all", true, 'both', $onlyPublic, 'Public');
            }
            else{
                $products = $person->getPapers("all", true, 'both', $onlyPublic, 'Public');
            }
            foreach($products as $product){
                $array = array('productId' => $product->getId(), 
                               'personId'=> $this->getParam('id'),
                               'startDate' => $product->getDate(),
                               'endDate' => $product->getDate());
                if($this->getParam('productId') != null && $product->getId() == $this->getParam('productId')){
                    return json_encode($array);
                }
                else if($this->getParam('productId') == null){
                    $json[] = $array;
                }
            }
            return json_encode($json);
        }
        else if($this->getParam(0) == "product"){
            // Get Authors
            $product = Paper::newFromId($this->getParam('id'));
            $json = array();
            $authors = $product->getAuthors(); 
            foreach($authors as $author){
                if($author->getId()){
                    $array = array('productId' => $this->getParam('id'), 
                                   'personId' => $author->getId(),
                                   'startDate' => $product->getDate(),
                                   'endDate' => $product->getDate());
                    if($this->getParam('personId') != null && $author->getId() == $this->getParam('personId')){
                        return json_encode($array);
                    }
                    else if($this->getParam('personId') == null){
                        $json[] = $array;
                    }
                }
            }
            return json_encode($json);
        }
        return null;
    }
    
    function doPOST(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            if($this->getParam(0) == "person"){
                $person = Person::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $person = Person::newFromId($this->getParam('personId'));
            }
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            $found = false;
            foreach($authors as $author){
                if($author->getId() == $person->getId()){
                    $found = true;
                }
            }
            if(!$found){
                $authors = unserialize($serializedAuthors);
                $authors[] = $person->getId();
                DBFunctions::update('grand_products',
                                    array('authors' => serialize($authors)),
                                    array('id' => $product->getId()));
                Paper::$cache = array();
                Paper::$dataCache = array();
            }
        }
        else{
            $this->throwError("Author was not added");
        }
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doPOST();
    }
    
    function doDELETE(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            if($this->getParam(0) == "person"){
                $person = Person::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $person = Person::newFromId($this->getParam('personId'));
            }
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            foreach($authors as $key => $author){
                if($author->getId() == $person->getId()){
                    $serializedAuthors = unserialize($serializedAuthors);
                    unset($serializedAuthors[$key]);
                    DBFunctions::update('grand_products',
                                        array('authors' => serialize($serializedAuthors)),
                                        array('id' => $product->getId()));
                    return;
                }
            }
        }
        else{
            $this->throwError("Author was not deleted");
        }
    }
    
}

class PersonRoleStringAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $json = array('id' => $person->getId(),
                      'roleString' => $person->getRoleString());
        return json_encode($json);
    }
    
    function doPOST(){
        return doGET();
    }
    
    function doPUT(){
        return doGET();
    }
    
    function doDELETE(){
        return doGET();
    }

}

?>
