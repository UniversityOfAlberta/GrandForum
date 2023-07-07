<?php

class SubMoneyCell extends MoneyCell{
    
    var $table = null;
    var $totalX = -1;
    var $totalY = -1;
    
    function SubMoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $value = '';
        if($cellValue != ''){
            if(isset($params[0]) && isset($params[1])){
                $this->totalY = $params[0];
                $this->totalX = $params[1];
            }
            $cellValue = str_replace(',', '', $cellValue);
            $value = $cellValue;
        }
        $this->value = $value;
        $this->table = $table;
        $this->summable = false;
    }
    
    function rasterize(){
        return array(SUB_MONEY, $this);
    }
    
    function render(){
        $str = "";
        if($this->value != ""){
            if(strstr($this->style, "font-style: italic;text-align:right;") === false){
                $this->style .= "font-style: italic;text-align:right;";
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
    }
}

?>
