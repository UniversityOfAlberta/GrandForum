<?php

class MoneyCell extends Cell{
    
    function MoneyCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $value = '';
        if($cellValue != ''){
            $cellValue = str_replace(',', '', $cellValue);
            if(is_numeric($cellValue)){
                $value = $cellValue;
            }
            else{
                $value = $cellValue;
                $this->error = "&#39;$cellValue&#39; is not a valid money value.";
            }
        }
        $this->value = $value;
    }
    
    function rasterize(){
        return array(MONEY, $this);
    }
    
    function toString(){
        return "$".$this->value;
    }
    
    function render(){
        if($this->value != ""){
            $this->style = "text-align:right;";
            if(is_numeric($this->value)){
                return "$".number_format($this->value);
            }
            return "$".$this->value;
        }
        else{
            return "";
        }
    }
}

?>
