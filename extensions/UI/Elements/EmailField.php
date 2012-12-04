<?php

class EmailField extends UIElement {
    
    function EmailField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations + VALIDATE_EMAIL);
        $this->attr('size', 30);
    }
    
    function render(){
        return "<input type='text' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />";
    }
    
}

?>
