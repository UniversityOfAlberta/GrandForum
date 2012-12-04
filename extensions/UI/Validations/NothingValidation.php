<?php

class NothingValidation extends UIValidation {

    function NothingValidation($neg=false) {
        parent::UIValidation(false);
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
