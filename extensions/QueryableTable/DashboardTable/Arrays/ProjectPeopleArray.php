<?php

    class ProjectPeopleArray extends GroupByArray {
        
        function ProjectPeopleArray($table){
            $project = $table->obj;
            $start = $project->getCreated();
            $end = ($project->isDeleted()) ? $project->getDeleted() : date('Y-m-d')." 23:59:59";
            $people = $project->getAllPeopleDuring(null, $start, $end);

            foreach($people as $person){
                if($person->isRoleDuring(PNI, $start, $end) || $person->isRoleDuring(CNI, $start, $end) || $person->isRoleDuring(AR, $start, $end) || $person->leadershipOf($project)){
                    $this->array[] = $person->getName();
                }
            }            
        }
        
    }

?>
