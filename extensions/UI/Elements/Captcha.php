<?php

class Captcha extends UIElement {
    
    function __construct($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $validations);
        $this->registerValidation(new CaptchaValidation(VALIDATION_POSITIVE));
    }
    
    function render(){
        global $wgLang;
        if($wgLang->getCode() == 'en'){
            return "<img id='captcha' src='../Classes/securimage/securimage_show.php' alt='CAPTCHA Image' /><br />
                    <input type='text' name='{$this->id}' size='10' maxlength='6' />
	                <a href='#' onclick='document.getElementById(\"captcha\").src = \"../Classes/securimage/securimage_show.php?\" + Math.random(); return false'>[ Different Image ]</a>";
        }
        if($wgLang->getCode() == 'fr'){
            return "<img id='captcha' src='../Classes/securimage/securimage_show.php' alt='CAPTCHA Image' /><br />
                    <input type='text' name='{$this->id}' size='10' maxlength='6' />
	                <a href='#' onclick='document.getElementById(\"captcha\").src = \"../Classes/securimage/securimage_show.php?\" + Math.random(); return false'>[ Image Différente ]</a>";
        }
    }
    
}


?>
