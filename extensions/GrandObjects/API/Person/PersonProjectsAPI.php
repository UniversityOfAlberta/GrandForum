<?php

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
