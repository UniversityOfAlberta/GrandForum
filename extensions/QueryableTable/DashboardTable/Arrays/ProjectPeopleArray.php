<?php

    class ProjectPeopleArray extends GroupByArray {
        
        function ProjectPeopleArray($table){
            $project = $table->obj;
            $start = $project->getCreated();
            $end = ($project->isDeleted()) ? $project->getDeleted() : date('Y-m-d')." 23:59:59";
            $people = $project->getAllPeopleDuring(null, $start, $end);

            foreach($people as $person){
                if($person->isRoleDuring(NI, $start, $end) || $person->isRole(PL, $project)){
                    if(!$person->isRole(CHAMP)){
                        $this->array[] = $person->getName();
                    }
                }
            }            
        }
        
    }

?>
