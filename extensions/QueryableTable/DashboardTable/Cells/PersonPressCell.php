<?php

class PersonPressCell extends PersonPublicationCell {
    
    function PersonPressCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Press";
        $this->category = "Press";
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_ACTIVITIES, $this);
    }
}

?>
