<?php

class NumberField extends UIElement {
    
    function NumberField($name, $value, $size=5, $validations=VALIDATE_NOTHING){
        parent::UIElement($name, $value, $validations + VALIDATE_IS_NUMBER);
        $this->size = $size;
    }
    
}


?>
