<?php

class BlankCell extends Cell{
    
    function BlankCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->value = "";
    }
    
    function rasterize(){
        return array(BLANK, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        return $this->value;
    }
}

?>
