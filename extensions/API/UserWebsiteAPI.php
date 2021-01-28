<?php

class UserWebsiteAPI extends API{

    function __construct(){
        $this->addPOST("website", true, "The url of a website", "http://www.mywebsite.com");
    }

    function processParams($params){
        if(isset($_POST['website']) && $_POST['website'] != ""){
            $_POST['website'] = str_replace("'", "&#39;", $_POST['website']);
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('user_website' => $_POST['website']),
                            array('user_id' => EQ($person->getId())));
	$person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Website added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
