<?php

class PersonProductsCell extends PersonPublicationCell {
    
    function PersonProductsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $category = $params[0];
        unset($params[0]);
        $params = array_values($params);
        $this->label = Inflect::pluralize($category);
        $this->category = $category;
        $this->PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PERSON_PRODUCTS, $this);
    }
}

?>
