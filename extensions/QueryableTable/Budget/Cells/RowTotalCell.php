<?php

class RowTotalCell extends MoneyCell{
    
    function RowTotalCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(ROW_TOTAL, $this);
    }
}

?>
