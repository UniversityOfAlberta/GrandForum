<?php

class MoneyCell extends Cell{
    
    var $table = null;
    var $totalX = -1;
    var $totalY = -1;
    var $postText = "";
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $value = '';
        if($cellValue != ''){
            if(isset($params[0]) && isset($params[1])){
                $this->totalY = $params[0];
                $this->totalX = $params[1];
            }
            else if(isset($params[0])){
                $this->postText = $params[0];
            }
            $cellValue = str_replace(',', '', $cellValue);
            $cellValue = str_replace('$', '', $cellValue);
            if(is_numeric($cellValue)){
                $value = $cellValue;
            }
            else{
                $value = $cellValue;
                //$this->error = "&#39;$cellValue&#39; is not a valid money value.";
            }
        }
        $this->value = $value;
        $this->table = $table;
    }
    
    function rasterize(){
        return array(MONEY, $this);
    }
    
    function toString(){
        return "$".$this->value;
    }
    
    function render(){
        $str = "";
        if($this->value != ""){
            if(strstr($this->style, "text-align:right;") === false){
                $this->style .= "text-align:right;";
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
                $str .= "$".number_format($this->value).$this->postText;
            }
        }
        return $str;
    }
}

?>
