<?php

    class ProjectPeopleArray extends GroupByArray {
        
        function ProjectPeopleArray($table){
            $project = $table->obj;
            $people = $project->getAllPeople();
            foreach($people as $person){
                if($person->isRole(PNI) || $person->isRole(CNI) || $person->leadershipOf($project)){
                    $this->array[] = $person->getName();
                }
            }
        }
        
    }

?>
