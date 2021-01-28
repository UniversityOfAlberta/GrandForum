<?php

class NullValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        if(is_array($value)){
            
            return (count($value) == 0);
        }
        return ($value == null || $value == "");
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be empty";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be empty";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be empty";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be empty";
    }
    
}

?>
