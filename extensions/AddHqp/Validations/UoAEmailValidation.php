<?php

class UoAEmailValidation extends UIValidation {

    function UoAEmailValidation($neg=false, $warning=false) {
        parent::UIValidation($neg, $warning);
    }
    
    function validateFn($value){
        return (strstr($value,"@ualberta.ca") !== false);
    }
    
    function failMessage($name){
        return "This email address '<i>{$this->value}</i>' must be a ualberta.ca address";
    }
    
    function failNegMessage($name){
        return "This email address '<i>{$this->value}</i>' has not be a ualberta.ca address";
    }
    
    function warningMessage($name){
        return "This email address '<i>{$this->value}</i>' should be a ualberta.ca address";
    }
    
    function warningNegMessage($name){
        return "This email address '<i>{$this->value}</i>' should not be a ualberta.ca address";
    }
    
}

?>
