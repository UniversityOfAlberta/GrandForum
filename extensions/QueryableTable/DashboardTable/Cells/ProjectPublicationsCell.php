<?php

class ProjectPublicationsCell extends ProjectPublicationCell {
    
    function ProjectPublicationsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Publications";
        $this->category = "Publication";
        $this->ProjectPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_PUBLICATIONS, $this);
    }
}

?>
