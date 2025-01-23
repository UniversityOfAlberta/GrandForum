<?php

class CaptchaValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        include_once 'Classes/securimage/securimage.php';
	    $securimage = new Securimage();
	    return ($securimage->check($value) !== false);
    }
    
    function failMessage($name){
        return "The security code was incorrect";
    }
    
    function failNegMessage($name){
        return "The security code was not incorrect";
    }
    
    function warningMessage($name){
        return "The security code was incorrect";
    }
    
    function warningNegMessage($name){
        return "The security code was not incorrect";
    }
    
}

?>
