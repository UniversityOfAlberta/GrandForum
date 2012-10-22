<?php

class PersonPublicationsCell extends PersonPublicationCell {
    
    function PersonPublicationsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Publications";
        $this->category = "Publication";
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_PUBLICATIONS, $this);
    }
}

?>
