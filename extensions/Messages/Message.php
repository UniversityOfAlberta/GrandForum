<?php

$wgMessage = new Message();

class Message {
    
    var $errors = array();
    var $warnings = array();
    var $success = array();
    var $info = array();
    var $purpleInfo = array();
    
    // Adds a (red) error message
    function addError($message){
        $this->errors[] = $message;
    }
    
    // Adds a (yellow) warning message
    function addWarning($message){
        $this->warnings[] = $message;
    }
    
    // Adds a (green) success message
    function addSuccess($message){
        $this->success[] = $message;
    }
    
    // Adds a (blue) info message
    function addInfo($message){
        $this->info[] = $message;
    }
    
    // Adds a (purple) info message
    function addPurpleInfo($message){
        $this->purpleInfo[] = $message;
    }
    
    // Empties all error messages
    function clearError(){
        $this->errors = array();
    }
    
    // Empties all warning messages
    function clearWarning(){
        $this->warnings = array();
    }
    
    // Empties all success messages
    function clearSuccess(){
        $this->success = array();
    }
    
    // Empties all info messages
    function clearInfo(){
        $this->info = array();
    }
    
    // Empties all purple info messages
    function clearPurpleInfo(){
        $this->purpleInfo = array();
    }
    
    // Displays the messages
    function showMessages(){
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
            $purpleInfo = "<div class='info'><span style='display:inline-block;'>$purpleInfo</span></div>\n";
        }
        echo $errors.$warnings.$success.$info.$purpleInfo;
        echo "<script type='text/javascript'>
            function closeParent(link){
                $(link).parent().remove();
            }
            
            $(document).ready(function(){
                $('.error, .warning, .success, .info, .purpleInfo').append('<a class=\'error_box_close\' onClick=\'closeParent(this)\'>X</a>');
            });
        </script>";
    }
}

?>
