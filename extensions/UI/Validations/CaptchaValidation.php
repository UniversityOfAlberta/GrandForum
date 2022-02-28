<?php

class CaptchaValidation extends UIValidation {

    function __construct($neg=false) {
        parent::__construct($neg);
    }
    
    function validateFn($value){
        global $config;
        
        $post_data = array(
            'secret' => $config->getValue('reCaptchaSecretKey'),
            'response' => $_POST['g-recaptcha-response']
        );
        
        // Prepare new cURL resource
        $crl = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLINFO_HEADER_OUT, true);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
            
        // Submit the POST request
        $response = json_decode(curl_exec($crl));
        return ($response->success == "true");
    }
    
    function failMessage($name){
        return "The robot test failed";
    }
    
    function failNegMessage($name){
        return "The robot test did not fail";
    }
    
    function warningMessage($name){
        return "The robot test failed";
    }
    
    function warningNegMessage($name){
        return "The robot test did not fail";
    }
    
}

?>
