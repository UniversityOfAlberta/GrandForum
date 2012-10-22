<?php

class ReadCell extends Cell{
    
    function ReadCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->value = $cellValue;
    }
    
    function rasterize(){
        return array(READ, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        return $this->value;
    }
}

?>
