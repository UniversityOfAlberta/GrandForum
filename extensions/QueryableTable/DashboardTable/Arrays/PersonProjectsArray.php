<?php

    class PersonProjectsArray extends GroupByArray {
        
        function PersonProjectsArray($table, $params){
            $start = "0000-00-00";
            $end = "2100-00-00";
            if(count($params) > 1){
                if(isset($params[1])){
                    // Start
                    $start = $params[1];
                }
                if(isset($params[2])){
                    // End
                    $end = $params[2];
                }
            }
            $person = $table->obj;
            $projects = $person->getProjectsDuring($start, $end);
            foreach($projects as $project){
                $this->array[] = $project->getName();
            }
        }
        
    }

?>
