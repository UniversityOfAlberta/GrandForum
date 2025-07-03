<?php

class Head2Cell extends Cell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(HEAD2, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        return "&nbsp;&nbsp;&nbsp;<b>{$this->value}</b>";
    }
}

?>
