<?php

class UniqueUserValidation extends UIValidation {

    var $duplicatePeople;
    var $personName;

    function __construct($neg=false, $warning=false) {
        parent::__construct($neg, $warning);
    }
    
    function validateFn($value){
        $firstName = @$_POST['first_name_field'];
        $lastName = @$_POST['last_name_field'];
        if("$firstName.$lastName" == "."){
            return true;
        }
        $person = Person::newFromName("$firstName.$lastName");
        $this->personName = "$firstName.$lastName";
        return ($person == null || $person->getName() == "");
    }
    
    function failMessage($name){
        return "The user name must not be an already existing Person (value used: {$this->personName})";
    }
    
    function failNegMessage($name){
        return "The user name must be a valid Person (value used: {$this->personName})";
    }
    
    function warningMessage($name){
        return "The user name should not be an already existing Person (value used: {$this->personName})";
    }
    
    function warningNegMessage($name){
        return "The user name should be a valid Person (value used: {$this->personName})";
    }
    
}

?>
