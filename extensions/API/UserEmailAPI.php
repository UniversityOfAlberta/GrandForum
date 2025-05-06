<?php

class UserEmailAPI extends API{

    function __construct(){
        $this->addPOST("email", true, "The email address for this account.", "email@mail.com");
    }

    function processParams($params){
        if(isset($_POST['email']) && $_POST['email'] != ""){
            $_POST['email'] = $_POST['email'];
        }
    }

	function doAction($noEcho=false){
	    global $wgMessage;
	    
        $person = Person::newFromName($_POST['user_name']);
        $valid = @Sanitizer::validateEmail($_POST['email']);
        if(!$valid){
            @$wgMessage->addError("<b>{$_POST['email']}</b> is not a valid email address");
            return false;
        }
        DBFunctions::update('mw_user',
                            array('user_email' => $_POST['email']),
                            array('user_id' => EQ($person->getId())));
        $person->email = $_POST['email'];
        DBCache::delete("mw_user_{$person->getId()}");
        if(!$noEcho){
            echo "Account email updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
