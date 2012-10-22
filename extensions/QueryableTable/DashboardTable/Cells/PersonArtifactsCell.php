<?php

class PersonArtifactsCell extends PersonPublicationCell {
    
    function PersonArtifactsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Artifacts";
        $this->category = "Artifact";
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_ARTIFACTS, $this);
    }
}

?>
