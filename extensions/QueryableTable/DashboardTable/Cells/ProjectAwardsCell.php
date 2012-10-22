<?php

class ProjectAwardsCell extends ProjectPublicationCell {
    
    function ProjectAwardsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Awards";
        $this->category = "Award";
        $this->ProjectPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_AWARDS, $this);
    }
}

?>
