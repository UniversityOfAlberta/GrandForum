<?php

class PersonAPI extends RESTAPI {

    var $id;
    var $action;

    function processParams($params){
        $this->id = @$params[1];
        $this->action = @$params[2];
    }

    function isLoginRequired(){
        return true;
    }
    
    function doGET(){
        if($this->id != ""){
            $person = Person::newFromId($this->id);
            if($person == null || $person->getName() == ""){
                $this->throwError("This user does not exist");
            }
            if($this->action == "projects"){
                $json = array();
                $projects = $person->getProjects(true); //TODO: Might need to get full history here
                foreach($projects as $project){
                    $json[] = array('projectId' => $project->getId(),
                                    'personId' => $person->getId(),
                                    'startDate' => $project->getJoinDate($person),
                                    'endDate' => $project->getEndDate($person));
                }
                return json_encode($json);
            }
            return $person->toJSON();
        }
        else{
            $json = array();
            $people = Person::getAllPeople('all');
            foreach($people as $person){
                $json[] = $person->toArray();
            }
            return json_encode($json);
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
        $person = Person::newFromId($this->id);
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        header('Content-Type: application/json');
        $person->update();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->id);
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        header('Content-Type: application/json');
        $person->delete();
    }
	
}

?>
