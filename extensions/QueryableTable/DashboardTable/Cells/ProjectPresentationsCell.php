<?php

class ProjectPresentationsCell extends ProjectPublicationCell {
    
    function ProjectPresentationsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Presentation";
        $this->category = "Presentation";
        $this->ProjectPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_PRESENTATION, $this);
    }
}

?>
