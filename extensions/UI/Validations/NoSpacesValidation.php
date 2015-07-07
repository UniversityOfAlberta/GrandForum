<?php

class NoSpacesValidation extends UIValidation {

    function NoSpacesValidation($neg=false) {
        parent::UIValidation($neg);
    }
    
    function validateFn($value){
        return (strstr($value, " ") === false);
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must not contain spaces";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must contain spaces";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should not contain spaces";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should contain spaces";
    }
    
}

?>
