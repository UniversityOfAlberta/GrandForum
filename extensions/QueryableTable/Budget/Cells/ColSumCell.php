<?php

class ColSumCell extends MoneyCell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $value = 0;
        $projection = array_project($table->xls, $colN);
        foreach($projection as $rowN1 => $row){
            if($row instanceof Cell){
                if(is_numeric($row->getValue()) && $row->summable && $rowN1 < $rowN){
                    $value += $row->getValue();
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
        return array(COL_TOTAL, new ColTotalCell("", "", $this->value, "", "", ""));
    }
}

?>
