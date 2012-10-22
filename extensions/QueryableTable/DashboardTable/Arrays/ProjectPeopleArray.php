<?php

    class ProjectPeopleArray extends GroupByArray {
        
        function ProjectPeopleArray($table){
            $project = $table->obj;
            $people = $project->getAllPeople();
            foreach($people as $person){
                if($person->isRole(PNI) || $person->isRole(CNI)){
                    $this->array[] = $person->getName();
                }
            }
        }
        
    }

?>
