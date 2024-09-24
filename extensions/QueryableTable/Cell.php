<?php

abstract class Cell {
    
    var $value;
    var $error = "";
    var $style;
    var $span;
    var $dynamic = false;
    var $params = array();
    var $summable = true;
    var $wrap = false;
    
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
