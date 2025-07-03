<?php

class ProjectValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        $project = Project::newFromHistoricName($value);
        return ($project != null && $project->getName() != "");
    }
    
    function failMessage($name){
        return "The field '".ucfirst($name)."' must be a valid Project (value used: {$this->value})";
    }
    
    function failNegMessage($name){
        return "The field '".ucfirst($name)."' must not be an already existing Project (value used: {$this->value})";
    }
    
    function warningMessage($name){
        return "The field '".ucfirst($name)."' should be a valid Project (value used: {$this->value})";
    }
    
    function warningNegMessage($name){
        return "The field '".ucfirst($name)."' should not be an already existing Project (value used: {$this->value})";
    }
    
}

?>
