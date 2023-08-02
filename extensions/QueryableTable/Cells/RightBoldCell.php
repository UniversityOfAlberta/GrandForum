<?php

class RightBoldCell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->value = $cellValue;
    }
    
    function rasterize(){
        return array(RIGHT_BOLD, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style .= "text-align:right;";
        return "<b>{$this->value}</b>";
    }
}

?>
