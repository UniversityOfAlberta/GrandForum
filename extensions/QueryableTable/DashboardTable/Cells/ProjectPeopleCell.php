<?php

class ProjectPeopleCell extends Cell{
    
    function ProjectPeopleCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
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
        $this->style = 'text-align:left;';
        $person = Person::newFromName($this->value);
        return "<a href='{$person->getUrl()}' target = '_blank'><b>{$person->getNameForForms()}</b></a>";
    }
}

?>
