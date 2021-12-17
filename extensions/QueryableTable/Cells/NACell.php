<?php

class NACell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        
    }
    
    function rasterize(){
        return array(NA, $this);
    }
    
    function toString(){
        
    }
    
    function render(){
        
    }
}

?>
