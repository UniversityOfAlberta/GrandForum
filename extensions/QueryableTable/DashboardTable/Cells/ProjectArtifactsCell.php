<?php

class ProjectArtifactsCell extends ProjectPublicationCell {
    
    function ProjectArtifactsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Artifacts";
        $this->category = "Artifact";
        $this->ProjectPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_ARTIFACTS, $this);
    }
}

?>
