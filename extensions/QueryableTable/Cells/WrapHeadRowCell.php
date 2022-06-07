<?php

class WrapHeadRowCell extends HeadRowCell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::__construct($cellType, $params, $cellValue, $rowN, $colN, $table);
        $this->wrap = true;
    }
    
    function rasterize(){
        return array(WRAP_HEAD_ROW, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
}

?>
