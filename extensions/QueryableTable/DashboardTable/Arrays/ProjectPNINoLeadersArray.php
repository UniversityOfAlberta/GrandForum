<?php

    class ProjectPNINoLeadersArray extends GroupByArray {
        
        function ProjectPNINoLeadersArray($table, $params){
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
                if((isset($params[1]) && isset($params[2]) && $person->isRoleDuring(PNI, $start, $end)) || 
                   ($person->isRole(PNI))){
                    if(!$person->leadershipOf($project->getName()) && !$person->isRole(CHAMP)){
                        $this->array[] = $person->getName();
                    }
                }
            }
        }

    }

?>
