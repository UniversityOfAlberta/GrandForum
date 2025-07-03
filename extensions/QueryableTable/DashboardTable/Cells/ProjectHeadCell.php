<?php

class ProjectHeadCell extends HeadCell {
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
        }
        else{
            $this->value = $table->obj->getName();
        }
    }
    
    function rasterize(){
        return array(PROJECT_HEAD, $this);
    }
    
    function toString(){
        $project = Project::newFromName($this->value);
        return $project->getName();
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $this->style = 'background:#DDDDDD;';
        $project = Project::newFromName($this->value);
        return "<a href='{$project->getUrl()}' target = '_blank'><b>{$project->getName()}: {$project->getFullName()}</b></a>";
    }
}

?>
