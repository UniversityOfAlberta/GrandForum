<?php

class NothingValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct(false);
    }
    
    function validateFn($value){
        return true;
    }
    
    function failMessage($name){
        return "";
    }
    
    function failNegMessage($name){
        return "";
    }
    
    function warningMessage($name){
        return "";
    }
    
    function warningNegMessage($name){
        return "";
    }
    
}

?>
