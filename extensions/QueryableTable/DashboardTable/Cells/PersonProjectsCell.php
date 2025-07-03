<?php

class PersonProjectsCell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $project = Project::newFromHistoricName(str_replace(" <span style='display:none;'>(Completed)</span>", "", $params[0]));
            $deleted = ($project->isDeleted()) ? " <span style='display:none;'>(Completed)</span>" : "";
            $this->value = $params[0].$deleted;
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
        $project = Project::newFromHistoricName(str_replace(" <span style='display:none;'>(Completed)</span>", "", $this->value));
        $deleted = ($project->isDeleted()) ? " <span style='display:none;'>(Completed)</span>" : "";
        return "<a href='{$project->getUrl()}' target = '_blank'><b>{$project->getName()}{$deleted}</b></a>";
    }
}

?>
