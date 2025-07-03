<?php

class ProjectRolesCell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        global $config;
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
            $leads = $person->leadership();
            foreach($leads as $lead){
                if($lead->getId() == $table->obj->getId()){
                    if($table->obj->isSubProject()){
                        $values[] = "s".PL;
                    }
                    else{
                        $values[] = PL;
                    }
                    break;
                }
            }
            foreach($table->obj->getSubProjects() as $sub){
                $break = false;
                foreach($leads as $lead){
                    if($lead->getId() == $sub->getId()){
                        $values[] = "s".sPL;
                        $break = true;
                        break;
                    }
                }
                if($break){
                    break;
                }
            }
            $committees = $config->getValue('committees');
            foreach($person->getRoles() as $role){
                if(!isset($committees[$role->getRole()])){
                    $values[] = $role->getRole();
                }
            }
            $values = array_unique($values);
            $this->value = implode(", ", $values);
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(PROJECT_ROLES, $this);
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
