<?php

class CubeTotalCell extends MoneyCell{
    
    function CubeTotalCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(CUBE_TOTAL, $this);
    }
}

?>
