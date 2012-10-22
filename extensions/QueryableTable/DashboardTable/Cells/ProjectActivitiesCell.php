<?php

class ProjectActivitiesCell extends ProjectPublicationCell {
    
    function ProjectActivitiesCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Activities";
        $this->category = "Activity";
        $this->ProjectPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_ACTIVITIES, $this);
    }
}

?>
