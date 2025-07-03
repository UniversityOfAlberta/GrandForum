<?php

class NumberField extends TextField {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations + VALIDATE_NUMBER);
        $this->attr('size', 5);
    }
    
}

?>
