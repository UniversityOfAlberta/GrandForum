<?php

class ColTotalCell extends MoneyCell{
    
    function ColTotalCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(COL_TOTAL, $this);
    }
}

?>
