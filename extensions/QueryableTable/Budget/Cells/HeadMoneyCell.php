<?php

class HeadMoneyCell extends MoneyCell{
    
    var $table = null;
    var $totalX = -1;
    var $totalY = -1;
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::__construct($cellType, $params, $cellValue, $rowN, $colN, $table);
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
