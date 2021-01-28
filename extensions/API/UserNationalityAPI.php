<?php

class UserNationalityAPI extends API{

    function __construct(){
        $this->addPOST("nationality", true, "The nationality of the user", "Canadian");
    }

    function processParams($params){
        if(isset($_POST['nationality']) && $_POST['nationality'] != ""){
            $_POST['nationality'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['nationality']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('user_nationality' => $_POST['nationality']),
                            array('user_id' => EQ($person->getId())));
	$person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Nationality added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
