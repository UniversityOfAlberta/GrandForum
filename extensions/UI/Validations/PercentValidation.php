<?php

class PercentValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        return ($value == null || (is_numeric($value) && $value >= 0 && $value <= 100));
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid percent";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be a valid percent";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid percent";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be a valid percent";
    }
    
}

?>
