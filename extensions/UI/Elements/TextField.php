<?php

class TextField extends UIElement {
    
    function TextField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->attr('size', 40);
    }
    
    function render(){
        return "<input type='text' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />";
    }
    
}


?>
