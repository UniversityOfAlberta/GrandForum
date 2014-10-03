<?php

    class ProjectARNoLeadersArray extends GroupByArray {
        
        function ProjectARNoLeadersArray($table, $params){
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
                if((isset($params[1]) && isset($params[2]) && $person->isRoleDuring(AR, $start, $end) && !$person->isRoleDuring(CNI, $start, $end) && !$person->isRoleDuring(PNI, $start, $end)) || 
                   ($person->isRole(AR) && !$person->isRole(CNI) && !$person->isRole(PNI))){
                    if(!$person->leadershipOf($project->getName()) && !$person->isRole(CHAMP)){
                        $this->array[] = $person->getName();
                    }
                }
            }
        }

    }

?>
