<?php

class ProjectContributionsAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $array = array();
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
            }
            if(!$me->isRoleAtLeast(MANAGER)){
                $this->throwError("You are not allowed to access this API");
            }
            $contributions = $project->getContributions();
            foreach($contributions as $contribution){
                $array[] = $contribution;
            }
            $array = new Collection(array_values($array));
            return $array->toJSON();
        }
    }
    
    function doPOST(){
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doGET();
    }
    
    function doDELETE(){
        return $this->doGET();
    }

}

?>
