<?php

class TextField extends UIElement {
    
    var $size;
    
    function TextField($id, $name, $value, $size=40, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->size = $size;
    }
    
    function render(){
        return "<input type='text' size='{$this->size}' name='{$this->id}' value='{$this->value}' />";
    }
    
}


?>
