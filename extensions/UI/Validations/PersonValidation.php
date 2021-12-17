<?php

class PersonValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        $person = Person::newFromNameLike($value);
        return ($person != null && $person->getName() != "");
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid Person (value used: {$this->value})";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be an already exisiting Person (value used: {$this->value})";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid Person (value used: {$this->value})";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be an already existing Person (value used: {$this->value})";
    }
    
}

?>
