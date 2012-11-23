<?php

class PercentField extends UIElement {
    
    function PercentField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations + VALIDATE_IS_PERCENT);
        $this->attr('size', 3);
    }
    
    function render(){
        return "<input type='text' {$this->renderAttr()} name='{$this->id}' value='{$this->value}' />%";
    }
    
}

?>
