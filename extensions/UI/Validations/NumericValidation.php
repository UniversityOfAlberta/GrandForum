<?php

class NumericValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        return (!$this->validateNotNull($value) || is_numeric($value));
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid number";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be a valid number";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid number";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be a valid number";
    }
    
}

?>
