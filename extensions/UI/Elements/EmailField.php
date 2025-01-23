<?php

class EmailField extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations + VALIDATE_EMAIL);
        $this->attr('size', 30);
    }
    
    function render(){
        return "<input type='text' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />";
    }
    
}

?>
