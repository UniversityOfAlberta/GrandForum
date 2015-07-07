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
    
    /*
    function render(){
        $str = "";
        if($this->value != ""){
            if(strstr($this->style, "text-align:right;font-family:monospace !important;") === false){
                $this->style .= "text-align:right;font-family:monospace !important;";
            }
            if($this->totalX != -1 && $this->totalY != -1){
                if(isset($this->table->xls[$this->totalY][$this->totalX])){
                    $totalCell = $this->table->xls[$this->totalY][$this->totalX];
                    $totalValue = $totalCell->getValue();
                    $percValue = round(($this->value / max(1, $totalValue))*100);
                    $str .= "($percValue%)&nbsp;";
                }
            }
            if(is_numeric($this->value)){
                $str .= "$".number_format($this->value);
            }
        }
        return $str;
    }*/
}

?>
