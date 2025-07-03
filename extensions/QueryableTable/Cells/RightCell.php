<?php

class RightCell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->value = $cellValue;
    }
    
    function rasterize(){
        return array(RIGHT, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style .= "text-align:right;";
        return $this->value;
    }
}

?>
