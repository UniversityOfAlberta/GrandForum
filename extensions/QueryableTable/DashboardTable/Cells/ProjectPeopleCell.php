<?php

class ProjectPeopleCell extends Cell{
    
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
            $this->value = $params[2];
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(PROJECT_PEOPLE, $this);
    }
    
    function toString(){
        $person = Person::newFromName($this->value);
        return $person->getNameForForms();
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        if(strstr($this->style, 'text-align:left;') === false){
            $this->style .= 'text-align:left;';
        }
        $person = Person::newFromName($this->value);
        return "<a href='{$person->getUrl()}' target = '_blank'><b>{$person->getNameForForms()}</b></a>";
    }
}

?>
