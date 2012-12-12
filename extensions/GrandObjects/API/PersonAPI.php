<?php

class PersonAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $person = Person::newFromId($this->getParam('id'));
            if($person == null || $person->getName() == ""){
                $this->throwError("This user does not exist");
            }
            return $person->toJSON();
        }
        else{
            $people = new Collection(Person::getAllPeople('all'));
            return $people->toJSON();
        }
    }
    
    function doPOST(){
        $person = new Person(array());
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        if($person->exists()){
            $this->throwError("This user already exists");
        }
        header('Content-Type: application/json');
        $person->create();
        
        $person = Person::newFromName($person->getName());
        return $person->toJSON();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        header('Content-Type: application/json');
        $person->update();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        header('Content-Type: application/json');
        $person->delete();
    }
}

class PersonProjectsAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $json = array();
        $projects = $person->getProjects(true);
        foreach($projects as $project){
            $json[] = array('projectId' => $project->getId(),
                            'personId' => $person->getId(),
                            'startDate' => $project->getJoinDate($person),
                            'endDate' => $project->getEndDate($person));
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

?>
