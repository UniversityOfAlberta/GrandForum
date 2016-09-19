<?php

class Captcha extends UIElement {
    
    function Captcha($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->registerValidation(new CaptchaValidation(VALIDATION_POSITIVE));
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        return "<img id='captcha' src='$wgServer$wgScriptPath/Classes/securimage/securimage_show.php' alt='CAPTCHA Image' /><br />
                <input type='text' name='{$this->id}' size='10' maxlength='6' />
	            <a href='#' onclick='document.getElementById(\"captcha\").src = \"$wgServer$wgScriptPath/Classes/securimage/securimage_show.php?\" + Math.random(); return false'>[ Different Image ]</a>";
    }
    
}


?>
