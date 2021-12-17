<?php

class ProjectPeopleRolesCell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
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
            $roles = $person->getRoleOn($table->obj);
            $this->value = "<a href='{$person->getUrl()}' target = '_blank'><b>{$person->getNameForForms()}</b></a><br />({$roles})";
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
        if(strstr($this->style, 'text-align:left;') === false){
            $this->style .= 'text-align:left;';
        }
        return "{$this->value}";
    }
}

?>
