<?php

    class ProjectPeopleNoLeadersArray extends GroupByArray {
        
        function ProjectPeopleNoLeadersArray($table){
            $project = $table->obj;
            $people = $project->getAllPeople();
            foreach($people as $person){
                if($person->isRole(PNI) || $person->isRole(CNI) || $person->isRole(AR)){
                    if(!$person->leadershipOf($project->getName())){
                        $this->array[] = $person->getName();
                    }
                }
            }
        }
        
    }

?>
