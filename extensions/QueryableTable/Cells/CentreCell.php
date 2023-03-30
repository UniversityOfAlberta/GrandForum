<?php

class CentreCell extends Cell{
    
    function CentreCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->value = $cellValue;
    }
    
    function rasterize(){
        return array(CENTRE, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style .= "text-align:center;";
        return $this->value;
    }
}

?>
