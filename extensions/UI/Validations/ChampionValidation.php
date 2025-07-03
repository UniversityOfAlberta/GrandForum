<?php

class ChampionValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        if($value == ""){
            return true;
        }
        $person = Person::newFromNameLike($value);
        return ($person != null && $person->getName() != "" && $person->isRole(CHAMP));
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid Champion (value used: {$this->value})";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be an already exisiting Champion (value used: {$this->value})";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid Champion (value used: {$this->value})";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be an already existing Champion (value used: {$this->value})";
    }
    
}

?>
