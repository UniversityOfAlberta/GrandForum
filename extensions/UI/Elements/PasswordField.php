<?php

class PasswordField extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
    }
    
    function render(){
        return "<input type='password' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />";
    }
    
}

?>
