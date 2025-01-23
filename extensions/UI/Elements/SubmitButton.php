<?php

class SubmitButton extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
    }
    
    function render(){
        return "<input {$this->renderAttr()} type='submit' name='{$this->id}' value='{$this->value}' />";
    }
}


?>
