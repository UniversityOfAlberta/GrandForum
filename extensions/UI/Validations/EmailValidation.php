<?php

class EmailValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        return (Sanitizer::validateEmail($value));
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid email address";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be a valid email address";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid email address";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be a valid email address";
    }
    
}

?>
