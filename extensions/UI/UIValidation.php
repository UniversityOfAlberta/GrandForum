<?php

abstract class UIValidation {

    var $functionName;
    var $params;
    var $failMessage;
    
    function UIValidation(){
        
    }
    
    abstract function validate($value);
    
    abstract function failMessage($name);
}

?>
