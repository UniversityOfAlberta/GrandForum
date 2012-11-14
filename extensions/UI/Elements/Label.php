<?php

class Label extends UIElement {
    
    function Label($id, $name, $value, $validations=0){
        parent::UIElement($id, $name, $value, $validations);
    }
    
    function render(){
        $redStar = "";
        if($this->isValidationSet(VALIDATE_NOT_NULL)){
            $redStar = "<span style='color:red;'>*</span>";
        }
        return "<div id='{$this->id}' class='label tooltip' title='{$this->value}'>{$this->name}{$redStar}:</div>";
    }
}


?>
