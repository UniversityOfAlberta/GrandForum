<?php

class FakeField extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
    }
    
    function render(){
        return "";
    }
    
}


?>
