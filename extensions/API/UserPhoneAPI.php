<?php

class UserPhoneAPI extends API{

    function __construct(){
        $this->addPOST("phone", true, "The phone number of the user", "555-5555");
    }

    function processParams($params){
        if(isset($_POST['phone']) && $_POST['phone'] != ""){
            $_POST['phone'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['phone']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $_POST['phone'] = @trim($_POST['phone']);
        DBFunctions::delete('grand_user_telephone',
                            array('primary_indicator' => EQ(1),
                                  'user_id' => EQ($person->getId())));
        DBFunctions::insert('grand_user_telephone',
                            array('user_id' => $person->getId(),
                                  'number' => $_POST['phone'],
                                  'primary_indicator' => 1));
        if(!$noEcho){
            echo "Phone number added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
