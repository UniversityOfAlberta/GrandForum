<?php

class TextareaField extends UIElement {
    
    function TextareaField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
    }
    
    function render(){
        return "<textarea style='height:100px;width:400px;' name='{$this->id}'>{$this->value}</textarea>";
    }
    
}


?>
