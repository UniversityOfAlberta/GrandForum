<?php

class PercCell extends Cell{
    
    function PercCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $value = "";
        if($cellValue != ""){
            if(is_numeric($cellValue)){
                $value = $cellValue;
            }
            else{
                $value = $cellValue;
                $this->error = "&#39;$cellValue&#39; is not a valid percent value.";
            }
        }
        $this->value = $value;
    }
    
    function rasterize(){
        return array(PERC, $this);
    }
    
    function toString(){
        return $this->value."%";
    }
    
    function render(){
        if($this->value != ""){
            $this->style = "text-align:right;";
            return number_format($this->value*100, 0)."%";
        }
        else{
            return "";
        }
    }
}

?>
