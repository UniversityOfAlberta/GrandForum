<?php

class ProjectPeopleRolesCell extends Cell{
    
    function ProjectPeopleRolesCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $start = "0000";
        $end = "2100";
        if(count($params) == 1){
            $params[2] = $params[0];
        }
        else{
            if(isset($params[0])){
                // Start
                $start = $params[0];
            }
            if(isset($params[1])){
                // End
                $end = $params[1];
            }
        }
        if(isset($params[2])){
            $person = Person::newFromName($params[2]);
            $values = array();
            $leads = $person->getLeadProjects();
            $coLeads = $person->getCoLeadProjects();
            foreach($leads as $lead){
                if($lead->getId() == $table->obj->getId()){
                    $values[] = "PL";
                    break;
                }
            }
            foreach($coLeads as $lead){
                if($lead->getId() == $table->obj->getId()){
                    $values[] = "COPL";
                    break;
                }
            }
            foreach($person->getRoles() as $role){
                if($role->getRole() == HQP || 
                    $role->getRole() == PNI || 
                    $role->getRole() == CNI){
                    $values[] = $role->getRole();
                }
            }
            foreach($person->getLeadershipRoles() as $role){
                $values[] = $role->getRole();
            }
            $this->value = "<a href='{$person->getUrl()}' target = '_blank'><b>{$person->getReversedName()}</b></a><br />(".implode(", ", $values).")";
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(PROJECT_PEOPLE_ROLES, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $this->style = 'text-align:left;';
        return "{$this->value}";
    }
}

?>
