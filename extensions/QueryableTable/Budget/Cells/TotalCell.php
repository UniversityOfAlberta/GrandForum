<?php

class TotalCell extends MoneyCell{
    
    function TotalCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(TOTAL, $this);
    }
}

?>
