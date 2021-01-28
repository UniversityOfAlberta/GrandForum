<?php

class UserGenderAPI extends API{

    function __construct(){
        $this->addPOST("gender", true, "The gender of this user (Male or Female)", "Male");
    }

    function processParams($params){
        if(isset($_POST['gender']) && $_POST['gender'] != ""){
            $_POST['gender'] = str_replace("'", "&#39;", $_POST['gender']);
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('user_gender' => $_POST['gender']),
                            array('user_id' => EQ($person->getId())));
	$person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "User's Gender updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
