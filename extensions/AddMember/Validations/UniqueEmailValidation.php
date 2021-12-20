<?php

class UniqueEmailValidation extends UIValidation {

    function UniqueEmailValidation($neg=false, $warning=false) {
        parent::__construct($neg, $warning);
    }
    
    function validateFn($value){
        if($value == ""){
            return true;
        }
        $person = Person::newFromEmail($value);
        return ($person == null);
    }
    
    function failMessage($name){
        return "This email address '<i>{$this->value}</i>' has already been taken";
    }
    
    function failNegMessage($name){
        return "This email address '<i>{$this->value}</i>' has not yet been taken";
    }
    
    function warningMessage($name){
        return "This email address '<i>{$this->value}</i>' has already been taken";
    }
    
    function warningNegMessage($name){
        return "This email address '<i>{$this->value}</i>' has not yet been taken";
    }
    
}

?>
