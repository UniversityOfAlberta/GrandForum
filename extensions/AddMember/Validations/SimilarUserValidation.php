<?php

class SimilarUserValidation extends UIValidation {

    var $duplicatePeople;

    function __construct($neg=false, $warning=false) {
        parent::__construct($neg, $warning);
    }
    
    function validateFn($value){
        $firstName = @$_POST['first_name_field'];
        $lastName = @$_POST['last_name_field'];
        if($firstName.$lastName == ""){
            return true;
        }
        $allPeople = Person::getAllPeople();
        foreach($allPeople as $person){
            similar_text($person->getName(), "$firstName.$lastName", $percent);
            if($percent > 80 && $percent != 100){
                $this->duplicatePeople[] = $person;
            }
        }
        return (count($this->duplicatePeople) == 0);
    }
    
    function failMessage($name){
        $names = array();
        foreach($this->duplicatePeople as $person){
            $names[] = "<a href='{$person->getURL()}' target='_blank'>{$person->getName()}</a>";
        }
        if(count($names) > 1){
            return "The name provided is similar to the following people: ".implode(", ", $names);
        }
        else{
            return "The name provided is similar to the following person: ".implode(", ", $names);
        }
    }
    
    function failNegMessage($name){
        return "";
    }
    
    function warningMessage($name){
        return $this->failMessage($name);
    }
    
    function warningNegMessage($name){
        return "";
    }
    
}

?>
