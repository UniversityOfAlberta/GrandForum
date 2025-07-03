<?php

class RowSumCell extends MoneyCell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $value = 0;
        foreach($table->xls[$rowN] as $colN1 => $col){
            if($col instanceof Cell){
                if(is_numeric($col->getValue()) && $col->summable && $colN1 < $colN){
                    $value += $col->getValue();
                }
            }
        }
        if($value === 0){
            $value = '0';
        }
        $this->value = $value;
        $this->dynamic = true;
    }
    
    function rasterize(){
        return array(ROW_TOTAL, new RowTotalCell("", "", $this->value, "", "", ""));
    }
}

?>
