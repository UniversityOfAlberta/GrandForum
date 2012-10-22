<?php

abstract class Cell {
    
    var $value;
    var $error;
    var $style;
    var $dynamic = false;
    var $params = array();
    
    function setValue($value){
        $this->value = $value;
    }
    
    function getValue(){
        return $this->value;
    }
    
    abstract function rasterize();
    
    abstract function render();
    
    abstract function toString();
    
}

?>
