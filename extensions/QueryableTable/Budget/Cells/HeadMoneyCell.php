<?php

class HeadMoneyCell extends MoneyCell{
    
    var $table = null;
    var $totalX = -1;
    var $totalY = -1;
    
    function HeadMoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(HEAD_MONEY, $this);
    }
    
    function render(){
        $this->style = 'background-color:#DDDDDD;';
        return parent::render();
    }

}

?>
