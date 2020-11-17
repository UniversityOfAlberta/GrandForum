<?php

class NumberField extends TextField {
    
    function NumberField($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::TextField($id, $name, $value, $validations + VALIDATE_NUMERIC);
        $this->attr('size', 5);
    }
    
}

?>
