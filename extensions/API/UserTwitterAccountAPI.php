<?php

class UserTwitterAccountAPI extends API{

    function __construct(){
        $this->addPOST("twitter", true, "The name of the twitter account", "stroulia");
    }

    function processParams($params){
        if(isset($_POST['twitter']) && $_POST['twitter'] != ""){
            $_POST['twitter'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['twitter']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('user_twitter' => $_POST['twitter']),
                            array('user_id' => EQ($person->getId())));
	$person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Account added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
