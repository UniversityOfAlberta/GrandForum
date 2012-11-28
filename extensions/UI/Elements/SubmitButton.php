<?php

class SubmitButton extends UIElement {
    
    function SubmitButton($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
    }
    
    function render(){
        return "<input {$this->renderAttr()} type='submit' name='{$this->id}' value='{$this->value}' />";
    }
}


?>
