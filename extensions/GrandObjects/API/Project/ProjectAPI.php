<?php

class ProjectAPI extends RESTAPI {
    
    function getPeople($project){
        $people = $project->getAllPeople();
        $finalPeople = array();
        foreach($people as $person){
            $finalPeople[$person->getReversedName()] = $person;
        }
        ksort($finalPeople);
        $finalPeople = new Collection(array_values($finalPeople));
        return $finalPeople->toArray();
    }
    
    function doGET(){
        $full = ($this->getParam('full') != "");
        if($this->getParam('id') != ""){
            $project = Project::newFromId($this->getParam('id'));
            if($this->getParam('id') == "-1"){
                $project->name = "Other";
            }
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
            }
            $array = $project->toArray();
            if($full){
                $array['members'] = $this->getPeople($project);
            }
            return json_encode($array);
        }
        else{
            $projects = new Collection(Project::getAllProjectsEver(true));
            $arrays = $projects->toArray();
            if($full){
                foreach($arrays as $key => $array){
                    $project = Project::newFromId($array['id']);
                    $arrays[$key]['members'] = $this->getPeople($project);
                }
            }
            return json_encode($arrays);
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
