<?php

class NumberField extends TextField {
    
    function NumberField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::TextField($id, $name, $value, $validations + VALIDATE_IS_NUMBER);
        $this->attr('size', 5);
    }
    
}

?>
