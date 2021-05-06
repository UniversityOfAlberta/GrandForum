<?php

$wgMessage = new Messages();

class Messages {
    
    var $errors = array();
    var $warnings = array();
    var $success = array();
    var $info = array();
    var $purpleInfo = array();
    var $errorIndex = 0;
    var $warningIndex = 0;
    var $successIndex = 0;
    var $infoIndex = 0;
    var $purpleIndex = 0;
    
    function Messages(){
        if(isset($_COOKIE['errors'])){
            $this->errors = unserialize($_COOKIE['errors']);
        }
        if(isset($_COOKIE['warnings'])){
            $this->warnings = unserialize($_COOKIE['warnings']);
        }
        if(isset($_COOKIE['success'])){
            $this->success = unserialize($_COOKIE['success']);
        }
        if(isset($_COOKIE['info'])){
            $this->info = unserialize($_COOKIE['info']);
        }
        if(isset($_COOKIE['purpleInfo'])){
            $this->info = unserialize($_COOKIE['purpleInfo']);
        }
        $this->clearCookies();
    }
    
    // Adds a (red) error message
    function addError($message, $index=false){
        if($index !== false){
            $this->errors[$index] = $message;
        }
        else{
            $this->errors[$this->errorIndex++] = $message;
        }
        @setcookie('errors', serialize($this->errors), time()+3600);
    }
    
    // Adds a (yellow) warning message
    function addWarning($message, $index=false){
        if($index !== false){
            $this->warnings[$index] = $message;
        }
        else{
            $this->warnings[$this->warningIndex++] = $message;
        }
        @setcookie('warnings', serialize($this->warnings), time()+3600);
    }
    
    // Adds a (green) success message
    function addSuccess($message, $index=false){
        if($index !== false){
            $this->success[$index] = $message;
        }
        else{
            $this->success[$this->successIndex++] = $message;
        }
        @setcookie('success', serialize($this->success), time()+3600);
    }
    
    // Adds a (blue) info message
    function addInfo($message, $index=false){
        if($index !== false){
            $this->info[$index] = $message;
        }
        else{
            $this->info[$this->infoIndex++] = $message;
        }
    }
    
    // Adds a (purple) info message
    function addPurpleInfo($message, $index=false){
        if($index !== false){
            $this->purpleInfo[$index] = $message;
        }
        else{
            $this->purpleInfo[$this->purpleIndex++] = $message;
        }
    }
    
    // Clears the message cookies
    function clearCookies(){
        @setcookie('errors', serialize(array()), time()-3600);
        @setcookie('warnings', serialize(array()), time()-3600);
        @setcookie('success', serialize(array()), time()-3600);
        @setcookie('info', serialize(array()), time()-3600);
        @setcookie('purpleInfo', serialize(array()), time()-3600);
    }
    
    // Empties all error messages
    function clearError(){
        $this->errors = array();
        $this->errorIndex = 0;
    }
    
    // Empties all warning messages
    function clearWarning(){
        $this->warnings = array();
        $this->warningIndex = 0;
    }
    
    // Empties all success messages
    function clearSuccess(){
        $this->success = array();
        $this->successIndex = 0;
    }
    
    // Empties all info messages
    function clearInfo(){
        $this->info = array();
        $this->infoIndex = 0;
    }
    
    // Empties all purple info messages
    function clearPurpleInfo(){
        $this->purpleInfo = array();
        $this->purpleIndex = 0;
    }
    
    // Displays the messages
    function showMessages(){
        $this->clearCookies();
        
        ksort($this->errors);
        ksort($this->warnings);
        $errors = implode("<br />\n", $this->errors);
        $warnings = implode("<br />\n", $this->warnings);
        $success = implode("<br />\n", $this->success);
        $info = implode("<br />\n", $this->info);
        $purpleInfo = implode("<br />\n", $this->purpleInfo);
        if($errors != ""){
            $errors = "<div class='error'><span style='display:inline-block;'>$errors</span></div>\n";
        }
        if($warnings != ""){
            $warnings = "<div class='warning'><span style='display:inline-block;'>$warnings</span></div>\n";
        }
        if($success != ""){
            $success = "<div class='success'><span style='display:inline-block;'>$success</span></div>\n";
        }
        if($info != ""){
            $info = "<div class='info'><span style='display:inline-block;'>$info</span></div>\n";
        }
        if($purpleInfo != ""){
            $purpleInfo = "<div class='purpleInfo'><span style='display:inline-block;'>$purpleInfo</span></div>\n";
        }
        echo $errors.$warnings.$success.$info.$purpleInfo;
    }
}

?>
