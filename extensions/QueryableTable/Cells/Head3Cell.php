<?php

class Head3Cell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        $this->value = $this->value;
        return HEAD3;
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{$this->value}</b>";
    }
}

?>
