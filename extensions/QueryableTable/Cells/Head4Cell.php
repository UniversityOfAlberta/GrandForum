<?php

class Head4Cell extends Cell{
    
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
        return HEAD4;
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style = "text-align: right;";
        return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{$this->value}</b>";
    }
}

?>
