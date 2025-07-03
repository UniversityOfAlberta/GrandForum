<?php

class VProjCell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if($cellValue != ''){
            $project = Project::newFromName($cellValue);
            if($project == null || $project->getName() == null){
                $this->error = "&#39;$cellValue&#39; is not a valid project.";
            }
            else{
                $cellValue = $project->getName();
            }
        }
        $this->value = str_replace(' ', '', $cellValue);
    }
    
    function rasterize(){
        return array(V_PROJ, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        return "{$this->value}";
    }
}

?>
