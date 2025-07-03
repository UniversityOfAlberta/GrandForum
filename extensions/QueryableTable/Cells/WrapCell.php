<?php

class WrapCell extends ReadCell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::__construct($cellType, $params, $cellValue, $rowN, $colN, $table);
        $this->wrap = true;
    }
    
    function rasterize(){
        return array(WRAP, $this);
    }
}

?>
