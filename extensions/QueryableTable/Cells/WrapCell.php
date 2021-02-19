<?php

class WrapCell extends ReadCell{
    
    function WrapCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        self::ReadCell($cellType, $params, $cellValue, $rowN, $colN, $table);
        $this->wrap = true;
    }
    
    function rasterize(){
        return array(WRAP, $this);
    }
}

?>
