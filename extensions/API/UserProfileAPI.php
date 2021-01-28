<?php

class UserProfileAPI extends API{

    function __construct(){
        $this->addPOST("type", true, "The type of profile this is.  Can be either 'public' or 'private'", "public");
        $this->addPOST("profile", true, "The profile text for this account.  Can be either 'public' or 'private'", "This is my profile");
    }

    function processParams($params){

    }

	function doAction($noEcho=false){
        $person = Person::newFromName(@$_POST['user_name']);
        DBFunctions::update('mw_user',
                            array("user_{$_POST['type']}_profile" => @$_POST['profile']),
                            array('user_id' => EQ($person->getId())));
	$person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Account profile updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
