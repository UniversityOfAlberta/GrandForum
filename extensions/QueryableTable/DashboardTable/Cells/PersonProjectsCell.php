<?php

class PersonProjectsCell extends Cell{
    
    function PersonProjectsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(PERSON_PROJECTS, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $project = Project::newFromName($this->value);
        $deleted = ($project->isDeleted()) ? " (Completed)" : "";
        return "<a href='{$project->getUrl()}' target = '_blank'><b>{$project->getName()}{$deleted}</b></a>";
    }
}

?>
