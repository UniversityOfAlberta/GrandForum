<?php

class ProjectMembersAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
            }
            if($this->getParam('role') != ""){
                $exploded = explode(",", $this->getParam('role'));
                $finalPeople = array();
                foreach($exploded as $role){
                    $role = trim($role);
                    if(!$project->isDeleted()){
                        $people = $project->getAllPeople($role);
                    }
                    else{
                        $people = $project->getAllPeopleOn($role, $project->getEffectiveDate());
                    }
                    foreach($people as $person){
                        $finalPeople[$person->getReversedName()] = $person;
                    }
                }
                ksort($finalPeople);
                $finalPeople = new Collection(array_values($finalPeople));
                return $finalPeople->toJSON();
            }
            else{
                $finalPeople = array();
                $people = $project->getAllPeople();
                foreach($people as $person){
                    $finalPeople[$person->getReversedName()] = $person;
                }
                ksort($finalPeople);
                $finalPeople = new Collection(array_values($finalPeople));
                return $finalPeople->toJSON();
            }
        }
    }
    
    function doPOST(){
        
    }
    
    function doPUT(){

    }
    
    function doDELETE(){

    }
	
}

?>
