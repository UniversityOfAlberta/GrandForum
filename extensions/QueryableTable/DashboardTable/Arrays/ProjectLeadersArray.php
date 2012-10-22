<?php

    class ProjectLeadersArray extends GroupByArray {
        
        function ProjectLeadersArray($table){
            $project = $table->obj;
            $people = array_merge($project->getLeaders(), $project->getCoLeaders());
            foreach($people as $person){
                $this->array[] = $person->getName();
            }
        }
        
    }

?>
