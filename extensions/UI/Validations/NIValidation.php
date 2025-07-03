<?php

class NIValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        if($value == ""){
            return true;
        }
        $person = Person::newFromName($value);
        if($person == null){
            $person = Person::newFromNameLike($value);
        }
        return ($person != null && $person->getName() != "" && $person->isRoleAtLeast(NI));
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid NI (value used: {$this->value})";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be an already exisiting NI (value used: {$this->value})";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid NI (value used: {$this->value})";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be an already existing NI (value used: {$this->value})";
    }
    
}

?>
