<?php

    class ProjectPeopleNoLeadersArray extends GroupByArray {
        
        function ProjectPeopleNoLeadersArray($table, $params){
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
                if((isset($params[1]) && isset($params[2]) && $person->isRoleDuring(PNI, $start, $end) || $person->isRole(CNI, $start, $end) || $person->isRole(AR, $start, $end)) || 
                   ($person->isRole(PNI) || $person->isRole(CNI) || $person->isRole(AR))){
                    if(!$person->leadershipOf($project->getName())){
                        $this->array[] = $person->getName();
                    }
                }
            }
        }

    }

?>
