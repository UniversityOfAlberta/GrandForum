<?php

class PersonActivitiesCell extends PersonPublicationCell {
    
    function PersonActivitiesCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Activities";
        $this->category = "Activity";
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_ACTIVITIES, $this);
    }
}

?>
