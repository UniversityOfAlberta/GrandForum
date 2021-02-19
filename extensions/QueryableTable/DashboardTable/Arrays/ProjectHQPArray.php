<?php

    class ProjectHQPArray extends GroupByArray {
        
        function ProjectHQPArray($table){
            $project = $table->obj;
            $start = $project->getCreated();
            $end = ($project->isDeleted()) ? $project->getDeleted() : date('Y-m-d')." 23:59:59";
            $people = $project->getAllPeopleDuring(null, $start, $end);

            foreach($people as $person){
                if($person->isRoleDuring(HQP, $start, $end) && !$person->isRole(PL, $project) && !$person->isRoleDuring(NI, $start, $end)){
                    if(!$person->isRole(CHAMP)){
                        $this->array[$person->getName()] = $person->getName();
                    }
                }
            }            
        }
        
    }

?>
