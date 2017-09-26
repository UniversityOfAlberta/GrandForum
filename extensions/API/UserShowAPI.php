<?php

class UserShowAPI extends API{

    function UserShowAPI(){
    }

    function processParams($params){
        if(isset($_POST['email']) && $_POST['email'] != ""){
            $_POST['email'] = $_POST['email'];
        }
    }

	function doAction($noEcho=false){
	    global $wgMessage;
	
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('grand_sop',
                            array('reviewer' => $checked),
                            array('user_id' => EQ($person->getId())));
        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Account show updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
