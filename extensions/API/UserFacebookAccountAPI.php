<?php

class UserFacebookAccountAPI extends API{

    function UserFacebookAccountAPI(){
        $this->addPOST("facebook", true, "The name of the facebook account", "stroulia");
    }

    function processParams($params){
        if(isset($_POST['facebook']) && $_POST['facebook'] != ""){
            $_POST['facebook'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['facebook']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('user_facebook' => $_POST['facebook']),
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
