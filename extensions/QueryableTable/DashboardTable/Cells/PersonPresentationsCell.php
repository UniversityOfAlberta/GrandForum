<?php

class PersonPresentationsCell extends PersonPublicationCell {
    
    function PersonPresentationsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Presentation";
        $this->category = "Presentation";
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_PRESENTATIONS, $this);
    }
}

?>
