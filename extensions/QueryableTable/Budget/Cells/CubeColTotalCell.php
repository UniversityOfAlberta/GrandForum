<?php

class CubeColTotalCell extends MoneyCell{
    
    function CubeColTotalCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(CUBE_COL_TOTAL, $this);
    }
}

?>
