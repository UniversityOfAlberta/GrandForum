<?php

class Captcha extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->registerValidation(new CaptchaValidation(VALIDATION_POSITIVE));
    }
    
    function render(){
        global $config;
        return "<div class='g-recaptcha' data-sitekey='{$config->getValue('reCaptchaSiteKey')}'></div>";
    }
    
}


?>
