<?php

class ColTotalCell extends MoneyCell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::__construct($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(COL_TOTAL, $this);
    }
}

?>
