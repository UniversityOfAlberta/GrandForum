<?php

class SubmitButton extends UIElement {
    
    var $buttonText = "";
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->buttonText = $this->value;
    }
    
    function render(){
        return "<button {$this->renderAttr()} type='submit' name='{$this->id}' value='{$this->value}'>{$this->buttonText}</button>";
    }
}


?>
