<?php

class ProjectPressCell extends ProjectPublicationCell {
    
    function ProjectPressCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Press";
        $this->category = "Press";
        $this->ProjectPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_ACTIVITIES, $this);
    }
}

?>
