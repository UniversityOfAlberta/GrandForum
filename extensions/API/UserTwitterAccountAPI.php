<?php

class UserTwitterAccountAPI extends API{

    function UserTwitterAccountAPI(){
        $this->addPOST("twitter", true, "The name of the twitter account", "stroulia");
    }

    function processParams($params){
        if(isset($_POST['twitter']) && $_POST['twitter'] != ""){
            $_POST['twitter'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['twitter']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $sql = "UPDATE mw_user
                SET `user_twitter` = '{$_POST['twitter']}'
                WHERE user_id = '{$person->getId()}'";
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "Account added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
