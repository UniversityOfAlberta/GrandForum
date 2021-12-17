<?php

    class ProjectLeadersArray extends GroupByArray {
        
        function __construct($table){
            $project = $table->obj;
            $people = $project->getLeaders();
            foreach($people as $person){
                $this->array[] = $person->getName();
            }
        }
        
    }

?>
