<?php

    class ProjectNINoLeadersArray extends GroupByArray {
        
        function __construct($table, $params){
            $project = $table->obj;
            if(isset($params[1]) && isset($params[2])){
                $start = $params[1];
                $end = $params[2];
                $people = $project->getAllPeopleDuring(null, $start, $end);
            }
            else{
                $people = $project->getAllPeople();
            }
            foreach($people as $person){
                if((isset($params[1]) && isset($params[2]) && $person->isRoleDuring(NI, $start, $end)) || 
                   ($person->isRole(NI))){
                    if(!$person->isRole(PL, $project->getName())){
                        $this->array[] = $person->getName();
                    }
                }
            }
        }

    }

?>
