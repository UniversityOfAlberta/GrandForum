<?php

class CubeRowTotalCell extends MoneyCell{
    
    function CubeRowTotalCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(CUBE_ROW_TOTAL, $this);
    }
}

?>
