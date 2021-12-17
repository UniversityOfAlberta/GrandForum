<?php

class ProjectProductsCell extends ProjectPublicationCell {
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $category = $params[0];
        unset($params[0]);
        $params = array_values($params);
        $this->label = Inflect::pluralize($category);
        $this->category = $category;
        parent::__construct($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(PROJECT_PRODUCTS, $this);
    }
}

?>
