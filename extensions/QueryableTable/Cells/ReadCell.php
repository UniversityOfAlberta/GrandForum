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
        if(is_numeric(str_replace(",", "", $this->value))){
            $this->style = "text-align:right;";
            return number_format($this->value, 1);
        }
        return $this->value;
    }
}

?>
