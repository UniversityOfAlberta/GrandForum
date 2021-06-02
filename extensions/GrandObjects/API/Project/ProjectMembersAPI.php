<?php

class ProjectMembersAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $projects = explode(",", $this->getParam('id'));
            $finalPeople = array();
            foreach($projects as $pId){
                $project = Project::newFromId($pId);
                if($project == null || $project->getName() == ""){
                    $project = Project::newFromName($pId);
                    if($project == null || $project->getName() == ""){
                        $this->throwError("This project does not exist");
                    }
                }
                if($this->getParam('role') != ""){
                    $exploded = explode(",", $this->getParam('role'));
                    foreach($exploded as $role){
                        $role = trim($role);
                        if(!$project->isDeleted()){
                            if(strstr($role, "Former")){
                                $people = $project->getAllPeopleDuring($role, "0000-00-00", currentTimeStamp());
                            }
                            else{
                                $people = $project->getAllPeople($role);
                            }
                        }
                        else{
                            $people = $project->getAllPeopleOn($role, $project->getEffectiveDate());
                        }
                        foreach($people as $person){
                            $finalPeople[$person->getReversedName()] = $person;
                        }
                    }
                }
                else{
                    $people = $project->getAllPeople();
                    foreach($people as $person){
                        $finalPeople[$person->getReversedName()] = $person;
                    }
                }
            }
            ksort($finalPeople);
            $finalPeople = new Collection(array_values($finalPeople));
            return $finalPeople->toJSON();
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
