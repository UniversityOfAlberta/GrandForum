<?php

class PersonAwardsCell extends PersonPublicationCell {
    
    function PersonAwardsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Awards";
        $this->category = "Award";
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_AWARDS, $this);
    }
}

?>
